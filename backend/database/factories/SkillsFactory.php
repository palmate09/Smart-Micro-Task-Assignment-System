<?php

namespace Database\Factories;

use App\Models\Skills; 
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Skills>
 */
class SkillsFactory extends Factory
{

    protected $model = Skills::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(), 
            'name' => $this->faker->unique()->jobTitle,
        ];
    }
}
