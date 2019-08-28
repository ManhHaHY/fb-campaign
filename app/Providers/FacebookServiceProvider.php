<?php

namespace App\Providers;

use App\Helpers\FacebookPersistentDataHandler;
use App\Helpers\UrlDetectionHandler;
use Illuminate\Support\ServiceProvider;
use Facebook\Facebook;
use Illuminate\Session\Store as Session;

class FacebookServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Facebook::class, function ($app) {
            $config = config('custom.facebook');
            $config['persistent_data_handler'] = new FacebookPersistentDataHandler($app['session.store']);
            $config['url_detection_handler'] = new UrlDetectionHandler($app['url']);
            return new Facebook($config);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
