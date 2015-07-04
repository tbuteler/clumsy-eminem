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
        Route::match(array('POST', 'PUT'), 'media-upload/{position?}', array(
            'as'   => 'media.upload',
            'uses' => 'Clumsy\Eminem\Controllers\MediaController@upload'
        ));

        Route::post('media-unbind/{id?}', array(
            'as'   => 'media.unbind',
            'uses' => 'Clumsy\Eminem\Controllers\MediaController@unbind'
        ));

        Route::post('media-save-meta/{id?}', array(
            'as'   => 'media.save-meta',
            'uses' => 'Clumsy\Eminem\Controllers\MediaController@meta'
        ));
    }
);