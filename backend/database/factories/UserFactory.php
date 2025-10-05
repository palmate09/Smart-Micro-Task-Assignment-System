<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Default state (minimal fields common to all roles)
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(), // UUID primary key
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password123'), // default password
            'role' => 'worker', // default role
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Worker must fill every field
     */
    public function worker(): Factory
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'worker',
            'rating' => $this->faker->randomFloat(2, 0, 5),
            'availability_status' => $this->faker->randomElement(['offline', 'busy', 'available']),
        ]);
    }

    /**
     * Admin only has base fields
     */
    public function admin(): Factory
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'admin',
            'rating' => null,
            'availability_status' => null,
        ]);
    }

    /**
     * Company only has base fields
     */
    public function company(): Factory
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'company',
            'rating' => null,
            'availability_status' => null,
        ]);
    }
}
