<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;

/*
|--------------------------------------------------------------------------
| Uploading and editing
|--------------------------------------------------------------------------
|
*/

Route::group(
    array(
        'prefix' => Config::get('clumsy/eminem::input-prefix'),
        'before' => Config::get('clumsy/eminem::input-filters-before'),
        'after'  => Config::get('clumsy/eminem::input-filters-after'),
    ),
    function()
    {
        Route::match(array('POST', 'PUT'), 'media-upload', array(
            'as'   => 'eminem.upload',
            'uses' => 'Clumsy\Eminem\Controllers\MediaController@upload'
        ));

        Route::post('media-save-meta/{id?}', array(
            'as'   => 'eminem.save-meta',
            'uses' => 'Clumsy\Eminem\Controllers\MediaController@meta'
        ));
    }
);


/*
|--------------------------------------------------------------------------
| Processing and response
|--------------------------------------------------------------------------
|
*/

Route::group(
    array(
        'prefix' => Config::get('clumsy/eminem::output-prefix'),
        'before' => Config::get('clumsy/eminem::output-filters-before'),
        'after'  => Config::get('clumsy/eminem::output-filters-after'),
    ),
    function()
    {
        Route::pattern('eminemMedia', '.+'); // Allows media path to have forward slashes

        Route::bind('eminemMedia', function($value) {
            return Media::where('path', $value)->first();
        });

        Route::get('eminem/output/{eminemMedia}', array(
            'as' => 'eminem.media-route',
            function(Media $media)
            {
                return MediaManager::response($media);
            }
        ));
    }
);