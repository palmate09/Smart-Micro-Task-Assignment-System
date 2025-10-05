<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Enums\userType;  
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;


class UserController extends Controller
{
    // shows all the users to the admin only 
    public function index(Request $request) {

        try{
            $authUser = $request->user();

            if (!$authUser || $authUser->role != 'admin') {
                return response()->json([
                    'message' => 'Only admin can see the users details'
                ], 403);
            }

            $users = User::all();

            return response()->json([
                'users' => $users,
                'message' => 'Users data found successfully!'
            ], 200);
        }
        catch(\Exception $e){
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'user index endpoint error'
            ], 500); 
        }
    }

    /**
     * Store a newly created user in storage. or register the user
     */
    public function store(Request $request) {
        try{

            //Normalize role input
            $request->merge([
                'role' => $request->has('role') ? Str::lower(trim($request->input('role'))) : null,
            ]);

            $validated = $request->validate([
                'name' => 'required|string|max:256',
                'email' => 'required|string|email|unique:users,email',
                'password' => 'required|string|min:6',
                'role' => ['required', 'string', Rule::in(['admin','company','worker'])],
                // Only required if role is worker
                'rating' => 'nullable|numeric|min:0|max:5|required_if:role,worker',
                'availability_status' => 'nullable|string|in:offline,busy,available|required_if:role,worker',
            ]);

            $user = User::create([
                'name' => $validated['name'], 
                'email' => $validated['email'],
                'password' => $validated['password'], 
                'role' => $validated['role'], 
                'rating' => $validated['rating'] ?? null, 
                'availability_status' => $validated['availability_status'] ?? null
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
     * Display the specific user's data .
     */
    public function show(string $id) {
        if(!$id || empty($id)){
            return response()->json([
                'message'=> 'id not found'
            ], 404); 
        }

        try{

            $user = User::find($id); 

            if(!$user || empty($user)){
                return response()->json([
                    'message' => 'user not found'
                ], 404); 
            }

            return \response()->json([
                'message' => 'user data found successfully!', 
                'data' => $user
            ], 200); 
        }
        catch(\Exception $e){
            return response()->json([
                'error' => 'Users show endpoint error', 
                'message' => $e->getMessage()
            ], 500); 
        }
    }

    /**
     * Update the specific user's data.
     */
    public function update(Request $request, string $id) {
        
        if(!$id || empty($id)){
            return \response()->json([
                'message' => 'id not found'
            ], 404); 
        }

        try{

            $user = User::find($id);
            
            if(!$user || empty($user)){
                return response()->json([
                    'message' => 'user not found'
                ], 404); 
            }

            if($request->user()->id !== $user->id){
                return \response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }


            $updatedValidation = $request->validate([
                'name' => 'sometimes|string|max:256',
                'email' => 'sometimes|string|email|unique:users,email',
                'password' => 'sometimes|string|min:6',
                'role' => ['sometimes', 'string', Rule::in(['admin','company','worker'])],
                // Only required if role is worker
                'rating' => 'nullable|numeric|min:0|max:5|required_if:role,worker',
                'availability_status' => 'nullable|string|in:offline,busy,available|required_if:role,worker'
            ]);
            

            foreach($updatedValidation as $key => $value){
                if($request->has($key)) {
                    $user->$key = $updatedValidation["$key"]; 
                }
                if($key === 'password' && $request->has('password')){
                    $user->password = Hash::make($updatedValidation['password']);;  
                }
            }


            return response()->json([
                'message' => 'User data updated successfully!', 
                'updated_user' => $user
            ], 200); 
        }
        catch(\Exception $e){
            return \response()->json([
                'message' => $e->getMessage(), 
                'error' => 'users update endpoint error'
            ], 500);
        }
    }

    /**
     * Remove the specific user from the storage.
     */
    public function destroy(string $id) {
        if(!$id || empty($id)){
            return respnose()->json([
                'message' => 'id not found'
            ], 404); 
        }

        try{

            $user = User::find($id); 

            if(!$user || empty($user)){
                return response()->json([
                    'message' => 'User not found!'
                ], 404); 
            }

            $user->delete(); 

            return response()->json([
                'message' => 'User deleted successfully!'
            ], 200); 
        }
        catch(\Exception $e){
            return response()->json([
                'error' => 'Users destroy endpoint error', 
                'message' => $e->getMessage()
            ], 500); 
        }
    }
}
