<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\TaskFeedback;   
use Illuminate\Support\Facades\DB;


class UserApiTest extends TestCase
{
    use WithFaker, RefreshDatabase; 
    // test for register user endpoint
    public function test_admin_or_company_can_register_without_rating_and_availability_status(): void {
        $userData = [
            'name' => $this->faker->name, 
            'email' => $this->faker->unique()->safeEmail, 
            'password' => bcrypt('password123'),
            'role' => $this->faker->randomElement(['admin', 'company']),
        ];

        $response = $this->postJson('/v1/api/register', $userData); 

        $response->assertStatus(201)
                 ->assertJsonStructure([
                    'message', 
                    'user', 
                    'token'
                 ]); 
    }
    // test for login user endpoint 
    public function test_worker_must_register_with_rating_and_availability_status(): void {
        $userData = [
            'name' => $this->faker->name, 
            'email' => $this->faker->unique()->safeEmail, 
            'password' => bcrypt('password123'), 
            'role' => 'worker', 
            'rating' => $this->faker->randomFloat(2,0,5), 
            'availability_status' => $this->faker->randomElement(['offline', 'busy', 'available']), 
            'random_token' => Str::random(10)
        ]; 

        $response = $this->postJson('/v1/api/register', $userData); 

        $response->assertStatus(201)
                 ->assertJsonStructure([
                    'message', 
                    'user', 
                    'token'
                 ]); 
    }

    // test for login the user 
    public function test_it_can_login_worker():void {
        
        $password = 'password123'; 

        $user = User::factory()->worker()->create([
            'email' => $this->faker->unique()->safeEmail,
            'password' => \bcrypt($password)
        ]); 
        
        $response = $this->postJson('/api/login', [
            'email' => $user->email, 
            'password' => $password
        ]); 

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'message',
                    'user', 
                    'token'
                 ]); 
    }


    // test for showing the user through id 
    public function test_it_can_show_user():void {

        $user = User::factory()->admin()->create(); 
        $id = $user->id; 

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson("/api/user/$id"); 

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'message' , 
                    'data'
                 ]); 
    }

    // test for updating the user through id 
    public function test_it_can_update_user():void {

        $user = User::factory()->worker()->create(); 
        $id = $user->id;    

        $updateData = [
            'name' => $this->faker->name, 
            'email' => $this->faker->unique()->safeEmail, 
            'rating' => $this->faker->randomFloat(2,0,5), 
            'availability_status' => $this->faker->randomElement(['offline', 'busy', 'available']), 
        ];

        $response = $this->actingAs($user, 'sanctum')
                         ->putJson("/api/user/$id", $updateData);
                         
        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'message', 
                    'updated_user'
                 ]); 
    }

    // test for deleting the user 
    public function test_it_can_delete_user():void {

        $user = User::factory()->create(); 
        $id = $user->id; 

        $user = User::factory()->company()->create(); 

        $response = $this->actingAs($user, 'sanctum')
                         ->deleteJson("/api/user/$id"); 

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'message'
                 ]); 
    }

    //test to shows all the user details only to the admin 
    public function test_it_can_show_user_details_to_admin():void {

        $admin = User::factory()->admin()->create(); 

        $response = $this->actingAs($admin, 'sanctum')
                         ->getJson("/api/admin/index"); 

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'users', 
                    'message'
                 ]); 
    }

    // test for logging out the user
    public function test_it_can_logout_the_user():void {

        $user = User::factory()->admin()->create(); 

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson("/api/logout"); 

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'message'
                 ]); 
    }
    
    // test for reset password request 
    public function test_user_can_request_forgot_password_link():void {
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
        ]);

        $response = $this->postJson('/api/forgot_password_request', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Reset Link sent successfully!',
                 ]);
    }

    // test for the reset the password 
    public function it_user_can_reset_password_with_token():void {
        $user = User::factory()->create([
            'email' => $this->faker->unique()->safeEmail,
            'password' => \bcrypt('password123')
        ]);
        
        $token = Str::random(64); 

        DB::table('password_resets')->insert([
            'email' => $user->email, 
            'token' => Hash::make($token), 
            'created_at' => now(),
        ]); 

        $newPassword = 'shubham09'; 

        $response = $this->postJson("/api/forgot_password", [
            'email' => $user->email,
            'token' => $token, 
            'password' => $newPassword, 
            'password_confirmation' => $newPassword
        ]); 

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'message'
                 ]); 
    }

    // test for not doing the request for the non registered mail 
    public function test_cannot_request_reset_for_non_existing_email():void {
        $response = $this->postJson("/api/forgot_password_request", [
            'email' => 'nonexist@example.com'
        ]); 

        $response->assertStatus(500);
    }


    public function test_it_lists_top_performers_workers(): void {
        $admin = User::factory()->admin()->create();

        $worker1 = User::factory()->worker()->create(['rating' => 4.9]);
        $worker2 = User::factory()->worker()->create(['rating' => 4.7]);
        $company = User::factory()->company()->create();

        // Seed completed tasks
        Task::create([
            'title' => 'Task A',
            'description' => 'desc',
            'required_skills' => [],
            'estimated_duration' => 2,
            'deadline' => now()->addDays(2),
            'status' => 'completed',
            'assigned_worker_id' => $worker1->id,
            'created_by' => $company->id,
        ]);
        Task::create([
            'title' => 'Task B',
            'description' => 'desc',
            'required_skills' => [],
            'estimated_duration' => 2,
            'deadline' => now()->addDays(2),
            'status' => 'completed',
            'assigned_worker_id' => $worker1->id,
            'created_by' => $company->id,
        ]);
        Task::create([
            'title' => 'Task C',
            'description' => 'desc',
            'required_skills' => [],
            'estimated_duration' => 2,
            'deadline' => now()->addDays(2),
            'status' => 'completed',
            'assigned_worker_id' => $worker2->id,
            'created_by' => $company->id,
        ]);

        // Feedbacks
        TaskFeedback::create([
            'task_id' => Task::create([
                'title' => 'Task D',
                'description' => 'desc',
                'required_skills' => [],
                'estimated_duration' => 2,
                'deadline' => now()->addDays(2),
                'status' => 'completed',
                'assigned_worker_id' => $worker1->id,
                'created_by' => $company->id,
            ])->id,
            'worker_id' => $worker1->id,
            'rating' => 5.0,
            'review' => 'Excellent'
        ]);
        TaskFeedback::create([
            'task_id' => Task::create([
                'title' => 'Task E',
                'description' => 'desc',
                'required_skills' => [],
                'estimated_duration' => 2,
                'deadline' => now()->addDays(2),
                'status' => 'completed',
                'assigned_worker_id' => $worker2->id,
                'created_by' => $company->id,
            ])->id,
            'worker_id' => $worker2->id,
            'rating' => 4.6,
            'review' => 'Good'
        ]);

        $response = $this->actingAs($admin, 'sanctum')
                         ->getJson('/api/workers/top-performers?limit=2');

        $response->assertStatus(200);
        $list = $response->json();
        $this->assertIsArray($list);
        $this->assertCount(2, $list);
        $this->assertArrayHasKey('id', $list[0]);
        $this->assertArrayHasKey('name', $list[0]);
        $this->assertArrayHasKey('rating', $list[0]);
        $this->assertArrayHasKey('tasks_completed', $list[0]);
    }

}
