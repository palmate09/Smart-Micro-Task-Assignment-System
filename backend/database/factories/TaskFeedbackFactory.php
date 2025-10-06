<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TaskFeedback;
use App\Models\Task;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TaskFeedback>
 */
class TaskFeedbackFactory extends Factory
{
    protected $model = TaskFeedback::class;

    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'worker_id' => User::factory()->worker(),
            'rating' => $this->faker->randomFloat(2, 1, 5),
            'review' => $this->faker->sentence(12),
        ];
    }
}


