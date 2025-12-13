<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Tour;

class TourCacheService
{
    const CACHE_PREFIX = 'tour';
    const CACHE_TTL = 3600; // 1 hour

    /**
     * Cache keys
     */
    const KEY_ALL = 'tours:all';
    const KEY_FEATURED = 'tours:featured';
    const KEY_AVAILABLE = 'tours:available';
    const KEY_DETAIL = 'tour:detail:';
    const KEY_SEARCH = 'tours:search:';

    /**
     * Get all tours with cache
     */
    public function getAll($perPage = 15)
    {
        $cacheKey = self::KEY_ALL . ":{$perPage}";

        return Cache::tags(['tours'])->remember($cacheKey, self::CACHE_TTL, function () use ($perPage) {
            return Tour::with(['images', 'itineraries'])
                ->paginate($perPage);
        });
    }

    /**
     * Get featured tours with cache
     * Featured = available tours ordered by newest
     */
    public function getFeatured($limit = 8)
    {
        $cacheKey = self::KEY_FEATURED . ":{$limit}";

        return Cache::tags(['tours', 'featured'])->remember($cacheKey, self::CACHE_TTL, function () use ($limit) {
            return Tour::with(['images'])
                ->where('availability', 1)
                ->orderBy('tourID', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get available tours with cache
     */
    public function getAvailable($filters = [], $perPage = 15)
    {
        $cacheKey = self::KEY_AVAILABLE . ':' . md5(json_encode($filters)) . ":{$perPage}";

        return Cache::tags(['tours', 'available'])->remember($cacheKey, self::CACHE_TTL, function () use ($filters, $perPage) {
            $query = Tour::with(['images', 'itineraries'])
                ->where('availability', 1);

            if (!empty($filters['destination'])) {
                $query->where('destination', 'like', "%{$filters['destination']}%");
            }

            if (!empty($filters['min_price'])) {
                $query->where('priceAdult', '>=', $filters['min_price']);
            }

            if (!empty($filters['max_price'])) {
                $query->where('priceAdult', '<=', $filters['max_price']);
            }

            if (!empty($filters['start_date'])) {
                $query->where('startDate', '>=', $filters['start_date']);
            }

            if (!empty($filters['end_date'])) {
                $query->where('endDate', '<=', $filters['end_date']);
            }

            return $query->paginate($perPage);
        });
    }

    /**
     * Get tour detail by ID with cache
     */
    public function getById($id)
    {
        $cacheKey = self::KEY_DETAIL . $id;

        return Cache::tags(['tours', 'tour:' . $id])->remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            return Tour::with(['images', 'itineraries', 'reviews.user'])
                ->findOrFail($id);
        });
    }

    /**
     * Search tours with cache
     */
    public function search($keyword, $perPage = 15)
    {
        $cacheKey = self::KEY_SEARCH . md5($keyword) . ":{$perPage}";

        return Cache::tags(['tours', 'search'])->remember($cacheKey, self::CACHE_TTL, function () use ($keyword, $perPage) {
            return Tour::with(['images', 'itineraries'])
                ->where(function ($query) use ($keyword) {
                    $query->where('title', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%")
                        ->orWhere('destination', 'like', "%{$keyword}%");
                })
                ->paginate($perPage);
        });
    }

    /**
     * Clear all tour caches
     */
    public function clearAll()
    {
        Cache::tags(['tours'])->flush();
    }

    /**
     * Clear cache for specific tour
     */
    public function clearTour($id)
    {
        Cache::tags(['tour:' . $id])->flush();
    }

    /**
     * Clear featured tours cache
     */
    public function clearFeatured()
    {
        Cache::tags(['featured'])->flush();
    }

    /**
     * Clear available tours cache
     */
    public function clearAvailable()
    {
        Cache::tags(['available'])->flush();
    }

    /**
     * Clear search cache
     */
    public function clearSearch()
    {
        Cache::tags(['search'])->flush();
    }

    /**
     * Warm up cache - Pre-load commonly accessed data
     */
    public function warmUp()
    {
        $this->getFeatured(8);
        $this->getAvailable([], 15);
    }
}
