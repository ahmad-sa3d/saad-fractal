<?php
namespace Saad\Fractal\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;

abstract class BaseMakeCommand extends Command
{
    /**
     * model name
     * @var string
     */
    protected $model;

    /**
     * full model class name
     * @var string
     */
    protected $full_model;

    /**
     * App Name
     * @var string
     */
    protected $app_name;

    /**
     * sub directories under main directory
     * @var string
     */
    protected $nested_level_path;

    /**
     * sub namespace after main directory namespace
     * @var string
     */
    protected $nested_level_namespace;

    /**
     * filesystem instance
     * @var Filesystem+
     */
    protected $filesystem;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $filesystem, Application $app)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
        $this->app_name = $app->getNamespace();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
    	$this->full_model = preg_replace("/^([\/\\\])|([\/\\\])$/", '', $this->argument('model'));
    	$this->model = class_basename($this->full_model);

        $nest = preg_replace("/^([\/\\\])|([\/\\\])$/", '', $this->option('nest'));
        $this->nested_level_namespace = '\\' . str_replace('/', '\\', $nest);
        $this->nested_level_path = '/' . str_replace('\\', '/', $nest);

    	if (! is_a($this->full_model, Model::class, true)) {
    		$this->error("the given {$this->full_model} argument should be valid Model Name");
    		return;
    	}
    }

    /**
     * Stubs Path
     * 
     * @return strng Stubs Path
     */
    abstract protected function getStubsPath();

    /**
     * Output Directory name
     * 
     * @return string output directory name
     */
    abstract protected function getOutputDirectoryName();

    /**
     * Create File
     * 
     * @param  string $stub_name     stub name to create file from
     * @param  string $sub_directory sub directory of stub file location, and output file location
     * @throws \Exception
     */
    protected function create($stub_name, $sub_directory = '')
    {
    	$stub_path = "{$this->getStubsPath()}/$sub_directory/{$stub_name}.stub";

    	if (! $this->filesystem->exists($stub_path)) {
    		throw new \Exception("Stub {$stub_path} not found!");
    	}

    	$stub = $this->filesystem->get($stub_path);

        $output_basename = $this->getStubOutputFileBaseName();
    	$content = $this->processStubContent($stub, $sub_directory, $output_basename);

    	$this->createFile("{$output_basename}.php", $sub_directory, $content);
    }

    /**
     * Get Output File Base name based on stub name
     *
     * @param string $stub_name Stub file base name
     */
    protected function getStubOutputFileBaseName($stub_name) {
        return "{$this->model}{$stub_name}";
    }

    /**
     * Delete Created File for rolling back
     * 
     * @param  string $file_name     File stub name [Without model name prefix]
     * @param  string $sub_directory sub directory where fil;e saved if there is
     */
    protected function delete($file_name, $sub_directory = '')
    {
    	$file_path = app_path("{$this->getOutputDirectoryName()}{$this->nested_level_path}{$sub_directory}/{$this->model}{$file_name}.php");

    	if ($this->filesystem->exists($file_path)) {
    		if ($this->confirm("Delete {$file_path} ?", true)) {
    			return $this->filesystem->delete($file_path);
    		}
    	}
    }

    /**
     * Replace stub variables by real ones
     * 
     * @param  string $content stub file content
     * @return string          processed stub content [output file content]
     */
    protected function processStubContent($content, $sub_directory = null, $output_basename = null)
    {
    	return preg_replace([
    		'/\$NAMESPACE\$/',
    		'/\$FULL_MODEL\$/',
            '/\$MODEL\$/',
            '/\$LOWER_MODEL\$/',
    		'/\$DIRECTORY\$/',
            '/\$FILE\$/',
    	], [
    		$this->getFullNamespace($sub_directory),
    		$this->full_model,
            $this->model,
    		snake_case($this->model),
            $this->getOutputDirectoryName(),
            $output_basename,
    	], $content);
    }

    /**
     * Create a file
     * 
     * @param  string $file_name File name
     * @param  string $directory Directory path
     * @param  string $content   file content
     */
    protected function createFile($file_name, $sub_directory, $content)
    {
    	// Write File
    	$directory = app_path("{$this->getOutputDirectoryName()}{$this->nested_level_path}{$sub_directory}");

        if (! $this->filesystem->exists($directory)) {
    		$this->info('Creating Directory');
    		$this->filesystem->makeDirectory($directory, 493, true, true);
    	}

    	$file_path = "{$directory}/{$file_name}";
    	if ($this->filesystem->exists($file_path)) {
    		if (! $this->confirm("File {$file_name} already exists, overwrite?", false)) {
    			$this->error('skipped');
	    		return false;
    		}
    	} 

    	if ($result = $this->filesystem->put($file_path, $content)) {
    		$this->info($file_name . " Created");
    	}

    	return $result;
    }

    /**
     * Get Created file full namespace
     * 
     * @param  string $sub_directory Sub Directory
     * @return string                Full namespace
     */
    protected function getFullNamespace($sub_directory)
    {
        $namespace = $this->app_name . str_replace('/', '\\', $this->getOutputDirectoryName()) . $this->nested_level_namespace . str_replace('/', '\\', $sub_directory);

        return $namespace;
    }
}