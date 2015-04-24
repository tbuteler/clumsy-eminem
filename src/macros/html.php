<?php

HTML::macro('mediaModal', function($id, $label, $media, $meta)
{	
    if (!$media)
    {
        $media = new Illuminate\Support\Collection();
    }

    return View::make('clumsy/eminem::media-modal', compact('id', 'label', 'media','meta'));
});