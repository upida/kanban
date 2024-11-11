<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectStoreRequest;
use App\Http\Requests\ProjectUpdateRequest;
use App\Models\Member;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
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
                return $subquery
                ->withCount(['notifications' => fn ($query) => $query->where('read', false)])
                ->withCount('members')->withCount([
                    'tasks as tasks_completed' => function ($query) {
                        return $query->where('done', true);
                    },
                    'tasks'
                ]);
            }
        ])->get();

        foreach ($memberships as $membership) {
            $membership->owner = $membership->project->owner()->toArray();
        }

        $memberships = $memberships->toArray();

        $projects = [];

        foreach ($memberships as $membership) {
            if ($membership['project']['tasks_completed'] === 0 || $membership['project']['tasks_count'] === 0) {
                $membership['project']['progress'] = 0;
            } else {
                $membership['project']['progress'] = round(($membership['project']['tasks_completed'] / $membership['project']['tasks_count']) * 100, 2);
            }
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
    public function show(Project $project, Task $task = null)
    {
        if (!$project instanceof Project) {
            return abort(404, 'Project not found');
        }
                
        $statuses = $project->statuses()->with(['tasks' => function($subquery) {
            return $subquery->with('members');
        }])->withCount([
            'tasks as tasks_completed' => function ($subsubquery) {
                return $subsubquery->where('done', true);
            },
            'tasks'
        ])->get();

        $notifications = $project->notifications()->get();
        $members = $project->members()->with('user')->get();

        $project->loadCount('members');
        $project->loadCount(['notifications' => fn ($query) => $query->where('read', false)]);
        $project->loadCount([
            'tasks as tasks_completed' => function ($query) {
                return $query->where('done', true);
            },
            'tasks'
        ]);

        $project['progress'] = ($project['tasks_completed'] !== 0 && $project['tasks_count'] !== 0) ? round(($project['tasks_completed'] / $project['tasks_count']) * 100, 2) : 0;

        foreach ($statuses as $status) {
            $status->progress = ($status->tasks_completed !== 0 && $status->tasks_count !== 0) ? round(($status->tasks_completed / $status->tasks_count) * 100, 2) : 0;
        }

        $props = [
            'project' => $project,
            'statuses' => $statuses,
            'notifications' => $notifications,
            'members' => $members,
        ];

        if ($task) {
            $props['task'] = $task->load('members');
        }

        return Inertia::render('Projects/Show', $props);
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
