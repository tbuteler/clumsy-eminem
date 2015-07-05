<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;

/*
|--------------------------------------------------------------------------
| Media Routes
|--------------------------------------------------------------------------
|
*/

Route::group(
    array(
        'prefix' => Config::get('clumsy/eminem::prefix'),
        'before' => Config::get('clumsy/eminem::filters.before'),
        'after'  => Config::get('clumsy/eminem::filters.after'),
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

Route::bind('media', function($value) {
    return Clumsy\Eminem\Models\Media::where('path', $value)->first();
});

Route::get('eminem/process/{media}', array(
    'as'   => 'eminem.media-route',
    'uses' => 'Clumsy\Eminem\Controllers\MediaController@routedMedia'
))->where('media', '.+'); // Allows media path to have forward slashes