<?php 
namespace Mansa\Simplepay;

use Mansa\Simplepay\SimplepayRequest;
use Illuminate\Support\ServiceProvider;

class SimplepayRequestServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('SimplepayRequest',function(){
            return new SimplepayRequest();
        });
    }
}
?>