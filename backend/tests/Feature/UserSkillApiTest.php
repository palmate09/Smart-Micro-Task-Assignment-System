<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Skills; 
use App\Models\User; 
use App\Models\UserSkill; 

class UserSkillApiTest extends TestCase
{
    use WithFaker, RefreshDatabase; 

    // test for adding the skills by the user
    public function test_user_can_add_skills():void {
        
        $user = User::factory()->worker()->create(); 
        $userId = $user->id;
        
        $skill = Skills::factory()->create(); 
        $skillId = $skill->id; 

        $inputData = [
            'user_id'=> $userId, 
            'skill_id' => $skillId, 
            'proficiency' => $this->faker->numberBetween(1, 10)
        ]; 

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson("/api/user/skills", $inputData); 

        $response->assertStatus(201)
                 ->assertJsonStructure([
                    'message', 
                    'user_skill'
                 ]);
    }

    // test to update the skills by the user;
    public function test_user_can_update_skills():void {

        $user = User::factory()->worker()->create(); 
        $userId = $user->id; 

        $Skill = Skills::factory()->create(); 
        $skillId = $Skill->id; 

        $userSkill = UserSkill::factory()->create(); 
        $id = $userSkill->id; 

        $updatedData = [
            'user_id' => $userId, 
            'skill_id' => $skillId, 
            'proficiency' => $this->faker->numberBetween(1, 10)
        ]; 

        $response = $this->actingAs($user, 'sanctum')
                         ->putJson("/api/user/skills/$id", $updatedData); 

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'message', 
                    'data'
                 ]); 
    }

    // test to show the particular skill of the user; 
    public function test_user_can_show_skill():void {

        $user = User::factory()->worker()->create(); 

        $userSkill = UserSkill::factory()->create(); 
        $id = $userSkill->id; 

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson("/api/user/skills/$id"); 

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'message',
                    'data'
                 ]); 
    }

    // test to delete the particular skill of the user; 
    public function test_user_can_delete_particular_skill():void {

        $user = User::factory()->worker()->create(); 

        $userSkill = UserSkill::factory()->create();
        $id = $userSkill->id; 
        
        $response = $this->actingAs($user, 'sanctum')
                         ->deleteJson("/api/user/skills/$id"); 

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'message'
                 ]); 
    }
}
