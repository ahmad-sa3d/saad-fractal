<?php
namespace Saad\Fractal\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Saad\Fractal\Commands\BaseMakeCommand;

class MakeTransformer extends BaseMakeCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:transformer {model} {--nest=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make Model Transformer';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
    	// Run Parent Handle
    	parent::handle();

    	try {
	    	// Create Transformer
	    	$this->create("Transformer");

	    } catch (\Exception $exception) {
	    	$this->error($exception->getMessage());
	    	$this->info('Rolling Back');

	    	$this->delete("Transformer");
	    }
    }

    /**
     * stubs path
     * @return string stubs dorectory full path
     */
    protected function getStubsPath() {
    	return __DIR__ . "/../../resources/stubs";
    }

    /**
     * get output directory
     * 
     * @return string directory name
     */
    protected function getOutputDirectoryName() {
    	return 'Transformers';
    }
}