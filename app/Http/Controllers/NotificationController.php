<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Project;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        $notifications = $project->notifications()->orderBy('created_at', 'desc')->get();

        return inertia('Notifications/Index', [
            'project' => $project,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Notification $notification)
    {
        $notification->read = true;
        $notification->save();

        $notification = $notification->load('project', 'task');

        return inertia('Notifications/Show', [
            'project' => $project,
            'notification' => $notification,
        ]);
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
    public function destroy(string $id)
    {
        //
    }
}
