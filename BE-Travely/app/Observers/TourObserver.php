<?php

namespace App\Observers;

use App\Models\Tour;
use App\Services\TourCacheService;

class TourObserver
{
    protected $cacheService;

    public function __construct(TourCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle the Tour "created" event.
     *
     * @param  \App\Models\Tour  $tour
     * @return void
     */
    public function created(Tour $tour)
    {
        // Clear all tours cache when new tour is created
        $this->cacheService->clearAll();
    }

    /**
     * Handle the Tour "updated" event.
     *
     * @param  \App\Models\Tour  $tour
     * @return void
     */
    public function updated(Tour $tour)
    {
        // Clear specific tour cache and related caches
        $this->cacheService->clearTour($tour->tourID);

        // If availability changed, clear featured and available cache
        if ($tour->isDirty('availability')) {
            $this->cacheService->clearFeatured();
            $this->cacheService->clearAvailable();
        }

        // Clear search cache as tour details changed
        $this->cacheService->clearSearch();
    }

    /**
     * Handle the Tour "deleted" event.
     *
     * @param  \App\Models\Tour  $tour
     * @return void
     */
    public function deleted(Tour $tour)
    {
        // Clear all caches when tour is deleted
        $this->cacheService->clearAll();
    }

    /**
     * Handle the Tour "restored" event.
     *
     * @param  \App\Models\Tour  $tour
     * @return void
     */
    public function restored(Tour $tour)
    {
        // Clear all caches when tour is restored
        $this->cacheService->clearAll();
    }

    /**
     * Handle the Tour "force deleted" event.
     *
     * @param  \App\Models\Tour  $tour
     * @return void
     */
    public function forceDeleted(Tour $tour)
    {
        //
    }
}
