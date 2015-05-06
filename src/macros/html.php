<?php

HTML::macro('mediaModal', function($id, $label, $media, $meta)
{	
    if (!$media)
    {
        $media = new Illuminate\Support\Collection();
    }

    return View::make('clumsy/eminem::media-modal', compact('id', 'label', 'media','meta'));
});

HTML::macro('mediaImage', function($media)
{	
    return HTML::image($media->previewPath(), null, array(
    	'data-src'            => $media->path(),
    	'data-media-id'       => $media->id,
    ));
});