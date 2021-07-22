<?php

declare(strict_types=1);

namespace Autumndev\Checkmend;

use Illuminate\Support\ServiceProvider;
use Autumndev\Checkmend\Checkmend;

class CheckmendServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/checkmend.php' => config_path('checkmend.php'),
        ], 'checkmend');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/checkmend.php', 'checkmend');
        $this->app->singleton('checkmend', function ($app) {
            $config = $app->make('config');

            $baseUri            = $config->get('checkmend.baseURI');
            $partnerId          = $config->get('checkmend.partnerId');
            $secret             = $config->get('checkmend.secret');
            $organisationId     = $config->get('checkmend.organisationId');
            $storeId            = $config->get('checkmend.storeId');
            $logging            = $config->get('checkmend.logging');
            $timeout            = $config->get('checkmend.timeout');
            $reseller           = $config->get('checkmend.reseller');
            $resellerDetails    = $config->get('checkmend.resellerDetails');

            return new Checkmend(
                $baseUri,
                $partnerId,
                $secret,
                $organisationId,
                $storeId,
                $logging,
                $timeout,
                $reseller,
                $resellerDetails
            );
        });
    }

    public function provides()
    {
        return ['checkmend'];
    }
}
