<?php

namespace App\Http\Middleware;

use App\Models\Member;
use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $project = $request->route('project');
        $member = $request->route('member');

        if ($project && !$project instanceof Project) {
            $project = Project::find($project);
        }

        if ($member && !$member instanceof Member) {
            $member = Member::find($member);
        }
        
        $user = auth('web')->user();

        if (in_array('myself', $roles) && $member->user_id === $user->id) {
            return $next($request);
        }

        $member = $project->members()->where('user_id', $user->id);
        if ($roles) {
            $roles_without_myself = array_filter($roles, function ($role) {
                return $role !== 'myself';
            });
            
            if ($roles_without_myself) {
                $member = $member->whereIn('role', $roles_without_myself);
            }
        }

        if ($project && $member->first() === null) {
            return abort(403, 'You are not the owner of this project');
        }

        return $next($request);
    }
}
