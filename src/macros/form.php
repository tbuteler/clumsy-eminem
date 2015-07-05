<?php

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Form;
use Illuminate\Support\Facades\HTML;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Event;
use Clumsy\Eminem\Models\Media;
use Clumsy\Assets\Facade as Asset;
use Clumsy\Eminem\Facade as MediaManager;

/*
|--------------------------------------------------------------------------
| Media upload button
|--------------------------------------------------------------------------
|
| This macro creates a button which allows users to upload media
| with drag and drop and AJAX functionality
|
|
*/

Form::macro('media', function($options = array())
{
    $options = array_merge(MediaManager::slotDefaults(), $options);
    extract($options, EXTR_SKIP);

    Asset::enqueue('media-management.css', 30);
    Asset::enqueue('media-management.js', 30);
    Asset::json('eminem', array('boxes' => array(array($id, $allow_multiple, Crypt::encrypt("{$association_type}|{$position}")))));
    Asset::json('eminem', array(
        'meta_url'      => URL::route('eminem.save-meta'),
        'general_error' => trans('clumsy/eminem::all.errors.general')
    ), $replace = true);

    $url = URL::route('eminem.upload');

    $output = '';
    $media = false;

    if ($association_id)
    {
        $media = Media::associatedTo($association_id);
                      
        if ($association_type)
        {
            $media->where('media_association_type', $association_type);
        }

        if ($position)
        {
            $media->where('position', $position);
        }

        $media = $media->get();

    }
    else
    {
        if (Input::old('media_bind'))
        {
            $unbound = array();

            foreach (Input::old('media_bind') as $media_id => $attributes)
            {
                if ($attributes['position'] !== $position)
                {
                    continue;
                }

                $output .= Form::mediaBind($media_id, $position, $attributes['allow_multiple']);
                
                $unbound[] = $media_id;
            }

            if (sizeof($unbound))
            {
                $media = Media::whereIn('id', $unbound)->get();
            }
        }
    }

    $comments = MediaManager::mediaSlotComments($options);
    if (sizeof($comments))
    {
        $comments = '<ul><li><small>'.implode('</small></li><li><small>', $comments).'</small></li></ul>';
    }

    $output .= View::make('clumsy/eminem::media-box', compact('id', 'label', 'media', 'options', 'comments', 'url'))->render();

    Event::listen('Print footer scripts', function() use($id, $label, $media, $meta)
    {
        return HTML::mediaModal($id, $label, $media, $meta);
    });

    return $output;
});

/*
|--------------------------------------------------------------------------
| Media bind input
|--------------------------------------------------------------------------
|
| Creates hidden inputs to bind media to models
|
*/

Form::macro('mediaBind', function($media_id, $position = null, $allow_multiple = false)
{
    $output = Form::hidden("media_bind[{$media_id}][position]", $position);
    $output .= Form::hidden("media_bind[{$media_id}][allow_multiple]", $allow_multiple);

    return $output;
});