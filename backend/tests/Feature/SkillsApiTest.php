<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Skills; 
use App\Models\User; 

class SkillsApiTest extends TestCase
{
    use WithFaker, RefreshDatabase; 
    
    // test to show all the skills 
    public function test_it_can_show_all_skills():void {

        $user = User::factory()->worker()->create(); 
        $skills = Skills::factory()->create(); 

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson("/api/skills");
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'message',
                    'data'
                 ]); 
    }

    // test to add the new skills 
    public function test_it_can_add_new_skill():void {

        $user = User::factory()->admin()->create(); 
        $skillData = [
            'name' => $this->faker->unique()->jobTitle
        ]; 

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson("/api/skills", $skillData); 
    

        $response->assertStatus(201)
                 ->assertJsonStructure([
                    'message', 
                    'data'
                 ]); 
    }

    // test to show the new skills 
    public function test_it_can_show_skill_by_id():void {

        $user = User::factory()->worker()->create(); 
        $skill = Skills::factory()->create(); 
        $id = $skill->id; 

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson("/api/skills/$id"); 

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'message', 
                    'data'
                 ]); 
    }

    // test to update the skill data 
    public function test_it_can_update_skill_by_id():void {

        $user = User::factory()->admin()->create(); 
        $skill = Skills::factory()->create(); 
        $id = $skill->id; 

        $updateData = [
            'name' => $this->faker->unique()->jobTitle
        ]; 

        $response = $this->actingAs($user, 'sanctum')
                         ->putJson("/api/skills/$id", $updateData); 
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'message', 
                    'data'
                 ]);
    }

    // test to delete the skill data
    public function test_it_can_delete_skill_by_id():void {

        $user = User::factory()->admin()->create(); 
        $skill = Skills::factory()->create(); 
        $id = $skill->id; 

        $response = $this->actingAs($user, 'sanctum')
                         ->delete("/api/skills/$id");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'message'
                 ]); 
    }
}
