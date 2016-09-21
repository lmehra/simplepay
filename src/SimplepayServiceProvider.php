<?php 
namespace Mansa\Simplepay;

use Mansa\Simplepay\Simplepay;
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
        $this->publishes([
            __DIR__.'/Config/simplepay.php' => config_path('simplepay.php'),
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

         $this->app->singleton('simplepay', function () {
            return new simplepay(); 
        });
    }
}
?>