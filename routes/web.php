<?php

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemberController;
use App\Http\Middleware\RoleMiddleware;
use App\Mail\SendEmail;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Route::get('/{project}', [DashboardController::class, 'project'])->name('project');
    // Route::get('/{project}/settings', [DashboardController::class, 'project_settings'])->name('project.settings');
    // Route::get('/{project}/analytics', [DashboardController::class, 'project_analytics'])->name('project.analytics');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index'); // view projects.index
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create'); // view projects.create
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');

    Route::middleware([RoleMiddleware::class])->group(function () {
        Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show'); // view projects.show
        Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit'); // view projects.edit
        Route::patch('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

        Route::post('/projects/{project}/statuses', [StatusController::class, 'store'])->name('projects.statuses.store');
        Route::patch('/projects/{project}/statuses/{status}', [StatusController::class, 'update'])->name('projects.statuses.update');
        Route::delete('/projects/{project}/statuses/{status}', [StatusController::class, 'destroy'])->name('projects.statuses.destroy');

        Route::get('/projects/{project}/members', [MemberController::class, 'index'])->name('projects.members');
       
        Route::get('/projects/{project}/analytics', [AnalyticsController::class, 'index'])->name('projects.analytics');
       
        Route::get('/projects/{project}/notifications', [NotificationController::class, 'index'])->name('projects.notifications');
        Route::get('/projects/{project}/notifications/{notification}', [NotificationController::class, 'show'])->name('projects.notifications.show');
        Route::patch('/projects/{project}/notifications/{notification}/read', [NotificationController::class, 'updateRead'])->name('projects.notifications.updateRead');
    
        Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::patch('/projects/{project}/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/projects/{project}/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    });

    Route::post('/projects/{project}/members/invite', [MemberController::class, 'invite'])->name('projects.members.invite')->middleware(RoleMiddleware::class.':owner');
    Route::delete('/projects/{project}/members/{member}', [MemberController::class, 'destroy'])->name('projects.members.destroy')->middleware(RoleMiddleware::class.':owner,myself');
});

Route::get('/projects/{project}/members/accept/{token}', [MemberController::class, 'accept'])->name('projects.members.accept');

require __DIR__.'/auth.php';
