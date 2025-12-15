<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Users;
use App\Models\Tour;
use App\Models\Booking;
use App\Models\Checkout;
use App\Models\Review;
use App\Models\TourItinerary;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Create admin user if not exists
        if (!Users::where('email', 'admin@travely.com')->exists()) {
            Users::factory()->create([
                'userName' => 'admin',
                'email' => 'admin@travely.com',
                'is_admin' => true,
                'role_id' => 1,
            ]);
        }

        // Create regular users
        Users::factory(20)->create();

        // Create tours
        $tours = Tour::factory(50)->create();

        // For each tour, create 1-5 itinerary days
        foreach ($tours as $tour) {
            $days = rand(1, 5);
            for ($d = 1; $d <= $days; $d++) {
                TourItinerary::create([
                    'tourID' => $tour->tourID,
                    'dayNumber' => $d,
                    'destination' => 'Điểm ' . $d . ' - ' . fake()->city(),
                    'activity' => fake()->sentence(8),
                ]);
            }
        }

        // Create bookings
        Booking::factory(200)->create();

        // Create checkouts (payments)
        Checkout::factory(150)->create();

        // Create reviews
        Review::factory(100)->create();

        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
