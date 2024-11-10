<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectStoreRequest;
use App\Http\Requests\ProjectUpdateRequest;
use App\Models\Member;
use App\Models\Project;
use App\Models\Status;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $memberships = Member::joined()->with([
            'project' => function ($subquery) {
                return $subquery->withCount('notifications')->withCount('members');
            }
        ])->get();

        foreach ($memberships as $membership) {
            $membership->owner = $membership->project->owner()->toArray();
        }

        $memberships = $memberships->toArray();

        $projects = [];

        foreach ($memberships as $membership) {
            $projects[] = array_merge(
                $membership['project'],
                [
                    'owner' => $membership['owner'],
                ]
            );
        }

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Projects/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProjectStoreRequest $request)
    {
        $project = Project::setupCreate($request->validated());

        return redirect()->route('projects.index')->with('message', "$project->name created successfully")->with('project', $project);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        if (!$project instanceof Project) {
            return abort(404, 'Project not found');
        }
                
        $statuses = $project->statuses()->with('tasks')->get();
        $notifications = $project->notifications()->get();

        $project = $project->withCount('members');
        $project = $project->withCount('notifications');
        
        $project = $project->first();

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'statuses' => $statuses,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project|int $project)
    {
        if (!$project instanceof Project) {
            return abort(404, 'Project not found');
        }

        return Inertia::render('Projects/Edit', [
            'project' => $project,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, ProjectUpdateRequest $request)
    {
        if (!$project instanceof Project) {
            return abort(404, 'Project not found');
        }

        $project->update($request->validated());

        return redirect()->route('projects.index')->with('message', "$project->name updated successfully")->with('project', $project);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        if (!$project instanceof Project) {
            return abort(404, 'Project not found');
        }

        $project_name = $project->name;

        $project->setupDelete();

        return redirect()->route('projects.index')->with('message', "$project_name deleted successfully");
    }
}
