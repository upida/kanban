<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index()
    {
        $projects = Project::join('members', 'projects.id', '=', 'members.project_id')->with(['statuses', 'statuses.tasks'])->withCount([
            'tasks as tasks_completed' => function ($query) {
                return $query->where('done', true);
            },
            'tasks'
        ])->where(function ($query) {
            $query->where('user_id', auth('web')->user()->id)
            ->orWhere('members.user_id', auth('web')->user()->id);
        })->get();

        $total_projects = $projects->count();

        $total_statuses = $projects->sum(function ($project) {
            return $project->statuses()->count();
        });

        $total_tasks = $projects->sum(function ($project) {
            return $project->tasks()->count();
        });

        $total_members = $projects->sum(function ($project) {
            return $project->members()->count();
        });

        $projects = $projects->sortBy('started_at')->toArray();

        foreach ($projects as $key => $project) {
            if ($project['tasks_completed'] === 0 || $project['tasks_count'] === 0) {
                $projects[$key]['progress'] = 0;
            } else {
                $projects[$key]['progress'] = round(($project['tasks_completed'] / $project['tasks_count']) * 100, 2);
            }
        }

        return inertia('Analytics/Index', [
            'total_projects' => $total_projects,
            'total_statuses' => $total_statuses,
            'total_tasks' => $total_tasks,
            'total_members' => $total_members,
            'projects' => array_values($projects),
        ]);
    }
}
