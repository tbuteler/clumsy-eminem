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

        'before' => '',

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
);