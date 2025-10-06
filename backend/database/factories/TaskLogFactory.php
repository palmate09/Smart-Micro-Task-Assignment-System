<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TaskLog;
use App\Models\Task;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TaskLog>
 */
class TaskLogFactory extends Factory
{
    protected $model = TaskLog::class;

    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'worker_id' => User::factory()->worker(),
            'status' => $this->faker->randomElement(['assigned','in-progress','completed']),
            'start_time' => now(),
            'end_time' => null,
            'comments' => $this->faker->sentence(8),
        ];
    }
}


