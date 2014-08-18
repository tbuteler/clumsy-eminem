<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Clumsy\Assets\Facade as Asset;

/*
|--------------------------------------------------------------------------
| Media Routes
|--------------------------------------------------------------------------
|
*/

Route::group(
    array(
        'prefix' => Config::get('eminem::prefix'),
        'before' => Config::get('eminem::filters.before'),
        'after'  => Config::get('eminem::filters.after'),
    ),
    function()
    {
        Route::match(array('POST', 'PUT'), 'media-upload/{object?}/{position?}', array(
            'as'   => 'media.upload',
            'uses' => 'Clumsy\Eminem\Controllers\MediaController@upload'
        ));

        Route::post('media-unbind/{id?}', array(
            'as'   => 'media.unbind',
            'uses' => 'Clumsy\Eminem\Controllers\MediaController@unbind'
        ));
 
        Asset::json('media', array(
            'unbind_url' => URL::route('media.unbind')
        ));
    }
);