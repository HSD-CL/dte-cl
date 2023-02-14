<?php

namespace HSDCL\DteCl;

use HSDCL\DteCl\Console\Commands\ExemptCertificationCommand;
use HSDCL\DteCl\Console\Commands\ShipmentBookCertificactionCommand;
use HSDCL\DteCl\Console\Commands\ShipmentCertificationCommand;
use HSDCL\DteCl\Console\Commands\PurchaseBookCertificationCommand;
use HSDCL\DteCl\Console\Commands\BasicCertificationCommand;
use HSDCL\DteCl\Console\Commands\ExportCertificactionCommand;
use HSDCL\DteCl\Console\Commands\SalesBookCertificationCommand;
use HSDCL\DteCl\Console\Commands\ShipmentCertificactionExportPdfCommand;
use Illuminate\Support\ServiceProvider;

class DteClServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'dte-cl');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'dte-cl');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('dte-cl.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/dte-cl'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/dte-cl'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/dte-cl'),
            ], 'lang');*/

            // Registering package commands.
            $this->commands([
                BasicCertificationCommand::class,
                ExemptCertificationCommand::class,
                ShipmentCertificationCommand::class,
                ShipmentBookCertificactionCommand::class,
                PurchaseBookCertificationCommand::class,
                SalesBookCertificationCommand::class,
                ShipmentBookCertificactionCommand::class,
                ExportCertificactionCommand::class,
                ShipmentCertificactionExportPdfCommand::class
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'dte-cl');

        // Register the main class to use with the facade
        $this->app->singleton('dte-cl', function () {
            return new DteCl;
        });
    }
}
