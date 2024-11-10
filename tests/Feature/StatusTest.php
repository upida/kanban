<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class StatusTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private object $project;
    private object $status;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->actingAs($this->user);

        $this->project = Project::factory()->create();

        $this->status = Status::factory()->for($this->project)->create();
    }

    public function test_index_statuses(): void
    {
        $response = $this->get('/projects/' . $this->project->id . '/statuses')->assertInertia(fn (AssertableInertia $page) =>
            $page->component('Statuses/Index')
            ->has('statuses', fn (AssertableInertia $statuses) =>
                $statuses->where('0.id', $this->status->id)
                ->where('0.name', $this->status->name)
                ->where('0.created_at', Carbon::parse($this->status->created_at)->format('Y-m-d H:i:s'))
                ->where('0.updated_at', Carbon::parse($this->status->updated_at)->format('Y-m-d H:i:s'))
                ->has('0.project', fn (AssertableInertia $project) =>
                    $project->where('id', $this->project->id)
                    ->where('name', $this->project->name)
                    ->where('description', $this->project->description)
                    ->where('started_at', Carbon::parse($this->project->started_at)->format('Y-m-d H:i:s'))
                    ->where('ended_at', Carbon::parse($this->project->ended_at)->format('Y-m-d H:i:s'))
                    ->where('created_at', Carbon::parse($this->project->created_at)->format('Y-m-d H:i:s'))
                    ->where('updated_at', Carbon::parse($this->project->updated_at)->format('Y-m-d H:i:s'))
                )
            )
        );
    }

    public function test_store_status(): void
    {
        $response = $this->post('/projects/' . $this->project->id . '/statuses', [
            'project_id' => $this->project->id,
            'name' => 'Test Status',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/projects/' . $this->project->id);
    }

    public function test_update_status(): void
    {
        $response = $this->patch("/projects/{$this->project->id}/statuses/{$this->status->id}", [
            'name' => 'Test Status Edit'
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/projects/' . $this->project->id);
    }

    public function test_delete_status(): void
    {
        $response = $this->delete("/projects/{$this->project->id}/statuses/{$this->status->id}");

        $response->assertSessionHasNoErrors()->assertRedirect('/projects/' . $this->project->id);
    }
}
