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
	 * 
	 * @var FractalRequestParser
	 */
	protected static $instance = null;

	/**
	 * Request
	 * 
	 * @var Illuminate\Http\Request
	 */
	protected $request;

	/**
	 * Includes Array
	 * 
	 * @var Illuminate\Support\Collections\Collection
	 */
	protected $includes;

	/**
	 * Excludes Array
	 * 
	 * @var Illuminate\Support\Collections\Collection
	 */
	protected $excludes;

	/**
	 * Options Array
	 * 
	 * @var Illuminate\Support\Collections\Collection
	 */
	protected $options;


	/**
	 * Construct Singletone Instance
	 * 
	 * @param Request $request Illuminate\Http\Request
	 */
	protected function __construct()
	{
		$this->request = app()->make(Request::class);
		$this->parseRequest();
	}

	/**
	 * Check if key exists in includes
	 * 
	 * @param  string  $key Key Path
	 * @return boolean
	 */
	public function includesHas($key)
	{
		return array_has($this->includes, $key);
	}

	/**
	 * Check if key exists in excludes
	 * 
	 * @param  string  $key Key Path
	 * @return boolean
	 */
	public function excludesHas($key)
	{
		return array_has($this->excludes, $key);
	}

	/**
	 * Check if given key has any options
	 * 
	 * @param  string  $key Key Path
	 * @return boolean
	 */
	public function optionsHas($key)
	{
		return array_key_exists($key, $this->options);
	}

	/**
	 * Check if given key has any options
	 * 
	 * @param  string  $key Key Path
	 * @return boolean
	 */
	public function optionsHasOption($key, $option)
	{
		return $this->optionsHas($key) && array_key_exists($option, $this->options[$key]);
	}

	/**
	 * get key options
	 * 
	 * @param  string  $key Key Path
	 * @param  mix    $default default value
	 * @return boolean
	 */
	public function getOptions($key, $default = null)
	{
		return $this->optionsHas($key) ? $this->options[$key] : $default;
	}

	/**
	 * get key specific option
	 * 
	 * @param  string  $key Key Path
	 * @param  string    $option option name
	 * @param  mix    $default default value
	 * @return boolean
	 */
	public function getOption($key, $option, $default = null)
	{
		return $this->optionsHasOption($key, $option) ? $this->options[$key][$option] : $default;
	}

	/**
	 * Get Or Create Parser Singletone
	 * 
	 * @return FractalRequestParser Parser
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance) || self::mustRefresh()) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Refresh Instance If Needed
	 * 
	 * @return FractalRequestParser Parser
	 */
	public function refreshIfNeeded()
	{
		if (self::mustRefresh()) {
			self::$instance = new self();
			$this->request = self::$instance->getRequest();
			$this->includes = self::$instance->getIncludes();
			$this->excludes = self::$instance->getExcludes();
		}

		return $this;
	}

	/**
	 * Get instance Request Object
	 * 
	 * @return Illuminate\Http\Request Request
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * Get instance includes collection
	 * 
	 * @return Illuminate\Support\Collection Includes collection
	 */
	public function getIncludes() {
		return $this->includes;
	}

	/**
	 * Get instance excludes collection
	 * 
	 * @return Illuminate\Support\Collection Excludes collection
	 */
	public function getExcludes() {
		return $this->excludes;
	}


	/**
	 * Check if instance must be recreated
	 * 
	 * @return boolean True if it instance needs to be recreated
	 */
	protected static function mustRefresh()
	{
		return app()->runningUnitTests();
	}

	/**
	 * Parse Request
	 * @return void
	 */
	protected function parseRequest()
	{
		$include = $this->request->input('include', '');
		$exclude = $this->request->input('exclude', '');

		$this->options = [];

		$this->parseIncludes($include);
		$this->parseExcludes($exclude);
	}

	/**
	 * Parse Includes
	 * 
	 * @param  string $includes include string
	 * @return void
	 */
	protected function parseIncludes($includes)
	{
		$this->includes = $this->parseString($includes);
	}

	/**
	 * Parse Excludes
	 * 
	 * @param  string $excludes include string
	 * @return void
	 */
	protected function parseExcludes($excludes)
	{
		$this->excludes = $this->parseString($excludes);
	}

	/**
	 * Parse String
	 * 
	 * @param  string $string string to parse
	 * @return void
	 */
	protected function parseString($string)
	{
		$final = [];

		$final_options = [];
        
		$mainKeys = collect(explode(',', trim($string, ',')));
		$mainKeys->transform( function($item) {
			// dump($item);
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
	protected function transformItem($item, $parent = null)
	{
		// $item = trim(trim($item, ':'), '.');
		$item = trim($item, '.');
		$first_dot_pos = strpos($item, '.');
		if ($first_dot_pos === false) {
			$first_colon_pos = strpos($item, ':');
			if ($first_colon_pos === false) {
				return [$item => true];
			} else {
				// Has Option
				$key = substr($item, 0, $first_colon_pos);
				$parent = $parent ? "{$parent}.{$key}" : $key;
				$this->parseOptions($item, substr($item, $first_colon_pos+1), $parent);
				return [$key => true];
			}
		} else {
			// Has Nested Includes
			$key = substr($item, 0, $first_dot_pos);
			$parent = $parent ? "{$parent}.{$key}" : $key;
			return [
				$key => $this->transformItem(substr($item, $first_dot_pos+1), $parent),
			];
		}
	}

	/**
	 * Parse Item Options
	 * 
	 * @param  string $item string to parse into tree
	 * @return array       String Tree
	 */
	protected function parseOptions($item, $options, $parent) {
		$options = explode(':', trim($options, ':'));

		foreach ($options as $option) {
			$option = explode('[', trim($option, ']'));
			$args = count($option) > 1 ? explode('|', $option[1]) : [];

			$this->options[$parent][$option[0]][] = $args;
		}

		// dump($item, $options, $parent);
		// dump($this->options);
	}
}