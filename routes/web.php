<?php

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TaskController;
use Illuminate\Foundation\Application;
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

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/statuses', [StatusController::class, 'index'])->name('statuses.index');
    Route::post('/statuses', [StatusController::class, 'store'])->name('statuses.store');
    Route::patch('/statuses/{status}', [StatusController::class, 'update'])->name('statuses.update');
    Route::delete('/statuses/{status}', [StatusController::class, 'destroy'])->name('statuses.destroy');

    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index'); // view projects.index
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create'); // view projects.create
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show'); // view projects.show
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit'); // view projects.edit
    Route::patch('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/projects/{project}/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/projects/{project}/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    Route::get('/projects/{project}/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/projects/{project}/notifications/{notification}/read', [NotificationController::class, 'updateRead'])->name('notifications.updateRead');
});

require __DIR__.'/auth.php';
