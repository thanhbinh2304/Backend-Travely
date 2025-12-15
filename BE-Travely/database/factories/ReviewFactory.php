<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\Tour;
use App\Models\Users;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $tour = Tour::inRandomOrder()->first() ?? Tour::factory()->create();
        $user = Users::inRandomOrder()->first() ?? Users::factory()->create();

        $statuses = ['pending', 'approved', 'hidden'];

        return [
            'tourID' => $tour->tourID,
            'userID' => $user->userID,
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->paragraphs(fake()->numberBetween(1, 3), true),
            'images' => fake()->optional(0.4)->passthrough([
                fake()->imageUrl(400, 300, 'nature'),
                fake()->imageUrl(400, 300, 'city')
            ]),
            'status' => fake()->randomElement($statuses),
            'is_verified_purchase' => fake()->boolean(70), // 70% verified purchases
            'timestamp' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
