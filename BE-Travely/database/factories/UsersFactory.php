<?php

namespace Database\Factories;

use App\Models\Users;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Users>
 */
class UsersFactory extends Factory
{
    protected $model = Users::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'userID' => (string) Str::uuid(),
            'userName' => fake()->userName(),
            'passWord' => bcrypt('password'), // password (bcrypt hashed)
            'phoneNumber' => fake()->numerify('##########'), // 10 digits
            'address' => fake()->address(),
            'email' => fake()->unique()->safeEmail(),
            'role_id' => 2, // Default user role
            'created_by' => 'seeder',
            'updated_by' => 'seeder',
            'refresh_token' => null,
            'email_verified' => true,
            'verification_token' => null,
            'verification_token_expires_at' => null,
            'google_id' => null,
            'avatar_url' => fake()->imageUrl(200, 200, 'people'),
            'is_admin' => false,
        ];
    }

    /**
     * Indicate that the model's email should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(fn(array $attributes) => [
            'email_verified' => false,
            'verification_token' => Str::random(64),
            'verification_token_expires_at' => now()->addHours(24),
        ]);
    }

    /**
     * Indicate that the user is an admin.
     *
     * @return static
     */
    public function admin()
    {
        return $this->state(fn(array $attributes) => [
            'is_admin' => true,
            'role_id' => 1, // Admin role
        ]);
    }
}
