<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskMember;
use App\Notifications\Notifier;
use Illuminate\Http\Request;

class TaskMemberController extends Controller
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
    public function store(Project $project, Task $task, Member $member)
    {
        $task->members()->create([
            'member_id' => $member->id,
        ]);

        $notification = $project->notifications()->create([
            'user_id' => $member->user->id,
            'task_id' => $task->id,
            'message' => "Task $task->title assigned to {$member->user->name} in project $project->name",
            'read' => false,
        ]);

        $member->user->notify(new Notifier([
            'subject' => "Task $task->title assigned to you in project $project->name",
            'lines' => [
                "Task $task->title assigned to you in project $project->name",
            ],
            'action' => [
                'title' => 'View Notification',
                'url' => route('projects.notifications.show', [
                    'project' => $project->id,
                    'notification' => $notification->id,
                ]),
            ]
        ]));

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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Task $task, TaskMember $taskMember)
    {
        $task_member_user = $taskMember->member->user;

        $taskMember->delete();

        $notification = $project->notifications()->create([
            'user_id' => $task_member_user->id,
            'message' => "{$task_member_user->name} removed from task $task->title in project $project->name",
            'read' => false,
        ]);

        $task_member_user->notify(new Notifier([
            'subject' => "{$task_member_user->name} removed from task $task->title in project $project->name",
            'lines' => [
                "{$task_member_user->name} removed from task $task->title in project $project->name",
            ],
            'action' => [
                'title' => 'View Notification',
                'url' => route('projects.notifications.show', [
                    'project' => $project->id,
                    'notification' => $notification->id,
                ]),
            ]
        ]));

        return redirect()->route('projects.show', $project->id);
    }
}
