<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User; 
use App\Models\Task; 
use App\Models\Skills; 


class TaskApiTest extends TestCase
{
    use WithFaker, RefreshDatabase; 
    
    // test for filter the tasks according to the status
    public function test_it_can_filter_task_according_to_status(): void {

        $user = User::factory()->worker()->create(); 
        $task = Task::factory()->create(); 

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson("/api/tasks"); 

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'message',
                    'data'
                 ]);
    }

    public function test_it_can_search_tasks_by_skill_status_and_worker(): void {
        $user = User::factory()->worker()->create();

        // create skills
        $php = Skills::firstOrCreate(['name' => 'PHP']);
        $laravel = Skills::firstOrCreate(['name' => 'Laravel']);

        // tasks
        $task1 = Task::create([
            'title' => 'Fix PHP validation',
            'description' => 'Bugfix',
            'required_skills' => [$php->id, $laravel->id],
            'estimated_duration' => 5,
            'deadline' => now()->addDays(7),
            'status' => 'pending',
            'created_by' => User::factory()->company()->create()->id,
        ]);

        $task2 = Task::create([
            'title' => 'Build REST API',
            'description' => 'API work',
            'required_skills' => [$php->id],
            'estimated_duration' => 10,
            'deadline' => now()->addDays(14),
            'status' => 'in-progress',
            'assigned_worker_id' => $user->id,
            'created_by' => User::factory()->company()->create()->id,
        ]);

        // filter by skill + status
        $res1 = $this->actingAs($user, 'sanctum')
                     ->getJson('/api/tasks/search?skill='.$php->id.'&status=pending');

        $res1->assertStatus(200);
        $list1 = $res1->json();
        $this->assertCount(1, $list1);
        $this->assertSame($task1->id, $list1[0]['id']);

        // filter by worker
        $res2 = $this->actingAs($user, 'sanctum')
                     ->getJson('/api/tasks/search?worker_id='.$user->id);

        $res2->assertStatus(200);
        $list2 = $res2->json();
        $this->assertCount(1, $list2);
        $this->assertSame($task2->id, $list2[0]['id']);
    }

    // test for storing new task in the db
    public function test_it_can_store_task(): void {

        $user = User::factory()->company()->create();
        
        $taskData = [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'required_skills'=> Skills::inRandomOrder()->take(rand(1, 3))->pluck('id')->toArray(),
            'estimated_duration' => $this->faker->numberBetween(1, 40), 
            'deadline' => $this->faker->dateTimeBetween('now', '+2 months')->format('Y-m-d H:i:s'), 
            'status' => $this->faker->randomElement(['pending', 'assigned', 'in-progress', 'completed', 'cancelled']),
            'created_by' => $user->id
        ]; 

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson("/api/tasks", $taskData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                    'message',
                    'data'
                 ]); 
    }

    // test for updating the task
    public function test_it_can_update_task(): void {

        $user = User::factory()->company()->create(); 
        $task = Task::factory()->create(); 
        $id = $task->id; 

        $updatedTask = [
            'title' => $this->faker->sentence(4),                        // required, string
            'description' => $this->faker->paragraph(),                  // optional, string
            'required_skills' => Skills::inRandomOrder()->take(rand(1, 3))->pluck('id')->toArray(),
            'estimated_duration' => $this->faker->numberBetween(1, 100), // optional, integer
            'deadline' => $this->faker->dateTimeBetween('now', '+2 months')->format('Y-m-d H:i:s'), 
            'status' => $this->faker->randomElement(['pending','assigned','in-progress','completed','cancelled']), 
            'assigned_worker_id' => User::factory()->worker()->create()->id, 
        ];

        $response = $this->actingAs($user, 'sanctum')
                         ->putJson("/api/tasks/$id", $updatedTask); 

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'message', 
                    'data'
                 ]); 
    }

    // test for showing the specific task 
    public function test_it_can_show_specific_task(): void {

        $user = User::factory()->company()->create(); 
        $task = Task::factory()->create(); 
        $id = $task->id; 

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson("/api/tasks/$id"); 

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'message', 
                    'data'
                 ]); 
    }

    // test for deleting the specific task 
    public function test_it_can_delete_specific_task():void {

        $user = User::factory()->company()->create(); 
        $task = Task::factory()->create(); 
        $id =$task->id; 

        $response = $this->actingAs($user, 'sanctum')
                         ->deleteJson("/api/tasks/$id");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'message'
                 ]); 
    }
}
