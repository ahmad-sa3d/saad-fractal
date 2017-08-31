<?php

namespace Saad\Fractal;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Spatie\Fractal\Fractal
 */
class FractalFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'saad-fractal';
    }
}
