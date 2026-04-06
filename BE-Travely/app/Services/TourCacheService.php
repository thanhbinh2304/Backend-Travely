<?php

namespace App\Services;

use App\Models\Tour;
use App\Support\TaggedCache;

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
    const KEY_DETAIL = 'tour:v2:detail:';
    const KEY_SEARCH = 'tours:search:';

    /**
     * Get all tours with cache
     */
    public function getAll($perPage = 15)
    {
        $cacheKey = self::KEY_ALL . ":{$perPage}";

        return TaggedCache::remember(['tours'], $cacheKey, self::CACHE_TTL, function () use ($perPage) {
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

        return TaggedCache::remember(['tours', 'featured'], $cacheKey, self::CACHE_TTL, function () use ($limit) {
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

        return TaggedCache::remember(['tours', 'available'], $cacheKey, self::CACHE_TTL, function () use ($filters, $perPage) {
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

        return TaggedCache::remember(
            ['tours', 'tour:' . $id],
            $cacheKey,
            self::CACHE_TTL,
            function () use ($id) {
                return Tour::with(['images', 'itineraries', 'reviews.user'])
                    ->where('tourID', $id)
                    ->first();
            }
        );
    }


    /**
     * Search tours with cache
     */
    public function search($keyword, $perPage = 15)
    {
        $normalizedKeyword = trim(preg_replace('/\s+/', ' ', (string) $keyword));
        $cacheKey = self::KEY_SEARCH . md5($normalizedKeyword) . ":{$perPage}";

        return TaggedCache::remember(['tours', 'search'], $cacheKey, self::CACHE_TTL, function () use ($normalizedKeyword, $perPage) {
            $escapedKeyword = addcslashes($normalizedKeyword, '%_\\');
            $tokens = array_values(array_filter(explode(' ', $normalizedKeyword)));

            return Tour::with(['images', 'itineraries'])
                ->where('availability', 1)
                ->where(function ($query) use ($escapedKeyword, $tokens) {
                    $query->where('title', 'like', "%{$escapedKeyword}%");

                    if (count($tokens) > 1) {
                        $query->orWhere(function ($tokenQuery) use ($tokens) {
                            foreach ($tokens as $token) {
                                $escapedToken = addcslashes($token, '%_\\');
                                $tokenQuery->where('title', 'like', "%{$escapedToken}%");
                            }
                        });
                    }
                })
                ->orderBy('tourID', 'desc')
                ->paginate($perPage);
        });
    }

    /**
     * Clear all tour caches
     */
    public function clearAll()
    {
        TaggedCache::flush(['tours']);
    }

    /**
     * Clear cache for specific tour
     */
    public function clearTour($id)
    {
        TaggedCache::flush(['tour:' . $id]);
    }

    /**
     * Clear featured tours cache
     */
    public function clearFeatured()
    {
        TaggedCache::flush(['featured']);
    }

    /**
     * Clear available tours cache
     */
    public function clearAvailable()
    {
        TaggedCache::flush(['available']);
    }

    /**
     * Clear search cache
     */
    public function clearSearch()
    {
        TaggedCache::flush(['search']);
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
