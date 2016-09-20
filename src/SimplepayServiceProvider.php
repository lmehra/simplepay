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
        //include __DIR__.'/routes.php';
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
        /*$this->app['Simplepay'] = $this->app->share(function($app) {
            return new makePayment;
        });*/
         // Bind captcha
        $this->app->singleton(Simplepay::class, function ($app) {
            return new Connection(config('simplepay'));
        });

        $this->app->bind('Simplepay', function($app)
        {
            return new Simplepay(
                $app['Mansa\Simplepay\GetSyncCallParameters'],
                $app['Mansa\Simplepay\GetAsyncCallParameters'],
                $app['Mansa\Simplepay\Exceptions\PaymentGatewayVerificationFailedException'],
                $app['Mansa\Simplepay\Exceptions\VariableValidationException'],
                $app['Mansa\Simplepay\ResultCheck'],
                $app['Mansa\Simplepay\SimplepayResponse'],
                $app['BadMethodCallException']
            );
        });
    }
}
?>