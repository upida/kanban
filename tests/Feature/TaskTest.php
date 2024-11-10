<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private object $project;
    private object $status;
    private object $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->actingAs($this->user);

        $this->project = Project::factory()->create();
        $this->status = Status::factory()->for($this->project)->create();

        $this->task = Task::factory()->for($this->project)->for($this->status)->create();
    }

    public function test_create_new_task(): void
    {
        $response = $this->post("/projects/{$this->project->id}/tasks", [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'deadline' => '2024-10-26 10:00:00',
            'done' => false,
            'status_id' => $this->status->id,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect("/projects/{$this->project->id}");
    }

    public function test_update_task(): void
    {
        $response = $this->patch("/projects/{$this->project->id}/tasks/{$this->task->id}", [
            'title' => 'Test Task Edit',
            'description' => 'Test Description Edit',
            'deadline' => '2024-10-26 10:00:00',
            'done' => false,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect("/projects/{$this->project->id}");
    }

    public function test_delete_task(): void
    {
        $response = $this->delete("/projects/{$this->project->id}/tasks/{$this->task->id}");

        $response->assertSessionHasNoErrors()->assertRedirect("/projects/{$this->project->id}");
    }
}
