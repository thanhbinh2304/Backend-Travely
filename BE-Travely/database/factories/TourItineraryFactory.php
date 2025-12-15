<?php

namespace Database\Factories;

use App\Models\TourItinerary;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TourItinerary>
 */
class TourItineraryFactory extends Factory
{
    protected $model = TourItinerary::class;

    public function definition()
    {
        return [
            'dayNumber' => $this->faker->numberBetween(1, 5),
            'destination' => $this->faker->city(),
            'activity' => $this->faker->sentence(8),
        ];
    }
}
