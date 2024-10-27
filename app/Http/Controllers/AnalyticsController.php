<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index()
    {
        // Get all projects with their related statuses and tasks
        $projects = Project::with(['statuses', 'tasks'])->get();

        // Count total projects
        $projectsCount = $projects->count();

        // Count total statuses across all projects
        $statusesCount = $projects->sum(function ($project) {
            return $project->statuses->count();
        });

        // Count total tasks across all projects
        $tasksCount = $projects->sum(function ($project) {
            return $project->tasks->count();
        });

        return inertia('Analytics/Index', [
            'projectsCount' => $projectsCount,
            'statusesCount' => $statusesCount,
            'tasksCount' => $tasksCount,
            'projects' => $projects, // Pass projects to the view for chart data
        ]);
    }
}
