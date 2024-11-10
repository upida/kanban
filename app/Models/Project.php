<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime:Y-m-d H:i:s',
        'ended_at' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(Status::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    static function setupCreate(array $data): Project
    {
        try {
            DB::beginTransaction();

            $project = Project::create($data);
    
            $project->members()->create([
                'user_id' => auth('web')->user()->id,
                'role' => 'owner',
            ]);
    
            DB::commit();
    
            return $project;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function setupDelete()
    {
        DB::beginTransaction();

        try {
            $this->members()->delete();
            $this->tasks()->delete();
            $this->statuses()->delete();
            $this->notifications()->delete();

            $this->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function owner(): User
    {
        return $this->members()->where('role', 'owner')->first()->user;
    }
}
