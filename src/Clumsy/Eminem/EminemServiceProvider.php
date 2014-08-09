<?php namespace Clumsy\Eminem;

use Illuminate\Support\ServiceProvider;
use Clumsy\Assets\Facade as Asset;
use Clumsy\Eminem\MediaManager;

class EminemServiceProvider extends ServiceProvider {

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
        $this->app['eminem'] = new MediaManager;
	}

	/**
	 * Boot the service provider.
	 *
	 * @return void
	 */
	public function boot()
	{
        $this->package('clumsy/eminem', 'clumsy/eminem');
        $this->app['config']->package('clumsy/eminem', $this->guessPackagePath() . '/config');

        $assets = include($this->guessPackagePath() . '/assets/assets.php');
		Asset::batchRegister($assets);

		require $this->guessPackagePath().'/routes.php';

		require $this->guessPackagePath().'/macros/form.php';
		require $this->guessPackagePath().'/macros/html.php';
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
