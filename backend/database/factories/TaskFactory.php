<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Skills;
use App\Models\Task;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        // Create assigned worker and creator users
        $assignedWorker = User::factory()->worker()->create();
        $creator = User::factory()->company()->create();

        return [
            'id' => (string) Str::uuid(),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'required_skills' => Skills::inRandomOrder()->take(rand(1, 3))->pluck('id')->toArray(), // array of skill UUIDs
            'estimated_duration' => $this->faker->numberBetween(1, 100),
            'deadline' => $this->faker->dateTimeBetween('now', '+1 year'),
            'status' => $this->faker->randomElement(['pending', 'assigned', 'in-progress', 'completed', 'cancelled']),
            'created_by' => $creator->id,                // UUID string
        ];
    }
}
