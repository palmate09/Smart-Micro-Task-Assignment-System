<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Notifications\TaskAssignmentNotification; 
use App\Models\User; 
use App\Models\Task; 

class TaskController extends Controller
{
    /**
     * Display all the task with the assigned worker to it.
     */
    public function index(Request $request){
        
        try{
            $status = $request->query('status'); 

            $query = Task::query(); 

            if($status){
                $query->where('status', $status); 
            }

            $tasks = $query->get(); 

            return \response()->json([
                'message' => 'tasks retireved successfully!', 
                'data' => $tasks
            ], 200); 
        }
        catch(\Exception $e){
            return \response()->json([
                'error' => 'Task index endpoint error', 
                'message' => $e->getMessage()
            ], 500); 
        }
    }

    /**
     * Search tasks by optional filters: skill (skill id), status, worker_id
     */
    public function search(Request $request){
        try{
            $skill = $request->query('skill');
            $status = $request->query('status');
            $workerId = $request->query('worker_id');

            $query = Task::query();

            if($skill){
                // required_skills is stored as array of skill UUIDs
                $query->whereJsonContains('required_skills', $skill);
            }
            if($status){
                $query->where('status', $status);
            }
            if($workerId){
                $query->where('assigned_worker_id', $workerId);
            }

            $tasks = $query->get();

            return \response()->json($tasks, 200);
        }
        catch(\Exception $e){
            return \response()->json([
                'error' => 'Task search endpoint error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created Task in the db.
     */
    public function store(Request $request) {
        try{

            $user = $request->user(); 
            $userRole = $user->role;

            if(!$userRole || $userRole !== 'company'){
                return \response()->json([
                    'message' => 'The given user is not a company'
                ], 404); 
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'required_skills' => 'nullable|array', // expects an array of skill IDs
                'required_skills.*' => 'uuid|exists:skills,id', // each skill must be a valid UUID from skills table
                'estimated_duration' => 'nullable|integer|min:1', // hours
                'deadline' => 'nullable|date|after_or_equal:today',
                'status' => 'sometimes|in:pending,assigned,in-progress,completed,cancelled',
            ]);

            $task = Task::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'required_skills' => $validated['required_skills'] ?? [],
                'estimated_duration' => $validated['estimated_duration'] ?? null,
                'deadline' => $validated['deadline'] ?? null,
                'status' => $validated['status'] ?? 'pending',
                'created_by' => $user->id,
            ]);
            
            return \response()->json([
                'message' => 'Task created successfully!', 
                'data' => $task
            ], 201); 
        }
        catch(\Exception $e){
            return \response()->json([
                'error' => 'Task store endpoint error', 
                'message' => $e->getMessage()
            ], 500); 
        }
    }

    /**
     * Display the specific id for the task.
     */
    public function show(string $id) {
        
        if(!$id || empty($id)){
            return \response()->json([
                'message' => 'id not found'
            ], 404); 
        }
        
        try{
            $task = Task::findOrFail($id); 

            if(!$task){
                return \response()->json([
                    'message' => 'Task not found!'
                ],404); 
            }

            return response()->json([
                'message' => 'Task received successfully!', 
                'data' => $task
            ], 200); 
        }
        catch(\Exception $e){
            return \response()->json([
                'error' => 'Task show endpoint error', 
                'message' => $e->getMessage()
            ], 500); 
        }
    }

    /**
     * Update the specific task.
     */
    public function update(Request $request, string $id) {
        
        if(!$id || empty($id)){
            return \response()->json([
                'message' => 'id not found'
            ], 404); 
        }
        
        try{

            $user = $request->user(); 
            $userRole = $user->role; 

            if(!$userRole || $userRole !== 'company'){
                return \response()->json([
                    'message' => 'company not found!'
                ], 404); 
            }

            $task = Task::findOrFail($id); 

            if(!$task){
                return response()->json([
                    'message' => 'task not found!'
                ], 404); 
            }

            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|nullable|string',
                'required_skills' => 'sometimes|array',
                'required_skills.*' => 'uuid|exists:skills,id',
                'estimated_duration' => 'sometimes|nullable|integer|min:1',
                'deadline' => 'sometimes|nullable|date|after_or_equal:today',
                'status' => 'sometimes|in:pending,assigned,in-progress,completed,cancelled',
            ]);

            $updatedTask = $task->update([
                'title' => $validated['title'], 
                'description' => $validated['description'],
                'required_skills' => $validated['required_skills'], 
                'estimated_duration' => $validated['estimated_duration'], 
                'deadline' => $validated['deadline'],
                'status' => $validated['status'], 
                'created_by' => $user->id
            ]);

            return response()->json([
                'message' => 'Task updated successfully!', 
                'data' => $updatedTask
            ], 200); 
        }
        catch(\Exception $e){
            return \response()->json([
                'error' => 'Task update endpoint error', 
                'message' => $e->getMessage()
            ], 500); 
        }
    }

    /**
     * Remove the specific task.
     */
    public function destroy(Request $request, string $id) {
        
        if(!$id || empty($id)){
            return \response()->json([
                'message' => 'id not found'
            ], 404); 
        }
        
        try{
            $user = $request->user(); 
            $userRole = $user->role; 

            if(!$userRole || $userRole !== 'company'){
                return response()->json([
                    'message' => 'Company not found!'
                ], 404); 
            }
            
            $task = Task::findOrFail($id); 

            if(!$task){
                return \response()->json([
                    'message' => 'Task not found!'
                ], 404); 
            }

            $task->delete(); 

            return \response()->json([
                'message' => 'Task deleted successfully!'
            ], 200); 
        }
        catch(\Exception $e){
            return \response()->json([
                'error' => 'Task destroy endpoint error', 
                'message' => $e->getMessage()
            ], 500); 
        }
    }

    public function autoAssign(Request $request){
        try{

            $taskIds = $request->input('task_ids', []);
            if (empty($taskIds)) {
                return response()->json(['message' => 'No task IDs provided'], 400);
            }

            $assigned = [];

            foreach ($taskIds as $taskId) {
                $task = Task::find($taskId);

                if (!$task || $task->status !== 'pending') {
                    continue;
                }

                $requiredSkills = $task->required_skills ?? [];

                // Fetch available workers with their userSkill relationship
                $availableWorkers = User::where('role', 'worker')
                    ->where('availability_status', 'available')
                    ->with('userSkill')
                    ->get();

                if ($availableWorkers->isEmpty()) {
                    continue;
                }

                // Calculate match scores
                $scoredWorkers = $availableWorkers->map(function ($worker) use ($requiredSkills) {
                    $workerSkills = $worker->userSkill->pluck('name')->toArray() ?? [];

                    $matchedSkills = array_intersect($requiredSkills, $workerSkills);
                    $skillMatchPercent = count($requiredSkills)
                        ? (count($matchedSkills) / count($requiredSkills)) * 100
                        : 0;

                    // Weighted formula (80% skills + 20% rating)
                    $score = (0.8 * $skillMatchPercent) + (0.2 * ($worker->rating * 20));

                    return [
                        'worker' => $worker,
                        'score' => round($score, 2),
                    ];
                });

                // Pick the best-scoring worker
                $best = $scoredWorkers->sortByDesc('score')->first();

                if ($best && $best['score'] > 0) {
                    $task->assigned_worker_id = $best['worker']->id;
                    $task->status = 'assigned';
                    $task->save();

                    $assigned[] = [
                        'task_id' => $task->id,
                        'worker_id' => $best['worker']->id,
                        'match_score' => $best['score'],
                    ];
                }
            }

            return response()->json([
                'message' => 'Auto-assignment completed',
                'assigned' => $assigned,
            ]);
        }
        catch(\Excpetion $e){
            return response()->json([
                'error' => 'autoAssign endpoint error',
                'message' => $e->getMessage()
            ], 500); 
        }
    }
}
