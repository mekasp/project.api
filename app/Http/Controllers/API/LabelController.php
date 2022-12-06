<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\LabelResource;
use App\Models\Label;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'min:4'],
        ]);

        $label = Label::create([
            'name' => $request->name,
            'user_id' => $request->user()->id
        ]);
    }

    public function sync(Request $request, $id)
    {
        $label = Label::find($id);
        $label->projects()->sync($request->all());
    }

    public function show(Request $request)
    {
        $label = Label::query();

        $label->select('labels.*')
            ->leftJoin('label_project', 'labels.id', '=', 'label_project.label_id')
            ->leftJoin('project_user', 'label_project.project_id', '=', 'project_user.project_id')
            ->leftJoin('projects', 'label_project.project_id', '=', 'projects.id')
            ->where(function ($query) use ($request) {
                $query->orWhere('labels.user_id', $request->user()->id);
                $query->orWhere('projects.user_id', $request->user()->id);
                $query->orwhere('project_user.user_id', $request->user()->id);
            });

        if (!empty($request->input('filter.user.email'))) {
            $label->join('users', 'labels.user_id', '=', 'users.id')
                ->where('users.email', $request->input('filter.user.email'));
        }

        if (!empty($request->input('filter.projects'))) {
            $label->whereIn('projects.name', explode(',', $request->input('filter.projects')));
        }

        return LabelResource::collection($label->groupBy('id')->get());
    }

    public function delete(Label $label)
    {
        $this->authorize('delete', $label);

        $label->projects()->detach();
        $label->delete();
    }
}

