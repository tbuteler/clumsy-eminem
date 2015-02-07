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
     | Admin prefix
     |--------------------------------------------------------------------------
     |
     | URL prefix to prepend on default media routes
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
     | Uploads folder
     |--------------------------------------------------------------------------
     |
     | Location within the public folder in which to keep media files
     |
     */

    'folder' => 'uploads',

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
     | Preview mime types
     |--------------------------------------------------------------------------
     |
     | The following mime types will try to be rendered by the browser
     |
     */

    'preview-mime-types'  => array(
        'image/pjpeg',
        'image/jpeg',
        'image/gif',
        'image/png',
        'image/bmp',
        'image/png',
    ),

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