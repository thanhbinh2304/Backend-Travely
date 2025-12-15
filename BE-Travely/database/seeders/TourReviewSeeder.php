<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;

class TourReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create 300 reviews (will pick random tours and users via factory)
        Review::factory(300)->create();
    }
}
