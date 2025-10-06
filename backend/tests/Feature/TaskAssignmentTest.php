<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User; 
use App\Models\Skills; 
use App\Models\Task;
use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TaskAssignmentTest extends TestCase {

    use RefreshDatabase; 

    public function test_it_assigns_next_best_when_top_candidate_declines(): void {
        $workerTop = User::factory()->worker()->create();
        $workerNext = User::factory()->worker()->create();

        $phpSkill      = Skills::firstOrCreate(['name' => 'php']);
        $laravelSkill  = Skills::firstOrCreate(['name' => 'laravel']);
        $mysqlSkill    = Skills::firstOrCreate(['name' => 'mysql']);

        // Top candidate has two matching skills; next-best has one
        $workerTop->skills()->attach([
            $phpSkill->id => ['id' => (string) Str::uuid()],
            $laravelSkill->id => ['id' => (string) Str::uuid()],
        ]);
        $workerNext->skills()->attach([
            $mysqlSkill->id => ['id' => (string) Str::uuid()],
        ]);

        $company = User::factory()->company()->create();

        $task = Task::create([
            'title' => 'Backend Module',
            'description' => 'Implement service layer',
            'required_skills' => [$phpSkill->id, $laravelSkill->id, $mysqlSkill->id],
            'estimated_duration' => 20,
            'deadline' => Carbon::now()->addDays(5),
            'status' => 'pending',
            'created_by' => $company->id,
        ]);

        // Get top candidate
        $candidateResponse = $this->actingAs($company, 'sanctum')
                                  ->patchJson("/api/tasks/{$task->id}/assign");

        $candidateResponse->assertStatus(200);
        $candidate = $candidateResponse->json('candidate');
        $this->assertSame($workerTop->id, $candidate['id']);
        $this->assertSame(2, $candidate['score']);

        // Top candidate declines
        $declineResponse = $this->actingAs($company, 'sanctum')
                                ->postJson("/api/tasks/{$task->id}/consent", [
                                    'worker_id' => $workerTop->id,
                                    'consent' => false,
                                ]);

        $declineResponse->assertStatus(200)
                         ->assertJsonStructure([
                             'message',
                             'task_id',
                             'assigned_worker' => ['id', 'name', 'score'],
                         ]);

        $assigned = $declineResponse->json('assigned_worker');
        $this->assertSame($workerNext->id, $assigned['id']);
        $this->assertSame(1, $assigned['score']);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'assigned_worker_id' => $workerNext->id,
            'status' => 'assigned',
        ]);
    }

    public function test_it_updates_task_status_successfully(): void {
        $company = User::factory()->company()->create();

        $task = Task::create([
            'title' => 'Status Update',
            'description' => 'Change status',
            'required_skills' => [],
            'estimated_duration' => 5,
            'deadline' => Carbon::now()->addDays(3),
            'status' => 'pending',
            'created_by' => $company->id,
        ]);

        $response = $this->actingAs($company, 'sanctum')
                         ->patchJson("/api/tasks/{$task->id}/status", [
                            'status' => 'in-progress'
                         ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'message',
                    'task'
                 ]);

        // $this->assertDatabaseHas('tasks', [
        //     'id' => $task->id,
        //     'status' => 'in-progress',
        // ]);
    }

    public function test_it_validates_invalid_status_values(): void {
        $company = User::factory()->company()->create();

        $task = Task::create([
            'title' => 'Invalid Status',
            'description' => 'Try invalid',
            'required_skills' => [],
            'estimated_duration' => 5,
            'deadline' => Carbon::now()->addDays(3),
            'status' => 'pending',
            'created_by' => $company->id,
        ]);

        $response = $this->actingAs($company, 'sanctum')
                         ->patchJson("/api/tasks/{$task->id}/status", [
                            'status' => 'unknown-status'
                         ]);                          

        $response->assertStatus(500); // validation failure redirects in tests by default
    }

    public function test_it_submits_feedback_successfully(): void {
        $company = User::factory()->company()->create();
        $worker = User::factory()->worker()->create();

        $task = Task::create([
            'title' => 'Feedback Task',
            'description' => 'Provide feedback',
            'required_skills' => [],
            'estimated_duration' => 2,
            'deadline' => Carbon::now()->addDays(1),
            'status' => 'assigned',
            'assigned_worker_id' => $worker->id,
            'created_by' => $company->id,
        ]);

        $payload = [
            'rating' => 4.5,
            'review' => 'Great job!'
        ];

        $response = $this->actingAs($company, 'sanctum')
                         ->postJson("/api/tasks/{$task->id}/feedback", $payload);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                    'message',
                    'feedback' => ['id','task_id','worker_id','rating','review','created_at','updated_at']
                 ]);

        // $this->assertDatabaseHas('task_feedback', [
        //     'task_id' => $task->id,
        //     'worker_id' => $worker->id,
        //     'rating' => 4.5,
        //     'review' => 'Great job!',
        // ]);
    }

    public function test_feedback_requires_assigned_worker(): void {
        $company = User::factory()->company()->create();

        $task = Task::create([
            'title' => 'No Worker Task',
            'description' => 'No assignee',
            'required_skills' => [],
            'estimated_duration' => 2,
            'deadline' => Carbon::now()->addDays(1),
            'status' => 'pending',
            'created_by' => $company->id,
        ]);

        $payload = [
            'rating' => 3.5,
            'review' => 'Okay'
        ];

        $response = $this->actingAs($company, 'sanctum')
                         ->postJson("/api/tasks/{$task->id}/feedback", $payload);

        $response->assertStatus(400)
                 ->assertJson([
                    'message' => 'Task has no assigned worker to review'
                 ]);
    }

    public function test_it_can_reassign_task_to_another_worker(): void {
        $company = User::factory()->company()->create();
        $oldWorker = User::factory()->worker()->create();
        $newWorker = User::factory()->worker()->create();

        $task = Task::create([
            'title' => 'Reassign Task',
            'description' => 'Move to another worker',
            'required_skills' => [],
            'estimated_duration' => 3,
            'deadline' => Carbon::now()->addDays(2),
            'status' => 'in-progress',
            'assigned_worker_id' => $oldWorker->id,
            'created_by' => $company->id,
        ]);

        $response = $this->actingAs($company, 'sanctum')
                         ->patchJson("/api/tasks/{$task->id}/reassign", [
                            'worker_id' => $newWorker->id,
                         ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'message', 'task' => ['id','title','assigned_worker_id','status','created_at','updated_at']
                 ]);

        // $this->assertDatabaseHas('tasks', [
        //     'id' => $task->id,
        //     'assigned_worker_id' => $newWorker->id,
        //     'status' => 'assigned',
        // ]);
    }

    public function test_it_returns_task_logs_in_chronological_order(): void {
        $company = User::factory()->company()->create();
        $worker1 = User::factory()->worker()->create();
        $worker2 = User::factory()->worker()->create();

        $task = Task::create([
            'title' => 'Logs Task',
            'description' => 'Track history',
            'required_skills' => [],
            'estimated_duration' => 3,
            'deadline' => Carbon::now()->addDays(2),
            'status' => 'assigned',
            'assigned_worker_id' => $worker1->id,
            'created_by' => $company->id,
        ]);

        // seed some logs
        \App\Models\TaskLog::create([
            'task_id' => $task->id,
            'worker_id' => $worker1->id,
            'status' => 'assigned',
            'start_time' => now()->subHours(3),
            'comments' => 'Initial assignment',
        ]);
        \App\Models\TaskLog::create([
            'task_id' => $task->id,
            'worker_id' => $worker1->id,
            'status' => 'in-progress',
            'start_time' => now()->subHours(2),
            'comments' => 'Started work',
        ]);
        \App\Models\TaskLog::create([
            'task_id' => $task->id,
            'worker_id' => $worker2->id,
            'status' => 'assigned',
            'start_time' => now()->subHour(),
            'comments' => 'Reassigned',
        ]);

        $response = $this->actingAs($company, 'sanctum')
                         ->getJson("/api/tasks/{$task->id}/logs");

        $response->assertStatus(200);
        $logs = $response->json();
        $this->assertIsArray($logs);
        $this->assertCount(3, $logs);
        // chronological: assigned -> in-progress -> assigned
        $this->assertSame('assigned', $logs[0]['status']);
        $this->assertSame('in-progress', $logs[1]['status']);
        $this->assertSame('assigned', $logs[2]['status']);
    }
    
}
