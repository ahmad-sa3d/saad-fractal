# An easy to use Fractal wrapper built for Laravel applications upon Spatie/Fractal Package

[Spatie/Fractal](https://github.com/spatie/laravel-fractal)

## Install

You can pull in the package via composer:

```bash
	composer require saad/fractal
```

The package will automatically register itself.

### Laravel Version
this package is compatible with laravel versions `>= 5.5`

### Changelog
> __V 1.2.0__
1. Add Strict Mode, so for null resources instead of returning empty array it will return null,
By Default strict mode is Enabled
    
## use

Exactly as spatie except that this package is automatically parse includes or excludes from parameters first if defined, otherwise it will look for query string includes and excludes

Default serializer is `ArraySerializer`

### `Console Generator`

you can generate a new transformer class using the following command

the following command will create "App\Transformers\UserTransformer.php"

```bash
	php artisan make:transformer 'App\User'
```

to create in nested folders "App\Transformers\Sub1\Sub2\UserTransformer.php"

```bash
	php artisan make:transformer 'App\User' --nest='Sub1\Sub2'
	
	# Nest Name could be:
	
	# Sub1/Sub2
	# /Sub1/Sub2/
	# Sub1\\Sub2
```

### `Request Includes`

> will include defined includes from __`availableIncludes`__ array

``` php
	
	// assume that Request Url = /countries?include=name,code,iso
	
	$couintries = Country::all();
	$transformed_data = \Saad\Fractal\Fractal::create($countries, new CountryTransformer());
	
	// CountryTransformer will include name, code and iso
	
```

### `Force Includes`
> we could also pass includes to create method as the `4th argument` which will have the heighest periority than request include
> will include defined includes from __`availableIncludes`__ array
	

``` php
	
	// assume that Request Url = /countries?include=name,code,iso
	
	$couintries = Country::all();
	$transformed_data = \Saad\Fractal\Fractal::create($countries, new CountryTransformer(), null, 'name,iso');
	
	// CountryTransformer will include only name and iso
	
```

### `Request Excludes`
> will exclude defined includes from __`defaultIncludes`__ array

``` php
	
	// assume that Request Url = /countries?exclude=name,code
	
	$couintries = Country::all();
	$transformed_data = \Saad\Fractal\Fractal::create($countries, new CountryTransformer());
	
	// CountryTransformer will exclude name and code from default includes
	
```

### `Force Excludes`
>we could also pass excludes to create method as the `5th argument` which will have the heighest periority than request include
> will include defined includes from __`defaultIncludes `__ array
	

``` php
	
	// assume that Request Url = /countries?exclude=name,code,iso
	
	$couintries = Country::all();
	$transformed_data = \Saad\Fractal\Fractal::create($countries, new CountryTransformer(), null, null, 'name');
	
	// CountryTransformer will exclude only name from defaultIncludes
	
```

## Transformer Abstract Class

this package has a base abstract Transformer Class `Saad\Fractal\Transformers\TransformerAbstract` you could use as the base class of your transformers, this class is based on extends `League\Fractal\TransformerAbstract` and adds the following features:
### *`TransformerAbstract::strictMode(bool)`*
> This mode added since V 1.2.0 <br>

null resources is returning empty array, when enable strict mode it will return NULL instead.
By default strict mode is enabled

To control strict mode you could call one of these methods in one of the service providers boot method:

> Enable Strict Mode (Enabled By Default): <br>
> `TransformerAbstract::strictMode(true)` <br>
> `TransformerAbstract::enableStrictMode()`

> Disable Strict Mode: <br>
> `TransformerAbstract::strictMode(false)` <br>
> `TransformerAbstract::disableStrictMode()`

``` php

    // Assume we have this transformer
    class CountryTransformer extends TransformerAbstract {
        ...
        
        includeRegions(Country $country) {
            // assume there are no regions
            return $this->null();
        }
    }
    
	$transformer = new CountryTransformer();
	
	$output = Fractal::create($country, $transformer);
	
	// output when strict mode is enabled (default status)
	[
	    ...
		'regions' => null,
	]
	
	
	// Disable Strict Mode
	TransformerAbstract::disableStrictMode();
	
	$output = Fractal::create($country, $transformer);
    	
    // output when strict mode is disabled
    [
        ...
        'regions' => [],
    ]
	
```

### *`transform()`* replaced by *`transforWithDefault()`*
> you should use __`transforWithDefault()`__ methodcinstead of __`transform()`__ method
> this is because the new `addEexternal` feature

### *`addExternal($key, $value)`*
> you can add external value to output

``` php
	$transformer = new CountryTransformer();
	
	$output = Fractal::create($country, $transformer);
	
	// assume this output of country is 
	[
		'name' => 'Egypt',
		'iso' => 'EG' 
	]
	
	
	// If we want to add another key to output
	
	$transformer->addExternal('new_key', 'Iam New Value');
	$output = Fractal::create($country, $transformer);
	
	// Then output of country will be
	[
		'name' => 'Egypt',
		'iso' => 'EG',
		'new_key' => 'Iam New Value',
	]
	
	
	// We can also add external which have a calculated value depends on transformed object
	// assume we want to add new key to output named 'name_iso' which its value is the concatenation of both 'name' and 'iso' properies
	
	$transformer->addExternal('name_iso', function ($country_object) {
		return $country_object->name . '_' . $country_object->iso;
	});
	
	$output = Fractal::create($country, $transformer);
	
	// Then output of country will be
	[
		'name' => 'Egypt',
		'iso' => 'EG',
		'name_iso' => 'Egypt_EG',
	]
	
	
```

### *`addDefaultInclude(string|array $defaults_to_add)`*
> will add provided keys to defaultIncludes array

``` php

	// assume that defaultIncludes are ['id', 'name']
	$transformer = new CountryTransformer();
	$transformer->addDefaultInclude(['iso']);
	
	$output = Fractal::create($country, $transformer);
	
	// assume this output of country is 
	[
		'id' => 1,
		'name' => 'Egypt',
		
		'iso' => 'EG' // added to defaultIncludes
	]
```

## Fractal Request Parser Singletone
this package also contains a singletone __`Saad\Fractal\FractalRequestParser`__
which is a helper class that provides usefull methods about Request includes and excludes
with the following methods :

__`Saad\Fractal\FractalRequestParser::includesHas($key_path)`__

__`Saad\Fractal\FractalRequestParser::excludesHas($key_path)`__

> assume we have the following request URI `?include=name,sub.name:lang(ar),sub.country`

``` php
 	$parser = Saad\Fractal\FractalRequestParser::getInstance();
 
 	// we can check the following
 	$parser->includesHas('name');  // true
 	$parser->includesHas('sub');  // true
 	$parser->includesHas('sub.name');  // true
 	$parser->includesHas('sub.country');  // true
 	
 	$parser->includesHas('iso');  // false
 	$parser->includesHas('sub.country.name');  // false
```

the same for __`excludesHas()`__
