<?php

/**
 * This Singletone Class is to parse request includes and excludes
 *
 * @author Ahmed saad <a7mad.sa3d.2014@gmail.com>
 * @license MIT
 */

namespace Saad\Fractal;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FractalRequestParser {

	/**
	 * Instance Singletone
	 * @var FractalRequestParser
	 */
	protected static $instance = null;

	/**
	 * Request
	 * @var Illuminate\Http\Request
	 */
	protected $request;

	/**
	 * Includes Array
	 * @var Illuminate\Support\Collections\Collection
	 */
	protected $includes;

	/**
	 * Excludes Array
	 * @var Illuminate\Support\Collections\Collection
	 */
	protected $excludes;


	/**
	 * Construct Singletone Instance
	 * @param Request $request Illuminate\Http\Request
	 */
	protected function __construct()
	{
		$this->request = app()->make(Request::class);
		$this->parseRequest();
	}

	/**
	 * Check if key exists in includes
	 * @param  string  $key Key Path
	 * @return boolean
	 */
	public function includesHas($key)
	{
		return array_has($this->includes, $key);
	}

	/**
	 * Check if key exists in excludes
	 * @param  string  $key Key Path
	 * @return boolean
	 */
	public function excludesHas($key)
	{
		return array_has($this->excludes, $key);
	}

	/**
	 * Get Or Create Parser Singletone
	 * @return FractalRequestParser Parser
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Parse Request
	 * @return void
	 */
	protected function parseRequest()
	{
		$include = $this->request->input('include', '');
		$exclude = $this->request->input('exclude', '');

		$this->parseIncludes($include);
		$this->parseExcludes($exclude);
	}

	/**
	 * Parse Includes
	 * @param  string $includes include string
	 * @return void
	 */
	protected function parseIncludes($includes)
	{
		$this->includes = $this->parseString($includes);
	}

	/**
	 * Parse Excludes
	 * @param  string $excludes include string
	 * @return void
	 */
	protected function parseExcludes($excludes)
	{
		$this->excludes = $this->parseString($excludes);
	}

	/**
	 * Parse String
	 * @param  string $string string to parse
	 * @return void
	 */
	protected function parseString($string)
	{
		$final = [];
        
		$mainKeys = collect(explode(',', trim($string, ',')));
		$mainKeys->transform( function($item) {
			return $this->transformItem($item);
		})->each(function ($item) use (&$final){
			$final = array_merge_recursive($final, $item);
		});

		return collect($final);
	}

	/**
	 * Transform Item to its Nested Tree
	 * 
	 * @param  string $item string to parse into tree
	 * @return array       String Tree
	 */
	protected function transformItem($item)
	{
		$item = trim(trim($item, ':'), '.');
		$first_dot_pos = strpos($item, '.');
		if ($first_dot_pos === false) {
			$first_colon_pos = strpos($item, ':');
			if ($first_colon_pos === false) {
				return [$item => true];
			} else {
				return [substr($item, 0, $first_colon_pos) => true];
			}
		} else {
			// Has Nested Includes
			return [
				substr($item, 0, $first_dot_pos) => $this->transformItem(substr($item, $first_dot_pos+1))
			];
		}
	}
}