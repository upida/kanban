<?php

namespace App\Http\Controllers;

use App\Mail\SendEmail;
use App\Models\Invitation;
use App\Models\Member;
use App\Models\Project;
use App\Models\User;
use App\Notifications\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        $members = $project->members()->with('user')->get();

        $invitations = $project->invitations()->where('status', 'pending')->get();

        $owner = $project->owner();

        return Inertia::render('Members/Index', [
            'project' => $project,
            'owner' => $owner,
            'members' => $members,
            'invitations' => $invitations,
        ]);
    }

    public function invite(Project $project, Request $request)
    {
        DB::beginTransaction();
        try {
            $token = bin2hex(random_bytes(32));

            $invitation = Invitation::create([
                'project_id' => $project->id,
                'email' => $request->input('email'),
                'token' => $token,
                'status' => 'pending',
            ]);

            Mail::to($invitation->email)->send(new SendEmail([
                'subject' => 'Invitation to join ' . $project->name,
                'title' => 'Invitation to join ' . $project->name,
                'body' => 'You have been invited to join ' . $project->name . ' by ' . $project->owner()->name . '. Please click the button below to accept the invitation.',
                'redirect' => route('projects.members.accept', [
                    'project' => $project->id,
                    'token' => $token,
                ]),
            ]));
            
            DB::commit();
            return redirect()->route('projects.members', $project->id);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function accept(Project $project, string $token)
    {
        DB::beginTransaction();

        try {
            $invitation = Invitation::where('project_id', $project->id)
                ->where('token', $token)
                ->where('status', 'pending')
                ->first();
    
            if (!$invitation) {
                return abort(404, 'Invitation not found');
            }
    
            $user = User::where('email', $invitation->email)->first();

            if ($user) {
                $member = Member::where('project_id', $project->id)
                    ->where('user_id', $user->id)
                    ->first();
        
                if ($member) {
                    $invitation->status = 'rejected';
                    $invitation->save();

                    DB::commit();

                    return redirect()->route('projects.show', $project->id);
                }
            }
    
            if (!$user) {
                return redirect()->intended(route('register', absolute: false));
            }
    
            $member = Member::create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'role' => 'member',
            ]);
    
            $invitation->status = 'accepted';
            $invitation->member_id = $member->id;
            $invitation->save();

            $notification = $project->notifications()->create([
                'user_id' => $user->id,
                'message' => "Member $invitation->email accepted to project $project->name",
                'read' => false,
            ]);

            $user->notify(new Notifier([
                'subject' => "You accepted to join project $project->name",
                'lines' => [
                    "You accepted to join project $project->name",
                ],
                'action' => [
                    'title' => 'View Notification',
                    'url' => route('projects.notifications.show', [
                        'project' => $project->id,
                        'notification' => $notification->id,
                    ]),
                ]
            ]));

            Invitation::where('project_id', $project->id)
                ->where('email', $invitation->email)
                ->where('id', '!=', $invitation->id)
                ->update([
                    'status' => 'rejected',
                ]);
            
            DB::commit();
            return redirect()->route('projects.show', $project->id);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
    public function store(Request $request)
    {
        //
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Member $member)
    {
        DB::beginTransaction();

        try {
            $user = auth('web')->user();
            $member_user = $member->user()->first();

            $member->delete();

            $notification = $project->notifications()->create([
                'user_id' => $member_user->id,
                'message' => "$member_user->name leave project $project->name",
                'read' => false,
            ]);

            $member_user->notify(new Notifier([
                'subject' => "You leave project $project->name",
                'lines' => [
                    "You leave project $project->name",
                ],
                'action' => [
                    'title' => 'View All Projects',
                    'url' => route('projects.index'),
                ]
            ]));

            DB::commit();

            if ($user->id === $member_user->id) {
                return redirect()->route('projects.index');
            } else {
                return redirect()->route('projects.members', $project->id);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
