<?php

HTML::macro('mediaModal', function($id, $label, $media)
{
    if (!$media)
    {
        $media = new Illuminate\Support\Collection();
    }

    return View::make('clumsy/eminem::media-modal', compact('id', 'label', 'media'));
});