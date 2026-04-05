<?php

namespace App\Services;

use App\Models\Wishlist;
use Illuminate\Support\Facades\Cache;

class WishlistCacheService
{
    private const CACHE_TTL = 900; // 15 minutes

    public function getByUserId($userId)
    {
        return Cache::tags(['wishlist', 'user:' . $userId])->remember(
            $this->wishlistCacheKey($userId),
            self::CACHE_TTL,
            function () use ($userId) {
                $wishlist = Wishlist::with(['tour.images', 'tour.reviews'])
                    ->where('userID', $userId)
                    ->orderBy('created_at', 'desc')
                    ->get();

                return $wishlist->map(function ($item) {
                    $tour = $item->tour;

                    if ($tour) {
                        $tour->avg_rating = $tour->reviews->avg('rating');
                        $tour->review_count = $tour->reviews->count();
                    }

                    return $item;
                });
            }
        );
    }

    public function getTourIds($userId)
    {
        return Cache::tags(['wishlist', 'user:' . $userId])->remember(
            $this->tourIdsCacheKey($userId),
            self::CACHE_TTL,
            function () use ($userId) {
                return Wishlist::where('userID', $userId)
                    ->pluck('tourID')
                    ->toArray();
            }
        );
    }

    public function clearByUserId($userId)
    {
        Cache::tags(['wishlist', 'user:' . $userId])->flush();
    }

    private function wishlistCacheKey($userId)
    {
        return 'wishlist:user:' . $userId;
    }

    private function tourIdsCacheKey($userId)
    {
        return 'wishlist:user:' . $userId . ':tour_ids';
    }
}
