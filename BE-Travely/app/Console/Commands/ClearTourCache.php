<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TourCacheService;

class ClearTourCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-tours 
                            {type? : The type of cache to clear (all, featured, available, search, tour)}
                            {id? : Tour ID (required when type is "tour")}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear tour-related caches';

    protected $cacheService;

    public function __construct(TourCacheService $cacheService)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $type = $this->argument('type') ?? 'all';
        $id = $this->argument('id');

        switch ($type) {
            case 'all':
                $this->cacheService->clearAll();
                $this->info('✅ All tour caches cleared successfully!');
                break;

            case 'featured':
                $this->cacheService->clearFeatured();
                $this->info('✅ Featured tours cache cleared successfully!');
                break;

            case 'available':
                $this->cacheService->clearAvailable();
                $this->info('✅ Available tours cache cleared successfully!');
                break;

            case 'search':
                $this->cacheService->clearSearch();
                $this->info('✅ Search tours cache cleared successfully!');
                break;

            case 'tour':
                if (!$id) {
                    $this->error('❌ Tour ID is required when clearing specific tour cache!');
                    return Command::FAILURE;
                }
                $this->cacheService->clearTour($id);
                $this->info("✅ Cache for tour #{$id} cleared successfully!");
                break;

            default:
                $this->error('❌ Invalid cache type! Use: all, featured, available, search, or tour');
                return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
