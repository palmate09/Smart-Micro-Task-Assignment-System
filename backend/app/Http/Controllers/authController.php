<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; 
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;


class authController extends Controller {
    
    //  login the user
    public function login(Request $request) {
        try{
            $validated = $request->validate([
                'email' => 'required|string|email', 
                'password' => 'required|string'
            ]);
            
            if(!Auth::attempt($validated)){
                throw ValidationException::withMessages([ 'email' => ['The provided credentials are incorrect.'] ]);
            }

            $user = User::where('email', $validated['email'])->firstOrFail(); 
            $token = $user->createToken('auth_token')->plainTextToken; 
            

            return \response()->json([
                'message' => 'User logged in Successfully!', 
                'user' => $user, 
                'token' => $token
            ], 200); 
        }
        catch(ValidationException $e){
            return response()->json([
                'error' => 'Invalid Credentials', 
                'message' => $e->getMessage() 
            ], 500); 
        }
        catch(\Exception $e){
            return response()->json([
                'error' => 'Auth Login endpoint error', 
                'message' => $e->getMessage()
            ], 500); 
        }
    }

    // logout the user
    public function logout(Request $request) {
        try{
            $request->user()->tokens()->delete(); 

            return response()->json([
                'message' => 'Logged out successfully!'
            ], 200); 
        }
        catch(\Exception $e){
            return response()->json([
                'error' => 'Auth Logout endpoint error', 
                'message' => $e->getMessage()
            ], 500); 
        }
    }

    // reset password request 
    public function forgot_password_request(Request $request) {
        
        try{

            $request->validate([
                'email' => 'required|email|exists:users,email'
            ]); 

            // send reset link
            $status = Password::sendResetLink(
                $request->only('email'),
                function ($user, $token) {
                    $resetUrl = config('app.frontend_url') . "/reset-password?token={$token}&email={$user->email}";
                    // Send your email manually using Mail::to($user)->send(new ResetPasswordMail($resetUrl))
                }
            );

            if($status !== Password::RESET_LINK_SENT){
                return \response()->json([
                    'message' => 'Reset Link is not sent yet'
                ], 400); 
            }

            return \response()->json([
                'message' => 'Reset Link sent successfully!'
            ], 200); 
        }
        catch(\Exception $e){
            return response()->json([
                'error' => 'Auth Reset Password Request Error', 
                'message' => $e->getMessage()
            ], 500); 
        }
    }

    // reset the password 
    public function forgot_password(Request $request) {

        try{

            $validated = $request->validate([
                'email' => 'required|email|exists:users, email', 
                'token' => 'required|string', 
                'password' => 'required|string|confirmed|min:8'
            ]); 

            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'), 
                function($user, $password){
                    //update the password 
                    $user->forceFill([
                        'password' => $password, // auto hashed because of the casts()
                    ])->save(); 
                }
            );

            if($status !== Password::PASSWORD_RESET) {
                return response()->json([
                    'message' => 'Password is not updated yet!'
                ], 400); 
            }

            return \response()->json([
                'message' => 'Password updated successfully!'
            ], 200);
        }
        catch(\Exception $e){
            return response()->json([
                'error'  => 'Auth Forgot  password Error', 
                'message' => $e->getMessage()
            ], 500); 
        }
    }
}
