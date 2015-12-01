<?php

return [

    'jquery' => [
        'set'   => 'footer',
        'path'  => '//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
    ],

    'bootstrap' => [
        'set'   => 'styles',
        'path'  => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css',
    ],

    'bootstrap.js' => [
        'set'   => 'footer',
        'path'  => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js',
        'req'   => 'jquery',
    ],

    'media-management.css' => [
        'set'    => 'styles',
        'path'   => 'vendor/clumsy/eminem/css/media-management.css',
        'req'    => 'bootstrap',
        'v'      => '0.9.1',
        'elixir' => false,
    ],

    'media-management.js' => [
        'set'    => 'footer',
        'path'   => 'vendor/clumsy/eminem/js/media-management.min.js',
        'req'    => 'bootstrap.js',
        'v'      => '0.9.1',
        'elixir' => false,
    ],
];
