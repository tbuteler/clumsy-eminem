<?php

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Event;
use Clumsy\Eminem\Models\Media;
use Clumsy\Assets\Facade as Asset;
use Clumsy\Eminem\Facade as MediaManager;

if (!function_exists('array_is_associative')) {

    function array_is_associative($array)
    {
        return (bool)count(array_filter(array_keys($array), 'is_string'));
    }
}

if (!function_exists('mediaBox')) {

    function mediaBox(array $options = [])
    {
        $options = array_merge(MediaManager::slotDefaults(), $options);
        extract($options);

        Asset::enqueue('media-management.css', 30);
        Asset::enqueue('media-management.js', 30);
        Asset::json('eminem', ['boxes' => [[$id, $allow_multiple, Crypt::encrypt("{$association_type}|{$position}")]]]);
        Asset::json('eminem', [
            'meta_url'      => route('eminem.save-meta'),
            'general_error' => trans('clumsy/eminem::all.errors.general')
        ], true);

        $url = route('eminem.upload');

        $output = '';
        $media = false;

        if ($association_id) {
            $media = Media::associatedTo($association_id);

            if ($association_type) {
                $media->where('media_association_type', $association_type);
            }

            if ($position) {
                $media->where('position', $position);
            }

            $media = $media->get();
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
                $media = Media::whereIn('id', $unbound)->get();
            }
        }

        $comments = MediaManager::mediaSlotComments($options);
        if (count($comments)) {
            $comments = '<ul><li><small>'.implode('</small></li><li><small>', $comments).'</small></li></ul>';
        }

        $output .= view('clumsy/eminem::media-box', compact('id', 'label', 'media', 'options', 'comments', 'url'))->render();

        Event::listen('Print footer scripts', function () use ($id, $label, $media, $meta) {

            if (!$media) {
                $media = collect();
            }

            return view('clumsy/eminem::media-modal', compact('id', 'label', 'media', 'meta'));
        });

        return $output;
    }
}
