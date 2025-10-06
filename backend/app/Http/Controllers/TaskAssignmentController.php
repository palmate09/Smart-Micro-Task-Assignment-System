<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task; 
use App\Models\User;
use App\Models\TaskFeedback;
use App\Models\TaskLog;

class TaskAssignmentController extends Controller {

    /**
     * Compute best matching worker for a task by skill overlap and return top candidate for consent.
     */
    public function assignTaskAutomatically(Request $request, Task $task){
        try{
            if($task->assigned_worker_id){
                return \response()->json([
                    'message' => 'Task is already assigned'
                ], 400);
            }

            $requiredSkills = is_array($task->required_skills) ? $task->required_skills : [];

            $workers = User::where('role', 'worker')->with('skills:id')->get();

            $bestWorker = null;
            $highestScore = -1;

            foreach($workers as $worker){
                $workerSkillIds = $worker->skills->pluck('id')->toArray();
                $score = count(array_intersect($requiredSkills, $workerSkillIds));
                if($score > $highestScore){
                    $highestScore = $score;
                    $bestWorker = $worker;
                }
            }

            if(!$bestWorker || $highestScore <= 0){
                return \response()->json([
                    'message' => 'No suitable workers found for required skills'
                ], 404);
            }

            return \response()->json([
                'message' => 'Top candidate identified. Awaiting consent.',
                'task_id' => $task->id,
                'candidate' => [
                    'id' => $bestWorker->id,
                    'name' => $bestWorker->name,
                    'score' => $highestScore,
                ],
            ], 200);
        }
        catch(\Exception $e){
            return \response()->json([
                'error' => 'assignTaskAutomatically endpoint error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle worker consent. If accepted, assign. If declined, assign to next best worker with lower score.
     */
    public function respondToTask(Request $request, Task $task){
        try{
            $validated = $request->validate([
                'worker_id' => 'required|uuid|exists:users,id',
                'consent' => 'required|boolean',
            ]);

            if($task->assigned_worker_id){
                return \response()->json([
                    'message' => 'Task is already assigned'
                ], 400);
            }

            $requiredSkills = is_array($task->required_skills) ? $task->required_skills : [];

            $workers = User::where('role', 'worker')->with('skills:id')->get();

            // Build ranking high to low by score
            $ranked = [];
            foreach($workers as $worker){
                $workerSkillIds = $worker->skills->pluck('id')->toArray();
                $score = count(array_intersect($requiredSkills, $workerSkillIds));
                if($score > 0){
                    $ranked[] = [
                        'worker' => $worker,
                        'score' => $score,
                    ];
                }
            }

            if(empty($ranked)){
                return \response()->json([
                    'message' => 'No suitable workers found for required skills'
                ], 404);
            }

            usort($ranked, function($a, $b){
                return $b['score'] <=> $a['score'];
            });

            // Consent flow
            if($validated['consent'] === true){
                $consentingWorker = collect($ranked)->firstWhere('worker.id', $validated['worker_id']);
                if(!$consentingWorker){
                    return \response()->json([
                        'message' => 'Worker is not eligible for this task'
                    ], 400);
                }
                $task->assigned_worker_id = $validated['worker_id'];
                $task->status = 'assigned';
                $task->save();

                return \response()->json([
                    'message' => 'Task assigned successfully!',
                    'task_id' => $task->id,
                    'assigned_worker' => [
                        'id' => $consentingWorker['worker']->id,
                        'name' => $consentingWorker['worker']->name,
                        'score' => $consentingWorker['score'],
                    ],
                ], 200);
            }

            // Declined: pick the next best (strictly lower or next in ranking)
            $declinedWorkerId = $validated['worker_id'];
            $next = null;
            foreach($ranked as $entry){
                if($entry['worker']->id === $declinedWorkerId){
                    continue;
                }
                $next = $entry; // first other entry is next best due to sorting
                break;
            }

            if(!$next){
                return \response()->json([
                    'message' => 'No alternative worker available for this task'
                ], 404);
            }

            // Assign to next best directly as per requirement
            $task->assigned_worker_id = $next['worker']->id;
            $task->status = 'assigned';
            $task->save();

            return \response()->json([
                'message' => 'Initial worker declined. Assigned to next best worker.',
                'task_id' => $task->id,
                'assigned_worker' => [
                    'id' => $next['worker']->id,
                    'name' => $next['worker']->name,
                    'score' => $next['score'],
                ],
            ], 200);
        }
        catch(\Exception $e){
            return \response()->json([
                'error' => 'respondToTask endpoint error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
 
    // Update the current status of the task
    public function updateStatusTask(Request $request , string $id) {

        if(!$id || empty($id)){
            return response()->json([
                'message' => 'id not found'
            ],404); 
        }

        try{

            $task = Task::findOrFail($id); 

            if(!$task){
                return response()->json([
                    'message' => 'task not found'
                ], 404); 
            }

            $validated = $request->validate([
                'status' => 'required|string|in:pending,in-progress,completed,cancelled,assigned'
            ]);

            $task->update([
                'status' => $validated['status']
            ]); 

            return response()->json([
                'message' => 'Task status updated successfully!', 
                'task' => $task
            ], 200); 
        }
        catch(\Exception $e){
            return response()->json([
                'error' => 'TaskAssigments updateStatusTask endpoint error', 
                'message'=> $e->getMessage()
            ], 500); 
        }
    }

    // submit the feedback of the user by the company
    public function submitFeedback(Request $request, string $id){

        if(!$id || empty($id)){
            return response()->json([
                'message' => 'id not found'
            ], 404); 
        }

        try{

            $task = Task::findOrFail($id); 

            if(!$task){
                return response()->json([
                    'message' => 'task not found'
                ], 404); 
            }

            $validated = $request->validate([
                'rating' => 'required|numeric|min:0|max:5',
                'review' => 'nullable|string|max:500'
            ]); 

            if(!$task->assigned_worker_id){
                return response()->json([
                    'message' => 'Task has no assigned worker to review'
                ], 400);
            }

            $feedback = TaskFeedback::create([
                'task_id' => $task->id,
                'worker_id' => $task->assigned_worker_id,
                'rating' => $validated['rating'],
                'review' => $validated['review'] ?? null,
            ]);

            return response()->json([
                'message' => 'Feedback submitted successfully!',
                'feedback' => $feedback
            ], 201);
        }
        catch(\Exception $e){
            return response()->json([
                'error' => 'SubmitFeedback endpoint error', 
                'message' => $e->getMessage()
            ], 500); 
        }
    }

    // Reassign the task to another worker
    public function reassign(Request $request, string $id){
        if(!$id || empty($id)){
            return response()->json([
                'message' => 'id not found'
            ], 404);
        }

        try{
            $validated = $request->validate([
                'worker_id' => 'required|uuid|exists:users,id'
            ]);

            $task = Task::findOrFail($id);
            if(!$task){
                return response()->json([
                    'message' => 'task not found'
                ], 404);
            }

            $oldWorkerId = $task->assigned_worker_id;
            $newWorkerId = $validated['worker_id'];

            if($oldWorkerId === $newWorkerId){
                return response()->json([
                    'message' => 'Task already assigned to this worker'
                ], 400);
            }

            // Optionally check availability here if field exists
            $task->assigned_worker_id = $newWorkerId;
            if($task->status !== 'assigned'){
                $task->status = 'assigned';
            }
            $task->save();

            // Log reassignment
            TaskLog::create([
                'task_id' => $task->id,
                'worker_id' => $newWorkerId,
                'status' => 'assigned',
                'start_time' => now(),
                'comments' => 'Task reassigned'.($oldWorkerId ? ' from '.$oldWorkerId : ''),
            ]);

            // TODO: Notify old and new workers (out of scope for now)

            return response()->json([
                'message' => 'Task reassigned successfully',
                'task' => $task
            ], 200);
        }
        catch(\Exception $e){
            return response()->json([
                'error' => 'Task reassign endpoint error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Get chronological logs for a task
    public function logs(Request $request, string $id){
        if(!$id || empty($id)){
            return response()->json([
                'message' => 'id not found'
            ], 404);
        }

        try{
            $task = Task::findOrFail($id);
            if(!$task){
                return response()->json([
                    'message' => 'task not found'
                ], 404);
            }

            $logs = TaskLog::where('task_id', $task->id)
                           ->orderBy('created_at')
                           ->get()
                           ->map(function($log){
                                return [
                                    'status' => $log->status,
                                    'worker_id' => $log->worker_id,
                                    'time' => $log->created_at?->toISOString(),
                                ];
                           });

            return response()->json($logs, 200);
        }
        catch(\Exception $e){
            return response()->json([
                'error' => 'Task logs endpoint error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
