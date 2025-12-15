<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tour;
use App\Models\TourItinerary;

class TourItinerarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();

        $tours = Tour::all();

        foreach ($tours as $tour) {
            $days = rand(1, 5);
            for ($d = 1; $d <= $days; $d++) {
                TourItinerary::create([
                    'tourID' => $tour->tourID,
                    'dayNumber' => $d,
                    'destination' => 'Điểm ' . $d . ' - ' . $faker->city(),
                    'activity' => $faker->sentence(8),
                ]);
            }
        }
    }
}
