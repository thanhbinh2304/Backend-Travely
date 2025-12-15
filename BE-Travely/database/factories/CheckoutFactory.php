<?php

namespace Database\Factories;

use App\Models\Checkout;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Checkout>
 */
class CheckoutFactory extends Factory
{
    protected $model = Checkout::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $booking = Booking::inRandomOrder()->first() ?? Booking::factory()->create();

        $paymentMethods = ['credit_card', 'debit_card', 'paypal', 'bank_transfer', 'cash'];
        $statuses = ['Pending', 'Completed', 'Failed', 'Refunded'];

        $amount = $booking->totalPrice;
        $status = fake()->randomElement($statuses);

        return [
            'bookingID' => $booking->bookingID,
            'paymentMethod' => fake()->randomElement($paymentMethods),
            'paymentDate' => fake()->dateTimeBetween($booking->bookingDate, 'now'),
            'amount' => $amount,
            'paymentStatus' => $status,
            'transactionID' => fake()->uuid(),
            'paymentData' => fake()->optional(0.5)->passthrough(['card_last_four' => fake()->numerify('####')]),
            'qrCode' => fake()->optional(0.3)->imageUrl(200, 200, 'business'),
            'createdAt' => fake()->dateTimeBetween($booking->bookingDate, 'now'),
            'updatedAt' => fake()->dateTimeBetween('createdAt', 'now'),
            'refundDate' => $status === 'refunded' ? fake()->dateTimeBetween('paymentDate', 'now') : null,
            'refundAmount' => $status === 'refunded' ? fake()->numberBetween(100000, $amount) : null,
            'refundReason' => $status === 'refunded' ? fake()->sentence() : null,
            'refundBy' => $status === 'refunded' ? 'admin' : null,
        ];
    }
}
