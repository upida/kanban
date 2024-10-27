<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskStoreRequest;
use App\Http\Requests\TaskUpdateRequest;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaskStoreRequest $request, Project $project)
    {
        if (!$project instanceof Project) {
            return abort(404, 'Project not found');
        }
        
        $task = Task::create($request->validated());

        return redirect()->route('projects.show', $task->project_id);
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
    public function update(TaskUpdateRequest $request, Project $project, Task $task)
    {
        if (!$project instanceof Project) {
            return abort(404, 'Project not found');
        }

        if (!$task instanceof Task) {
            return abort(404, 'Task not found');
        }
        
        $task->update($request->validated());

        return redirect()->route('projects.show', $task->project_id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Task $task)
    {
        if (!$project instanceof Project) {
            return abort(404, 'Project not found');
        }

        if (!$task instanceof Task) {
            return abort(404, 'Task not found');
        }

        $task->delete();

        return redirect()->route('projects.show', $project->id);
    }
}
