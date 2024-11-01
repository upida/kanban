<?php

namespace App\Http\Controllers;

use App\Http\Requests\StatusStoreRequest;
use App\Http\Requests\StatusUpdateRequest;
use App\Models\Status;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $statuses = Status::with('project')->get();
        return Inertia::render('Statuses/Index', [
            'statuses' => $statuses,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StatusStoreRequest $request)
    {
        $status = Status::create($request->validated());

        return redirect()->route('projects.show', $status->project_id);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StatusUpdateRequest $request, Status $status)
    {
        if (!$status instanceof Status) {
            return abort(404, 'Status not found');
        }

        $status->update($request->validated());

        return redirect()->route('projects.show', $status->project_id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Status $status)
    {
        if (!$status instanceof Status) {
            return abort(404, 'Status not found');
        }

        $project = $status->project;
        
        $status->delete();

        return redirect()->route('projects.show', $project->id);
    }
}
