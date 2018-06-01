<?php

namespace Autumndev\Checkmend;

use Illuminate\Support\ServiceProvider;
use Autumndev\Checkmend\Checkmend;

class CheckmendServiceProvider extends ServiceProvider
{
    protected $defer = true;

	public function boot() {
		$this->publishes([
			__DIR__.'/../config/checkmend.php' => config_path('checkmend.php'),
		], 'checkmend');
    }
    
    public function register() {
		$this->mergeConfigFrom( __DIR__.'/../config/checkmend.php', 'checkmend');
        $this->app->singleton('checkmend', function($app) {

            $config = $app->make('config');

            $baseUri        = $config->get('checkmend.baseURI');
            $partnerId      = $config->get('checkmend.partnerId');
            $secret         = $config->get('checkmend.secret');
            $organisationId = $config->get('checkmend.organisationId');
            $storeId        = $config->get('checkmend.storeId');
            $logging        = $config->get('checkmend.logging');

            return new Checkmend(
                $baseUri, 
                $partnerId, 
                $secret,
                $organisationId,
                $storeId
            );
        });
    }

    public function provides() {
        return ['mongo'];
    }
}