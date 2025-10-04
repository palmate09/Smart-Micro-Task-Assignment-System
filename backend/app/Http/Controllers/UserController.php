<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Enums\userType;  
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // shows all the users to the admin only 
    public function index(string $admin_id) {

        try{
            $userData = User::find($admin_id); 

            if(!$userData || empty($userData)){
                return response()->json([
                    'message' => 'User not found!'
                ], 404); 
            }

            if($userData->role === userType::admin){
                $users = User::all(); 

                return response()->json([
                    'users' => $users, 
                    'message' => 'users data found successfully!'
                ], 200); 
            }
        }
        catch(\Exception $e){
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'user index endpoint error'
            ], 500); 
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{

            $validated = $request->validate([
                'name' => 'required|string|max:256', 
                'email' => 'required|string|email|unique:users, email', 
                'password' => 'required|string|min:6',
                'role' => 'required|string|in:admin, user, worker', 
                'rating' => 'nullable|numeric|min:0|max:5', 
                'availability_status' => 'required|string|in:offline, busy, available'
            ]);

            $user = User::create([
                'name' => $validated['name'], 
                'email' => $validated['email'],
                'password' => $validated['password'], 
                'role' => $validated['role'], 
                'rating' => $validated['rating'], 
                'availability_status' => $validated['availability_status']
            ]); 

            $token = $user->createToken('auth_token')->plainTextToken; 

            return response()->json([
                'message' => 'User registered successfully!', 
                'user' => $user, 
                'token' => $token
            ], 201); 
        } 
        catch(\Exception $e){
            return response()->json([
                'message' => $e->getMessage(), 
                'error' => "Users store endpoint error"
            ], 500); 
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
