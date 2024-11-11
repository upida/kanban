<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskStoreRequest;
use App\Http\Requests\TaskUpdateRequest;
use App\Models\Project;
use App\Models\Task;
use App\Notifications\Notifier;
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

        $notification = $project->notifications()->create([
            'user_id' => $project->owner()->id,
            'task_id' => $task->id,
            'message' => "Task $task->title created in project $project->name",
            'read' => false,
        ]);

        $project->members()->get()->each(function ($member) use ($project,$task, $notification) {
            $member->user->notify(new Notifier([
                'subject' => "Task $task->title created in project $project->name",
                'lines' => [
                    "Task $task->title created in project $project->name",
                ],
                'action' => [
                    'title' => 'View Notification',
                    'url' => route('projects.notifications.show', [
                        'project' => $project->id,
                        'notification' => $notification->id,
                    ]),
                ]
            ]));
        });

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

        $members = $task->members()->get();

        $request_members = $request->members ?? [];

        usort($request_members, function ($a, $b) {
            return $a - $b;
        });
        
        $current_members = array_column($members->toArray() ?? [], 'member_id');
        usort($current_members, function ($a, $b) {
            return $a - $b;
        });

        if ($request_members != $current_members) {
            $new_members = array_map(function ($member) {
                return [
                    'member_id' => $member
                ];
            }, $request->members);
            
            $task->members()->delete();
            $task->members()->createMany($new_members);
        }

        $updated = $request->validated();
        unset($updated['members']);

        $task->update($updated);

        $notification = $project->notifications()->create([
            'user_id' => $project->owner()->id,
            'task_id' => $task->id,
            'message' => "Task $task->title updated in project $project->name",
            'read' => false,
        ]);

        $project->members()->get()->each(function ($member) use ($project, $task, $notification) {
            $member->user->notify(new Notifier([
                'subject' => "Task $task->title updated in project $project->name",
                'lines' => [
                    "Task $task->title updated in project $project->name",
                ],
                'action' => [
                    'title' => 'View Notification',
                    'url' => route('projects.notifications.show', [
                        'project' => $project->id,
                        'notification' => $notification->id,
                    ]),
                ]
            ]));
        });

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

        $notification = $project->notifications()->create([
            'user_id' => $project->owner()->id,
            'message' => "Task $task->title deleted from project $project->name",
            'read' => false,
        ]);

        $project->members()->get()->each(function ($member) use ($project, $task, $notification) {
            $member->user->notify(new Notifier([
                'subject' => "Task $task->title deleted from project $project->name",
                'lines' => [
                    "Task $task->title deleted from project $project->name",
                ],
                'action' => [
                    'title' => 'View Notification',
                    'url' => route('projects.notifications.show', [
                        'project' => $project->id,
                        'notification' => $notification->id,
                    ]),
                ]
            ]));
        });

        return redirect()->route('projects.show', $project->id);
    }
}
