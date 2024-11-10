<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private object $project;
    private object $status;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->actingAs($this->user);

        $this->project = Project::factory()->create();
        $this->status = Status::factory()->for($this->project)->create();
    }

    public function test_create_new_project(): void
    {
        $response = $this->post('/projects', [
            'name' => 'Test Project',
            'description' => 'Test Description',
            'started_at' => '2024-10-26 10:00:00',
            'ended_at' => '2024-10-26 10:00:00',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/projects');
    }

    public function test_edit_project(): void
    {
        $response = $this->patch("/projects/{$this->project->id}", [
            'name' => 'Test Project Edit'
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/projects');
    }

    public function test_delete_project(): void
    {
        $response = $this->delete("/projects/{$this->project->id}");

        $response->assertSessionHasNoErrors()->assertRedirect('/projects');
    }

    public function test_show_project(): void
    {
        // create a task for the project
        $task = Task::factory()
        ->for($this->project)
        ->for($this->status)
        ->create([
            'title' => 'Test Task',
            'description' => 'Test Description',
            'deadline' => '2024-10-26 10:00:00',
            'done' => false,
        ]);

        // create a notification for the project
        $notification = Notification::factory()->for($this->user)->for($this->project)
        ->create([
            'message' => 'Test Notification',
            'read' => false,
        ]);

        $this->get("/projects/{$this->project->id}")
        ->assertInertia(fn (AssertableInertia $page) => 
            $page->component('Projects/Show')
            ->has('project', fn (AssertableInertia $project) => 
                $project
                ->where('id', $this->project->id)
                ->where('name', $this->project->name)
                ->where('description', $this->project->description)
                ->where('started_at', Carbon::parse($this->project->started_at)->format('Y-m-d H:i:s'))
                ->where('ended_at', Carbon::parse($this->project->ended_at)->format('Y-m-d H:i:s'))
                ->where('created_at', Carbon::parse($this->project->created_at)->format('Y-m-d H:i:s'))
                ->where('updated_at', Carbon::parse($this->project->updated_at)->format('Y-m-d H:i:s'))
            )
            ->has('statuses', fn (AssertableInertia $statuses) => 
                $statuses->where('0.id', $this->status->id)
                ->where('0.name', $this->status->name)
                ->where('0.created_at', Carbon::parse($this->status->created_at)->format('Y-m-d H:i:s'))
                ->where('0.updated_at', Carbon::parse($this->status->updated_at)->format('Y-m-d H:i:s'))
                ->has('0.tasks', fn (AssertableInertia $tasks) => 
                    $tasks->where('0.id', $task->id)
                    ->where('0.title', $task->title)
                    ->where('0.description', $task->description)
                    ->where('0.deadline', Carbon::parse($task->deadline)->format('Y-m-d H:i:s'))
                    ->where('0.done', $task->done ? 1 : 0)
                    ->where('0.created_at', Carbon::parse($task->created_at)->format('Y-m-d H:i:s'))
                    ->where('0.updated_at', Carbon::parse($task->updated_at)->format('Y-m-d H:i:s'))
                )
            )
            ->has('notifications', fn (AssertableInertia $notifications) => 
                $notifications->where('0.id', $notification->id)
                ->where('0.message', $notification->message)
                ->where('0.read', $notification->read ? 1 : 0)
                ->where('0.created_at', Carbon::parse($notification->created_at)->format('Y-m-d H:i:s'))
                ->where('0.updated_at', Carbon::parse($notification->updated_at)->format('Y-m-d H:i:s'))
            )
        );
    }

    public function test_show_all_projects(): void
    {
        $task = Task::factory()->for($this->project)->for($this->status)
        ->create([
            'status_id' => Status::factory()->for($this->project)->create()->id,
            'title' => 'Test Task',
            'description' => 'Test Description',
            'deadline' => '2024-10-26 10:00:00',
            'done' => false,
        ]);

        $notification = Notification::factory()->for($this->user)->for($this->project)
        ->create([
            'message' => 'Test Notification',
            'read' => false,
        ]);

        $this->get('/projects')
        ->assertInertia(fn (AssertableInertia $page) =>
            $page->component('Projects/Index')
            ->has('projects', fn (AssertableInertia $projects) =>
                $projects->where('0.id', $this->project->id)
                ->where('0.name', $this->project->name)
                ->where('0.description', $this->project->description)
                ->where('0.started_at', Carbon::parse($this->project->started_at)->format('Y-m-d H:i:s'))
                ->where('0.ended_at', Carbon::parse($this->project->ended_at)->format('Y-m-d H:i:s'))
                ->where('0.created_at', Carbon::parse($this->project->created_at)->format('Y-m-d H:i:s'))
                ->where('0.updated_at', Carbon::parse($this->project->updated_at)->format('Y-m-d H:i:s'))
                ->has('0.notifications', fn (AssertableInertia $notifications) =>
                    $notifications->where('0.id', $notification->id)
                    ->where('0.message', $notification->message)
                    ->where('0.read', $notification->read ? 1 : 0)
                    ->where('0.created_at', Carbon::parse($notification->created_at)->format('Y-m-d H:i:s'))
                    ->where('0.updated_at', Carbon::parse($notification->updated_at)->format('Y-m-d H:i:s'))
                )
            )
        );
    }
}
