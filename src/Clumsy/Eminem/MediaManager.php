<?php namespace Clumsy\Eminem;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File as Filesystem;
use Illuminate\Support\Facades\Response;
use Clumsy\Eminem\Models\Media;
use Clumsy\Eminem\Models\MediaAssociation;
use Clumsy\Eminem\File\MediaFile;

class MediaManager {

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
        return array(
            'id'               => 'media',
            'label'            => 'Media',
            'association_type' => null,
            'association_id'   => null,
            'position'         => 'media',
            'path_type'        => Config::get('clumsy/eminem::default-path-type'),
            'allow_multiple'   => false,
            'validate'         => null,
            'meta'             => null,
            'media_meta'       => null,
            'show_comments'    => true,
            'comments'         => null,
        );
    }

    public function slots($model, $id = null)
    {
        $slots = array();

        if (!method_exists($model, 'mediaSlots'))
        {
            return $slots;
        }

        if (!is_object($model))
        {
            $model = new $model;
        }

        $defined = $model->mediaSlots();

        if (!is_array($defined))
        {
            return $slots;
        }

        $defaults = array(
            'association_type' => get_class($model),
            'association_id'   => $id,
        );
        
        if (!array_is_associative($defined))
        {
            foreach ($defined as $slot)
            {
                $slots[$slot['position']] = array_merge(
                    $this->slotDefaults(),
                    $defaults,
                    array('id' => $slot['position']),
                    $slot
                );
            }

            return $slots;
        }

        $slots[$defined['position']] = array_merge(
            $this->slotDefaults(),
            $defaults,
            array('id' => $defined['position']),
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
        if (!Filesystem::exists($media->filePath()))
        {
            return App::abort(404);
        }

        return $media->isImage() ? $this->imageResponse($media) : $this->fileResponse($media);
    }

    public function imageResponse($media)
    {
        $image = $media->file();

        return $image->response();
    }

    public function fileResponse($media)
    {
        $file = Filesystem::get($media->filePath());
        $response = Response::make($file, 200);
        $response->header('Content-Type', $media->mime_type);
        return $response;
    }

    public function mediaSlotComments($options = array())
    {
        $output = array();

        $defaults = array(
            'show_comments'     => true,
            'allow_multiple'    => false,
            'validate'          => null,
            'comments'          => null,
        );

        $options = array_merge($defaults, $options);
        extract($options, EXTR_SKIP);

        $show_all = $show_comments && !is_array($show_comments);

        if (!$show_comments)
        {
            return $output;
        }

        if ($comments)
        {
            $output = (array)$comments;
        }

        if ($validate)
        {
            $rules = explode('|', $validate);
            foreach ($rules as $rule)
            {
                if (str_contains($rule, 'image') && ($show_all || in_array('mimes', $show_comments)))
                {
                    $types = array('jpeg', 'png', 'gif', 'bmp');
                    $output[] = trans('clumsy/eminem::all.comments.mimes', array('values' => '<strong>'.implode(', ', $types).'</strong>'));
                }

                if (str_contains($rule, 'mimes') && ($show_all || in_array('mimes', $show_comments)))
                {
                    list($rule, $types) = explode(':', $rule);
                    $types = explode(',', $types);
                    $output[] = trans('clumsy/eminem::all.comments.mimes', array('values' => '<strong>'.implode(', ', $types).'</strong>'));
                }

                if (str_contains($rule, 'min') && ($show_all || in_array('min', $show_comments)))
                {
                    list($rule, $min) = explode(':', $rule);
                    $output[] = trans('clumsy/eminem::all.comments.min', array('min' => '<strong>'.$min.'</strong>'));
                }

                if (str_contains($rule, 'max') && ($show_all || in_array('max', $show_comments)))
                {
                    list($rule, $max) = explode(':', $rule);
                    $output[] = trans('clumsy/eminem::all.comments.max', array('max' => '<strong>'.$max.'</strong>'));
                }
            }
        }

        if ($allow_multiple && ($show_all || in_array('allow_multiple', $show_comments)))
        {
            $output[] = trans('clumsy/eminem::all.comments.allow_multiple');
        }

        return $output;
    }
}