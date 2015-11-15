<?php

namespace Clumsy\Eminem;

use Illuminate\Support\ServiceProvider;
use Clumsy\Assets\Facade as Asset;
use Clumsy\Eminem\Media;

class EminemServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register('Intervention\Image\ImageServiceProvider');

        $this->mergeConfigFrom(__DIR__.'/Support/config/config.php', 'clumsy/eminem');

        $this->app->bind('eminem', function ($app) {
			return new MediaManager;
        });
    }

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/Support/lang', 'clumsy/eminem');

        $this->publishes([
            __DIR__.'/Support/config/config.php'  => config_path('vendor/clumsy/eminem/config.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/clumsy/eminem'),
        ], 'public');

	    $this->publishes([
	        __DIR__.'/../database/migrations' => database_path('migrations')
	    ], 'migrations');

        $this->registerRoutes();

        require __DIR__.'/Support/helpers.php';
        require __DIR__.'/Support/macros/form.php';
        require __DIR__.'/Support/macros/html.php';

        $assets = include(__DIR__.'/assets/assets.php');
        Asset::batchRegister($assets);

        $this->loadViewsFrom(__DIR__.'/views', 'clumsy/eminem');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array(
            'eminem',
        );
    }

    public function registerRoutes()
    {
        /*
		|--------------------------------------------------------------------------
		| Uploading and editing
		|--------------------------------------------------------------------------
		|
		*/

        $this->app['router']->group([
                'prefix'     => config('clumsy/eminem::input-prefix'),
                'middleware' => config('clumsy/eminem::input-middleware'),
            ], function () {

                $this->app['router']->match(['POST', 'PUT'], 'media-upload', [
                    'as'   => 'eminem.upload',
                    'uses' => 'Clumsy\Eminem\Controllers\MediaController@upload'
                ]);

                $this->app['router']->post('media-save-meta/{id?}', [
                    'as'   => 'eminem.save-meta',
                    'uses' => 'Clumsy\Eminem\Controllers\MediaController@meta'
                ]);
            }
        );

        /*
		|--------------------------------------------------------------------------
		| Processing and response
		|--------------------------------------------------------------------------
		|
		*/

        $this->app['router']->group([
                'prefix'     => config('clumsy/eminem.output-prefix'),
                'middleware' => config('clumsy/eminem.output-middleware'),
            ], function () {

                $this->app['router']->pattern('eminemMedia', '.+'); // Allows media path to have forward slashes

                $this->app['router']->bind('eminemMedia', function ($value) {
                    return Media::where('path', $value)->first();
                });

                $this->app['router']->get('eminem/output/{eminemMedia}', [
                    'as' => 'eminem.media-route',
                    function (Media $media) {
                        return $this->app['eminem']->response($media);
                    }
                ]);
            }
        );
    }
}
