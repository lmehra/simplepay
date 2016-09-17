<?php namespace Mansa\Simplepay;

use Illuminate\Support\ServiceProvider;

class SimplepayServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Route
        include __DIR__.'/routes.php';
         $this->publishes([
            __DIR__.'/Config/Simplepay.php' => config_path('Simplepay.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom( __DIR__.'/Config/simplepay.php', 'simplepay');
        $this->app['Simplepay'] = $this->app->share(function($app) {
            return new Simplepay;
        });
    }
}
?>