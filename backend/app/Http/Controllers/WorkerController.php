<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;
use App\Models\TaskFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkerController extends Controller
{
    public function topPerformers(Request $request)
    {
        try{
            $limit = $request->query('limit', 5);

            // Fetch workers
            $workers = User::where('role', 'worker')
                ->select('id', 'name', 'rating')
                ->get();

            $data = $workers->map(function ($worker) {
                // Count completed tasks
                $tasksCompleted = Task::where('assigned_worker_id', $worker->id)
                                    ->where('status', 'completed')
                                    ->count();

                // Average feedback rating
                $avgFeedback = TaskFeedback::where('worker_id', $worker->id)
                                        ->avg('rating');

                // Combine performance metrics
                $overallRating = $avgFeedback ?? $worker->rating ?? 0;

                return [
                    'id' => $worker->id,
                    'name' => $worker->name,
                    'rating' => round($overallRating, 2),
                    'tasks_completed' => $tasksCompleted,
                ];
            });

            // Sort by rating desc, then by tasks completed desc
            $sorted = $data->sortByDesc('rating')
                        ->sortByDesc('tasks_completed')
                        ->values()
                        ->take($limit);

            return response()->json($sorted->values(), 200);
        }
        catch(\Exception $e){
            return response()->json([
                'error' => 'topPerformers endpoint error', 
                'message' => $e->getMessage()
            ], 500); 
        }
    }
}
