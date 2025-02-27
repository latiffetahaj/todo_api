<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Requests\StoreOrUpdateTaskRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\TaskResource;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Filters\TaskFilter;
use Illuminate\Database\Eloquent\Builder;


class TaskController extends Controller
{
    /**
     * Get all tasks with optional filtering.
     * @return JsonResponse
     */
    public function index()
    {
        try {
            $query = Task::where('user_id', Auth::id());
            
            $filter = new TaskFilter(request());
            $query = $filter->apply($query);


            return TaskResource::collection($query->get());
        } catch (QueryException $e) {
            Log::error('Could not get tasks: ' . $e->getMessage());

            return response()->json(['error' => 'Could not get tasks'], 500);
        }
        // try {
        //     // Start with a base query for the authenticated user
        //     $query = Task::where('user_id', Auth::id());
            
        //     // Apply name filter if provided
        //     if ($name = request()->query('name')) {
        //         $query->orWhere('name', 'like', '%' . $name . '%');
        //     }
            
        //     // Apply description filter if provided
        //     if ($description = request()->query('description')) {
        //         $query->orWhere('description', 'like', '%' . $description . '%');
        //     }
            
        //     // Apply completion date filter if provided
        //     if ($completedAt = request()->query('completed_at')) {
        //         // For exact date matching
        //         $query->orWhereDate('completed_at', $completedAt);
                

        //     }
                        
        //     // Execute the query and return the results
        //     $tasks = $query->get();
            
        //     return TaskResource::collection($tasks);
            
        // } catch (QueryException $e) {   
        //     Log::error('Could not get tasks: ' . $e->getMessage());
        //     return response()->json(['error' => 'Could not get tasks'], 500);
        // }
    }

    /**
     * Store a new task.
     * @param StoreOrUpdateTaskRequest $request
     * @return JsonResponse
     */
    public function store(StoreOrUpdateTaskRequest $request): JsonResponse
    {   
        
        try {
            Log::info('Creating task for user: ' . Auth::id());
            Log::info($request->validated());
            $new_task = Task::create(array_merge($request->validated(), ['user_id' => Auth::id()]));

            Log::info(array_merge($request->validated(), ['user_id' => Auth::id()]));
            return $this->formatTaskResponse($new_task, 'Task created successfully', 201);

        } catch (\QueryException $e) {
                Log::error('Could not create task: ' . $e->getMessage());

                return response()->json(['error' => 'Could not create task'], 500); 
            }
        }

    /**
     * Display the task by id.
     * @param Task $task
     * @return JsonResponse
     */
    public function show(Task $task): JsonResponse
    {
        try {
            return $this->formatTaskResponse($task, 'Task found successfully', 200);

        }  catch(QueryException $e) {
            Log::error('Could not find task: ' . $e->getMessage());

            return response()->json(['error' => 'Could not find task'], 404);
        }

    }

    /**
     * Update the task by id.
     * @param StoreOrUpdateTaskRequest $request
     * @param Task $task
     * @return JsonResponse
     */
    public function update(StoreOrUpdateTaskRequest $request, Task $task): JsonResponse
    {
        
        try {
            $task = Task::where('user_id', Auth::id())->findOrFail($task->id);  
            $validated = $request->validated();            
            
            // Handle completion status changes
            if ($request->has('is_completed')) {
                if ($request->is_completed && !$task->is_completed) {
                    $validated['completed_at'] = Carbon::now()->toDateTimeString();
                } elseif (!$request->is_completed) {
                    $validated['completed_at'] = null;
                }
            }


            $task->update($validated);


            return $this->formatTaskResponse($task, 'Task updated successfully', 200);

        } catch (\QueryException $e) {
            Log::error('Could not update task: ' . $e->getMessage());

            return response()->json(['error' => 'Could not update task'], 500);
        }
    }

    /**
     * Remove the task by id.
     * @param Task $task
     * @return JsonResponse
     */
    public function destroy(Task $task): JsonResponse
    {
        try {
            $task = Task::where('user_id', Auth::id())->findOrFail($task->id);
            $task->delete();

            return $this->formatTaskResponse($task, 'Task deleted successfully', 200);
        } catch (ModelNotFoundException $e) {
            Log::error('Could not delete task: ' . $e->getMessage());

            return response()->json(['error' => 'Could not delete task'], 404);
        }
        catch (\QueryException $e) {
            Log::error('Could not delete task: ' . $e->getMessage());

            return response()->json(['error' => 'Could not delete task'], 500);
        }
    }


    /**
     * Format the response for a task.
     * @param Task $task
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    private function formatTaskResponse(Task $task, string $message, int $statusCode): JsonResponse
    {
        return (new TaskResource($task))
            ->additional(['message' => $message])
            ->response()
            ->setStatusCode($statusCode);
    }

}
