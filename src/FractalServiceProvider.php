<?php

/**
 * @package  saad/fractal
 *
 * @author Ahmed Saad <a7mad.sa3d.2014@gmail.com>
 * @license MIT MIT
 */

namespace Saad\Fractal;

use Illuminate\Support\ServiceProvider;
use Saad\Fractal\Commands\MakeTransformer;
use Spatie\Fractal\FractalFacade as SpatieFractalFacade;
use Spatie\Fractal\FractalServiceProvider as SpatieFractalServiceProvider;

class FractalServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register Generator Command
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeTransformer::class,
            ]);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // Bind Spatie package Service Provider
        $this->app->register(
            SpatieFractalServiceProvider::class
        );

        // Bind Spatie Package Aliace [ FACADE ]
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('Fractal', SpatieFractalFacade::class);


        // Add To service Container
        $this->app->bind('saad-fractal', function (...$arguments) {
            return Fractal::create(...$arguments);
        });

        $this->app->alias('saad-fractal', FractalFacade::class);
    }
}
