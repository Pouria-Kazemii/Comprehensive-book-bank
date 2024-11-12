<?php

namespace App\Console\Commands\ConvertIntoMongodb;

use App\Models\MongoDBModels\BookIrBook2;
use Illuminate\Console\Command;

class DeleteXmongoParentInBookIrBookCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:xmongo_parent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'delete the xmongo_parent field in bookir_book collection';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $result = BookIrBook2::raw(function($collection) {
            return $collection->updateMany(
                ['xmongo_parent' => ['$exists' => true]], // Find documents with xmongo_parent field
                ['$unset' => ['xmongo_parent' => 1]] // Remove xmongo_parent field
            );
        });

        $this->info("Deleted xmongo_parent field from {$result->getModifiedCount()} documents.");
        return 0;
    }
}
