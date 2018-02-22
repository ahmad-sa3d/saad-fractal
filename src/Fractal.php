<?php

/**
 * This Fractal Class Will automatically Parse Request includes and excludes
 * And includes, excludes Parameters which will have the heighest Periority
 *
 * @author Ahmed saad <a7mad.sa3d.2014@gmail.com>
 * @license MIT
 */

namespace Saad\Fractal;

use Illuminate\Support\Facades\Request;
use League\Fractal\Manager;
use Spatie\Fractal\Fractal as SpatieFractal;
use Spatie\Fractalistic\ArraySerializer;

class Fractal extends SpatieFractal
{
    /** @param \League\Fractal\Manager $manager */
    public function __construct(Manager $manager, $include_query_string = null, $exclude_query_string = null)
    {
        $this->manager = $manager;

        // Parse Includes
        $include_query_string = $include_query_string ?: Request::get('include');
        if ($include_query_string) {
            $this->parseIncludes($include_query_string);
        }

        // Parse Excludes
        $exclude_query_string = $exclude_query_string ?: Request::get('exclude');
        if ($exclude_query_string) {
            $this->manager->parseExcludes( $exclude_query_string );
        }
        
    }

    /**
     * @param null|mixed $data
     * @param null|callable|\League\Fractal\TransformerAbstract $transformer
     * @param null|\League\Fractal\Serializer\SerializerAbstract $serializer
     *
     * @return \Spatie\Fractalistic\Fractal
     */
    public static function create($data = null, $transformer = null, $serializer = null, $include_query_string = null, $exclude_query_string = null)
    {
        $instance = new static(new Manager(), $include_query_string, $exclude_query_string);

        $instance->data = $data ?: null;
        $instance->dataType = $instance->determineDataType($data);
        $instance->transformer = $transformer ?: null;
        $instance->serializer = $serializer ?: new ArraySerializer();

        return $instance;
    }

}
