# An easy to use Fractal wrapper built for Laravel applications upon Spatie/Fractal Package

[Spatie/Fractal](https://github.com/spatie/laravel-fractal)

## Install

You can pull in the package via composer:
``` bash
$ composer require saad/fractal
```

The package will automatically register itself.

## use

```php

// Exactly as spatie except that this package is automatically parse includes or excludes from parameters first if defined, otherwise it will look for query string includes and excludes
// Default serializer is ArraySerializer
$data = \Saad\Fractal\Fractal::create( $data, new TransformerClass(), new ArraySerializer(), $include, $exclude );

```