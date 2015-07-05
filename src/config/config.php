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
     | Routed media alias
     |--------------------------------------------------------------------------
     |
     | The name / alias of the route which resolves non-public media.
     | There is a default route binding, route and controller method to resolve
     | private media, but these can be overridden, if desired.
     |
     |
     */

    'media-route' => 'eminem.media-route',

    /*
     |--------------------------------------------------------------------------
     | URL prefix
     |--------------------------------------------------------------------------
     |
     | URL prefix to prepend on back-end media routes
     |
     */

	'prefix' => '',

    /*
     |--------------------------------------------------------------------------
     | Route filters
     |--------------------------------------------------------------------------
     |
     | Any route filters you'd like to add before or after media routes can be
     | declared here.
     |
     */

    'filters' => array(

        'before' => 'csrf',

        'after'  => '',
    ),

    /*
     |--------------------------------------------------------------------------
     | Upload folders
     |--------------------------------------------------------------------------
     |
     | Location within the public / storage folders in which to keep media files
     |
     */

    'public-folder' => 'media',

    'routed-folder' => 'media',

    /*
     |--------------------------------------------------------------------------
     | Organize uploads folder
     |--------------------------------------------------------------------------
     |
     | Whether or not to organize the uploads folder in year/month subfolders
     |
     */

    'organize'  => false,

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