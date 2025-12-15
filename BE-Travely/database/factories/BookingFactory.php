<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Users;
use App\Models\Tour;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $user = Users::inRandomOrder()->first() ?? Users::factory()->create();
        $tour = Tour::inRandomOrder()->first() ?? Tour::factory()->create();

        $numAdults = fake()->numberBetween(1, 5);
        $numChildren = fake()->numberBetween(0, 3);
        $totalPrice = ($numAdults * $tour->priceAdult) + ($numChildren * $tour->priceChild);

        $statuses = ['confirmed', 'cancelled', 'completed'];
        $paymentStatuses = ['pending', 'paid', 'refunded'];

        return [
            'tourID' => $tour->tourID,
            'userID' => $user->userID,
            'bookingDate' => fake()->dateTimeBetween('-6 months', 'now'),
            'numAdults' => $numAdults,
            'numChildren' => $numChildren,
            'totalPrice' => $totalPrice,
            'paymentStatus' => fake()->randomElement($paymentStatuses),
            'bookingStatus' => fake()->randomElement($statuses),
            'specialRequests' => fake()->optional(0.3)->sentence() ?? '', // Always provide a value
        ];
    }
}
