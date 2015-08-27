<?php

/*
 |--------------------------------------------------------------------------
 | Media management settings
 |--------------------------------------------------------------------------
 |
 |
 */

return array(

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
     | Route filters
     |--------------------------------------------------------------------------
     |
     | Any route filters you'd like to add before or after media routes can be
     | declared here.
     |
     */

    'input-filters-before' => 'csrf',
    'input-filters-after'  => '',

    /*
     |--------------------------------------------------------------------------
     | Media output (Processing and response)
     |--------------------------------------------------------------------------
     */

    'output-prefix' => '',

    'output-filters-before' => '',
    'output-filters-after'  => '',

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

     'placeholder-folder' => 'packages/clumsy/eminem/img',
);