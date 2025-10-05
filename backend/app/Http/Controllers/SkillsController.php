<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; 
use App\Models\Skills; 

class SkillsController extends Controller {
    /**
     * Display  all the skills to the workers.
     */
    public function index(Request $request) {
        
        try{
            $skills = Skills::all(); 

            return response()->json([
                'message' => 'All skills received successfully!', 
                'data' => $skills
            ], 200); 
        }
        catch(\Exception $e){
            return response()->json([
                'error' => 'Skills index endpoint error', 
                'message' => $e->getMessage()
            ], 500); 
        }
    }

    /**
     * Store a newly created skills by the admin.
     */
    public function store(Request $request) {
        
        try{
            $userRole = $request->user()->role; 

            if($userRole === 'worker'){
                return response()->json([
                    'message' => 'admin not found!'
                ], 404); 
            }

            $validated = $request->validate([
                'name' => 'required|string|max:100|unique:skills,name|regex:/^[a-zA-Z0-9\s]+$/'
            ]); 

            $skillsData = Skills::create([
                'name' => $validated['name']
            ]); 

            return response()->json([
                'message' => 'skills data stored successfully!', 
                'data' => $skillsData
            ], 201);
        }
        catch(\Exception $e){
            return response()->json([
                'error' => 'Skills store endpoint error', 
                'message' => $e->getMessage()
            ], 500); 
        }
    }

    /**
     * Display the specified skill .
     */
    public function show(string $id) {
        
        if(!$id || empty($id)){
            return response()->json([
                'message' => 'id not found'
            ], 404); 
        }

        try{

            $skill = Skills::find($id); 

            if(!$skill){
                return \response()->json([
                    'message' => 'skill not found!'
                ], 404); 
            }

            return \response()->json([
                'message' => 'skill found successfully!', 
                'data' => $skill
            ],200); 

        }
        catch(\Exception $e){
            return response()->json([
                'error' => 'Skills show endpoint error', 
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
                'message' => 'id not found!'
            ], 404); 
        }

        try{

            $userRole = $request->user()->role; 

            if(!$userRole || $userRole === 'worker'){
                return \response()->json([
                    'message' => 'admin not found!'
                ], 404); 
            }

            $skill = Skills::findOrFail($id); 

            $validated = $request->validate([
                'name' => 'required|string|max:100|unique:skills,name' .$skill->id
            ]); 

            $skill->update([
                'name' => $validated['name']
            ]); 

            return response()->json([
                'message' => 'skill data updated successfully!', 
                'data' => $skill
            ], 200); 
        }
        catch(\Exception $e){
            return response()->json([
                'error' => 'Skills update endpoint error', 
                'message' => $e->getMessage()
            ], 500); 
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id) {
        
        if(!$id || empty($id)){
            return response()->json([
                'message' => 'id not found!'
            ], 404); 
        }

        try{

            $userRole = $request->user()->role; 

            if(!$userRole || $userRole === 'worker'){
                return response()->json([
                    'message' => 'admin not found'
                ], 404); 
            }

            $skill = Skills::findOrFail($id); 

            if(!$skill){
                return response()->json([
                    'message' => 'skill not found'
                ]); 
            }

            $skill->delete(); 

            return \response()->json([
                'message' => 'skill deleted successfully!'
            ], 200); 

        }
        catch(\Exception $e){
            return response()->json([
                'error' => 'Skills destroy endpoint error', 
                'message' => $e->getMessage()
            ], 500); 
        }
    }
}
