<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\Skills; 
use App\Models\UserSkill;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AutoAssignTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_auto_assigns_tasks_to_best_workers(): void
    {
        $admin = User::factory()->admin()->create();
        $company = User::factory()->company()->create(); 

        $worker1 = User::factory()->worker()->create([
            'rating' => 4.8,
            'availability_status' => 'available',
        ]);
        $worker2 = User::factory()->worker()->create([
            'rating' => 4.5,
            'availability_status' => 'available',
        ]);

        $phpSkill = Skills::create(['name' => 'PHP']);
        $laravelSkill = Skills::create(['name' => 'Laravel']);
        $reactSkill = Skills::create(['name' => 'React']);

        // worker skills
        UserSkill::create(['user_id' => $worker1->id, 'skill_id' => $phpSkill->id]);
        UserSkill::create(['user_id' => $worker1->id, 'skill_id' => $laravelSkill->id]);
        UserSkill::create(['user_id' => $worker2->id, 'skill_id' => $reactSkill->id]);

        // Pending tasks
        $task1 = Task::create([
            'title' => 'Backend API Task',
            'description' => 'Build API',
            'required_skills' => ['PHP', 'Laravel'],
            'status' => 'pending',
            'created_by' => $company->id
        ]);

        $task2 = Task::create([
            'title' => 'Frontend UI Task',
            'description' => 'Build UI',
            'required_skills' => ['React'],
            'status' => 'pending',
            'created_by' => $company->id
        ]);

        $response = $this->actingAs($admin, 'sanctum')
                         ->postJson('/api/tasks/auto-assign', [
                             'task_ids' => [$task1->id, $task2->id],
                         ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'assigned' => [
                ['task_id', 'worker_id', 'match_score']
            ]
        ]);

        $data = $response->json('assigned');

        $this->assertEquals($task1->id, $data[0]['task_id']);
        $this->assertEquals($worker1->id, $data[0]['worker_id']);

        $this->assertEquals($task2->id, $data[1]['task_id']);
        $this->assertEquals($worker2->id, $data[1]['worker_id']);
    }
}
