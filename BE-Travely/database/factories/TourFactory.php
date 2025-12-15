<?php

namespace Database\Factories;

use App\Models\Tour;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tour>
 */
class TourFactory extends Factory
{
    protected $model = Tour::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $startDate = fake()->dateTimeBetween('now', '+6 months');
        $endDate = fake()->dateTimeBetween($startDate, $startDate->format('Y-m-d H:i:s') . ' +30 days');

        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraphs(3, true),
            'quantity' => fake()->numberBetween(10, 100),
            'priceAdult' => fake()->numberBetween(1000000, 10000000), // 1M to 10M VND
            'priceChild' => fake()->numberBetween(500000, 5000000), // 500K to 5M VND
            'destination' => fake()->city(),
            'availability' => fake()->boolean(80), // 80% chance available
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }
}
