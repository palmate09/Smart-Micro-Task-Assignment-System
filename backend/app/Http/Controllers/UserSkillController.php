<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; 
use App\Models\Skills; 
use App\Models\UserSkill; 


class UserSkillController extends Controller
{
    /**
     * Store a new user skill data.
     */
    public function store(Request $request) {
        try{
            $user = $request->user();
            $userRole = $user->role;  

            if(!$userRole || $userRole !== 'worker'){
                return \response()->json([
                    'message' => 'worker not found!'
                ], 404);
            }

            $userId = $user->id; 

            // validate input 
            $validated = $request->validate([
                'skill_id' => 'required|exists:skills,id', 
                'proficiency' => 'required|integer|min:1|max:10'
            ]); 

            // check if user exists
            $user = User::findOrFail($userId); 

            if(UserSkill::where('user_id' , $user->id)->where('skill_id', $validated['skill_id'])->exists()){
                return response()->json([
                    'message' => 'This skill is already assigned to the user'
                ], 409); // conflict
            }

            // create user_skill entry
            $userSkill = UserSkill::create([
                'user_id' => $userId, 
                'skill_id' => $validated['skill_id'], 
                'proficiency' => $validated['proficiency']
            ]);

            return response()->json([
                'message' => 'Skill added to user successfully!', 
                'user_skill' => $userSkill
            ], 201); 
        }
        catch(\Exception $e){
            return \response()->json([
                'error' => 'UserSkill store endpoint error', 
                'message' => $e->getMessage()
            ], 500); 
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request,string $id) {
        
        if(!$id || empty($id)){
            return response()->json([
                'message' => 'id not found'
            ], 404); 
        }
        
        try{
            $user = $request->user();
            $userRole = $user->role;  

            if(!$userRole || $userRole !== 'worker'){
                return \response()->json([
                    'message' => 'worker not found!'
                ], 404);
            }

            $userSkill = UserSkill::findOrFail($id); 

            if(!$userSkill || empty($userSkill)){
                return response()->json([
                    'message' => 'user skill not found!'
                ], 404); 
            }

            return response()->json([
                'message' => 'user skill found successfully!', 
                'data' => $userSkill
            ], 200); 
        }
        catch(\Exception $e){
            return \response()->json([
                'error' => 'UserSkill show endpoint error', 
                'message' => $e->getMessage()
            ], 500); 
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) {

        if(!$id || empty($id)){
            return response()->json([
                'message' => 'id not found'
            ], 404); 
        }
        
        try{
            $user = $request->user();
            $userRole = $user->role;  

            if(!$userRole || $userRole !== 'worker'){
                return \response()->json([
                    'message' => 'worker not found!'
                ], 404);
            }

            $userId = $user->id; 

            // validate the input 
            $validated = $request->validate([
                'skill_id' => 'sometimes|exists:skills,id',
                'proficiency' => 'required|integer|min:1|max:10',
            ]);

            $userSkill = UserSkill::findOrFail($id); 

            $update = $userSkill->update([
                'skill_id' => $validated['skill_id'], 
                'proficiency' => $validated['proficiency']
            ]); 

            return \response()->json([
                'message' => 'User skills data updated successfully!', 
                'data' => $userSkill
            ], 200); 
        }
        catch(\Exception $e){
            return \response()->json([
                'error' => 'UserSkill update endpoint error', 
                'message' => $e->getMessage()
            ], 500); 
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request , string $id) {
        
        if(!$id || empty($id)){
            return response()->json([
                'message' => 'id not found'       
            ], 404); 
        }
        
        try{
            
            $user = $request->user(); 
            $userRole = $user->role; 

            if(!$userRole || $userRole !== 'worker'){
                return \response()->json([
                    'message' => 'worker not found!'
                ], 404); 
            }

            $userId = $user->id; 
            $userSkill = UserSkill::findOrFail($id);
            $userSkill->delete(); 

            return \response()->json([
                'message' => 'skill removed successfully!'
            ],200);

        }
        catch(\Exception $e){
            return \response()->json([
                'error' => 'UserSkill destroy endpoint error', 
                'message' => $e->getMessage()
            ], 500); 
        }
    }
}
