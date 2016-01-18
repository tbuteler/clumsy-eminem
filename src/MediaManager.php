<?php

namespace Clumsy\Eminem;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File as Filesystem;
use Clumsy\Assets\Facade as Asset;
use Clumsy\Eminem\File\MediaFile;
use Clumsy\Eminem\Models\Media;

class MediaManager
{
    public function mediaModel()
    {
        return config('clumsy.eminem.media-model');
    }

    public function media()
    {
        $model = $this->mediaModel();
        return new $model;
    }

    protected function hasMultipleSlots($array)
    {
        return (bool) count(array_filter(array_keys($array), 'is_string'));
    }

    public function guessExtension($path)
    {
        preg_match('/\.\w{2,4}$/', $path, $extension);

        return str_replace('.', '', head($extension));
    }

    public function fileForSlot($slot, $file, $filename = null)
    {
        extract($slot, EXTR_SKIP);

        return with(new MediaFile($file, $filename, $path_type))
                ->validate($validate)
                ->setMeta($media_meta);
    }

    public function add($slot, $file, $filename = null)
    {
        return $this->fileForSlot($slot, $file, $filename)->add();
    }

    public function addCopy($slot, $file, $filename = null)
    {
        return $this->fileForSlot($slot, $file, $filename)->addCopy();
    }

    public function slotDefaults()
    {
        return [
            'id'               => 'media',
            'label'            => 'Media',
            'association_type' => null,
            'association_id'   => null,
            'position'         => 'media',
            'path_type'        => config('clumsy.eminem.default-path-type'),
            'allow_multiple'   => false,
            'validate'         => null,
            'meta'             => null,
            'media_meta'       => null,
            'show_comments'    => true,
            'comments'         => null,
            'preview'          => 'image',
            'url'              => route('eminem.upload'),
            'meta_url'         => route('eminem.save-meta'),
            'view_bind'        => 'clumsy/eminem::media-bind',
            'view_media_item'  => 'clumsy/eminem::media-item',
            'view_media_box'   => 'clumsy/eminem::media-box',
            'view_media_modal' => 'clumsy/eminem::media-modal',
            'box_assets'       => [
                'media-management.css',
                'media-management.js',
            ],
        ];
    }

    public function slots($model, $id = null)
    {
        $slots = [];

        if (!method_exists($model, 'mediaSlots')) {
            return $slots;
        }

        if (!is_object($model)) {
            $model = new $model;
        }

        $defined = $model->mediaSlots();

        if (!is_array($defined)) {
            return $slots;
        }

        $defaults = [
            'association_type' => get_class($model),
            'association_id'   => $id,
        ];

        if (!$this->hasMultipleSlots($defined)) {
            foreach ($defined as $slot) {
                $slots[$slot['position']] = array_merge(
                    $this->slotDefaults(),
                    $defaults,
                    ['id' => $slot['position']],
                    $slot
                );
            }

            return $slots;
        }

        $slots[$defined['position']] = array_merge(
            $this->slotDefaults(),
            $defaults,
            ['id' => $defined['position']],
            $defined
        );

        return $slots;
    }

    public function getSlot($model, $position)
    {
        return array_get($this->slots($model), $position);
    }

    public function response(Media $media)
    {
        if (!Filesystem::exists($media->filePath())) {
            return abort(404);
        }

        return $media->isImage() ? $this->imageResponse($media) : $this->fileResponse($media);
    }

    public function imageResponse(Media $media)
    {
        $image = $media->file();

        return $image->response();
    }

    public function fileResponse(Media $media)
    {
        $file = Filesystem::get($media->filePath());
        $response = response($file);
        $response->header('Content-Type', $media->mime_type);
        return $response;
    }

    public function mediaBox(Eloquent $model, $position)
    {
        $slot = self::getSlot($model, $position);
        extract($slot);

        $association_id = $model->getKey();

        Asset::enqueue($box_assets, 30);
        Asset::json('eminem', [
            'boxes' => [
                $id => [
                    'id' => $id,
                    'association' => Crypt::encrypt("{$association_type}|{$association_id}|{$position}"),
                    'allowMultiple' => $allow_multiple,
                ],
            ],
            'meta_url' => $meta_url,
            'general_error' => trans('clumsy/eminem::all.errors.general'),
        ]);

        $output = '';

        $media = false;
        if ($model->exists) {
            $media = $model->allMedia($position);
        }

        if (old('media_bind')) {

            $unbound = [];

            foreach (old('media_bind') as $mediaId => $attributes) {

                if ($attributes['position'] !== $position) {
                    continue;
                }

                $output .= view('clumsy/eminem::media-bind', [
                    'mediaId'       => $mediaId,
                    'position'      => $position,
                    'allowMultiple' => $attributes['allow_multiple']
                ]);

                $unbound[] = $mediaId;
            }

            if (count($unbound)) {
                $media = self::media()->whereIn('id', $unbound)->get();
            }
        }

        $comments = self::mediaSlotComments($slot);
        if (count($comments)) {
            $comments = '<ul><li><small>'.implode('</small></li><li><small>', $comments).'</small></li></ul>';
        }

        $output .= view($view_media_box, compact('id', 'label', 'media', 'slot', 'preview', 'comments', 'url'))->render();

        Event::listen('Print footer scripts', function () use ($id, $label, $media, $meta, $view_media_modal) {

            if (!$media) {
                $media = collect();
            }

            return view($view_media_modal, compact('id', 'label', 'media', 'meta'));
        });

        return $output;
    }

    public function mediaSlotComments($slot)
    {
        $output = [];

        extract($slot);

        $show_all = $show_comments && !is_array($show_comments);

        if (!$show_comments) {
            return $output;
        }

        if ($comments) {
            $output = (array)$comments;
        }

        if ($validate) {
            $rules = explode('|', $validate);
            foreach ($rules as $rule) {
                if (str_contains($rule, 'image') && ($show_all || in_array('mimes', $show_comments))) {
                    $types = ['jpeg', 'png', 'gif', 'bmp'];
                    $output[] = trans('clumsy/eminem::all.comments.mimes', ['values' => '<strong>'.implode(', ', $types).'</strong>']);
                }

                if (str_contains($rule, 'mimes') && ($show_all || in_array('mimes', $show_comments))) {
                    list($rule, $types) = explode(':', $rule);
                    $types = explode(',', $types);
                    $output[] = trans('clumsy/eminem::all.comments.mimes', ['values' => '<strong>'.implode(', ', $types).'</strong>']);
                }

                if (str_contains($rule, 'min') && ($show_all || in_array('min', $show_comments))) {
                    list($rule, $min) = explode(':', $rule);
                    $output[] = trans('clumsy/eminem::all.comments.min', ['min' => '<strong>'.$min.'</strong>']);
                }

                if (str_contains($rule, 'max') && ($show_all || in_array('max', $show_comments))) {
                    list($rule, $max) = explode(':', $rule);
                    $output[] = trans('clumsy/eminem::all.comments.max', ['max' => '<strong>'.$max.'</strong>']);
                }
            }
        }

        if ($allow_multiple && ($show_all || in_array('allow_multiple', $show_comments))) {
            $output[] = trans('clumsy/eminem::all.comments.allow_multiple');
        }

        return $output;
    }
}
