<?php

/*
 |--------------------------------------------------------------------------
 | Media management settings
 |--------------------------------------------------------------------------
 |
 |
 */

return [

    /*
     |--------------------------------------------------------------------------
     | Media model
     |--------------------------------------------------------------------------
     |
     | The model to use for media within Eminem. If not using Eminem's default
     | Media model, the overriding model must at least extend it.
     |
     */

    'media-model' => \Clumsy\Eminem\Models\Media::class,

    /*
     |--------------------------------------------------------------------------
     | Default path type
     |--------------------------------------------------------------------------
     |
     | Whether you want your media to be stored in your public folder and be
     | directly accessible or served by Laravel via routes and controllers,
     | so you can process it or secure it inside an admin area.
     |
     | Individual media slots can override this setting.
     |
     | Supported: "public", "routed"
     |
     |
     */

    'default-path-type' => 'public',

    /*
     |--------------------------------------------------------------------------
     | Media input (Uploading and editing)
     |--------------------------------------------------------------------------
     */

    /*
     |--------------------------------------------------------------------------
     | URL prefix
     |--------------------------------------------------------------------------
     |
     | URL prefix to prepend on back-end media routes
     |
     */

    'input-prefix' => '',

    /*
     |--------------------------------------------------------------------------
     | Route middleware
     |--------------------------------------------------------------------------
     |
     | Any middleware you'd like to add to media routes can be declared here.
     |
     */

    'input-middleware' => [],

    /*
     |--------------------------------------------------------------------------
     | Media output (Processing and response)
     |--------------------------------------------------------------------------
     */

    'output-prefix' => '',

    'output-middleware' => [
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],

    /*
     |--------------------------------------------------------------------------
     | Upload folders
     |--------------------------------------------------------------------------
     |
     | Location within the public / storage folders in which to keep media files
     |
     */

    'folder' => 'media',

    /*
     |--------------------------------------------------------------------------
     | Organize uploads folder
     |--------------------------------------------------------------------------
     |
     | Whether or not to organize the uploads folder in year/month subfolders
     |
     */

    'organize'  => true,

    /*
     |--------------------------------------------------------------------------
     | Placeholder images base path
     |--------------------------------------------------------------------------
     |
     | Mime types which are not rendered by the browser will show a placeholder
     | image instead. This is the folder in which we'll look for the images.
     |
     | Note: it must be inside your app's public path
     |
     */

     'placeholder-folder' => 'vendor/clumsy/eminem/img',
];
