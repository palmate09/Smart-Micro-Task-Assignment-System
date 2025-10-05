<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\UserSkill;
use Illuminate\Support\Str;
use App\Models\User; 
use App\Models\Skills; 

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserSkill>
 */
class UserSkillFactory extends Factory
{
    protected $model = UserSkill::class; 
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), 
            'skill_id' => Skills::factory(), 
            'proficiency' => $this->faker->numberBetween(1, 10)
        ];
    }
}
