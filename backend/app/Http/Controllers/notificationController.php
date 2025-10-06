<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class notificationController extends Controller
{
    public function show(Request $request) {
        try{
            $user = $request->user();

            if(!$user){
                return \response()->json(['message' => 'Unauthenticated'], 401);
            }

            $notifications = Notification::where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->get();

            return \response()->json([
                'message' => 'Notifications retrieved successfully!',
                'data' => $notifications
            ], 200);
            
        }
        catch(\Exception $e){
            return \response()->json([
                'error' => 'Notification show endpoint error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function markAsRead(Request $request, string $id) {
        if(!$id || empty($id)){
            return \response()->json([
                'message' => 'id not found'
            ], 404);
        }

        try{
            $user = $request->user();
            if(!$user){
                return \response()->json(['message' => 'Unauthenticated'], 401);
            }

            $notification = Notification::where('id', $id)
                                        ->where('user_id', $user->id)
                                        ->first();

            if(!$notification){
                return \response()->json([
                    'message' => 'Notification not found'
                ], 404);
            }

            $notification->read_at = now();
            $notification->save();

            return \response()->json([
                'message' => 'Notification marked as read'
            ], 200);
        }
        catch(\Exception $e){
            return \response()->json([
                'error' => 'Notification markAsRead endpoint error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function unreadCount(Request $request) {
        try{
            $user = $request->user();
            if(!$user){
                return \response()->json(['message' => 'Unauthenticated'], 401);
            }

            $count = Notification::where('user_id', $user->id)
                                 ->whereNull('read_at')
                                 ->count();

            return \response()->json([
                'unread_count' => $count
            ], 200);
        }
        catch(\Exception $e){
            return \response()->json([
                'error' => 'Notification unreadCount endpoint error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
