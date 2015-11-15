<?php

use Collective\Html\HtmlFacade as HTML;
use Illuminate\Support\Collection;

HTML::macro('mediaModal', function ($id, $label, $media, $meta) {

    if (!$media) {
        $media = new Collection();
    }

    return view('clumsy/eminem::media-modal', compact('id', 'label', 'media', 'meta'));
});

HTML::macro('mediaImage', function ($media) {

    return HTML::image($media->previewURL(), null, array(
        'data-src'      => $media->url(),
        'data-media-id' => $media->id,
    ));
});
