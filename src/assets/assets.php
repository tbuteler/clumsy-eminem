<?php

return [

    'jquery' => [
        'set'     => 'footer',
        'path'    => '//ajax.googleapis.com/ajax/libs/jquery/{{version}}/jquery.min.js',
        'version' => '3.1.1',
    ],

    'bootstrap.js' => [
        'set'      => 'footer',
        'path'     => '//maxcdn.bootstrapcdn.com/bootstrap/{{version}}/js/bootstrap.min.js',
        'version'  => '3.3.7',
        'requires' => 'jquery',
    ],

    'media-management.css' => [
        'set'     => 'styles',
        'path'    => 'vendor/clumsy/eminem/css/media-management.css',
        'hash'    => false,
        'version' => '0.14.0',
    ],

    'media-management.js' => [
        'set'      => 'footer',
        'path'     => 'vendor/clumsy/eminem/js/media-management.min.js',
        'requires' => 'bootstrap.js',
        'hash'     => false,
        'version'  => '0.14.0',
    ],
];
