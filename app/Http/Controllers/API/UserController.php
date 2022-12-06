<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\UserResource;
use App\Mail\VerificationMail;
use App\Models\Label;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Psy\Exception\ErrorException;

class UserController
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'min:4'],
            'email' => ['required', 'email']
        ]);
        $token = Str::random(32);
        $user = User::create($request->all() + ['token' => Hash::make($token)]);

        Mail::to($user->email)->send(new VerificationMail($user, $token));
        return 'your token: ' . $token;
    }

    public function verify($id)
    {
        $user = User::find($id);

        if ($user->verified_at !== null) {
            return new ErrorException('already verified');
        }

        if (!\request()->has('token') || !Hash::check(\request()->get('token'), $user->token)) {
            return new ErrorException('token error');
        }

        $user->verified_at = now();
        $user->save();
    }

    public function show(Request $request)
    {
        $user = User::query();

        if (!empty($request->input('filter.name'))) {
            $user->where('name', 'like', '%'. $request->input('filter.name') . '%');
        }
        if (!empty($request->input('filter.email'))) {
            $user->where('email', $request->input('filter.email'));
        }
        if (!empty($request->input('filter.verified'))) {
            $user->where('verified_at', 'like', '%' . $request->input('filter.verified') . '%');
        }
        if (!empty($request->input('filter.country'))) {
            $user->where('country_id', $request->input('filter.country'));
        }

        return UserResource::collection($user->get());
    }

    public function update(Request $request)
    {
        $user = User::find($request->user()->id);
        $user->update($request->all());
    }

    public function destroy(Request $request)
    {
        $user = User::find($request->user()->id);
        $labels = Label::where('user_id', $user['id'])->get();
        foreach ($labels as $label) {
            $label->projects()->detach();
            $label->delete();
        }

        $projects = Project::where('user_id', $user['id'])->get();
        foreach ($projects as $project) {
            $project->users()->detach();
            $project->labels()->detach();
            $project->delete();
        }

        $user->projects()->detach();
        $user->delete();
    }
}

