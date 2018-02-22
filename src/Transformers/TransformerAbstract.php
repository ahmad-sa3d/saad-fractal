<?php

/**
 * This Abstract Class extends League Transformer
 * Adds more functionality like add external, add Default include to default includes
 *
 * Important note is the "transform" method is here defined as final
 * so you cannot define it in your concrete class and you have to use anew method called
 * "transformWithDefault"
 *
 * @author Ahmed saad <a7mad.sa3d.2014@gmail.com>
 * @license MIT
 */

namespace Saad\Fractal\Transformers;

use League\Fractal\TransformerAbstract as BaseTransformerAbstract;
use Exception;

abstract class TransformerAbstract extends BaseTransformerAbstract
{
    /**
     * Externals to add to output
     * @var array
     */
    protected $externals = [];

    /**
     * Add to default includes
     * @param string|array $defaults_to_add keys to add to default includes
     * @return  instance.   to allow method chaining
     */
    public function addDefaultInclude($defaults_to_add)
    {
        $defaults_to_add = is_array($defaults_to_add) ? $defaults_to_add : [$defaults_to_add];
        $this->defaultIncludes = array_unique(array_merge($this->defaultIncludes, $defaults_to_add));
        return $this;
    }

    /**
     * Add External Key Value
     * @param string $key   Key
     * @param mix $value Value
     * @throws Exception Invalid key exception
     * @return  instance.   to allow method chaining
     */
    public function addExternal($key, $value)
    {
        if (!$key || is_numeric($key) || !is_string($key)) {
            throw new Exception('Error Adding external, $key must be string', 1);
        }

        $this->externals[$key] = $value;

        return $this;
    }

    /**
     * transform
     * here this method add as final and in your transformer class
     * you should use 'transformWithDefault' method
     * the reason is do this is to append externals to output
     *
     * @param  object $object Object being transformmed
     * @return array
     */
    final public function transform($object)
    {
        $data = [];

        // Get Default Data
        if (method_exists($this, 'transformWithDefault')) {
            $data = $this->transformWithDefault($object);
        }

        // Return Default Data and Externals
        return array_merge($this->externals($object), $data);
    }

    /**
     * Prepare External Data
     * @param  Object Being Transformed $object
     * @return array    Externals Array
     */
    protected function externals($object)
    {
        $externals = [];

        foreach ($this->externals as $key => $value) {
            $externals[$key] = is_callable($value) ? call_user_func($value, $object) : $value;
        }

        return $externals;
    }
}