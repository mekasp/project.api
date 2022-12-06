<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * @OA\Post(
     * path="/api/projects",
     * summary="Add projects",
     * description="Add project",
     * operationId="addProject",
     * tags={"projects"},
     * security={ {"bearer": {} }},
     * @OA\RequestBody(
     *    required=true,
     *    description="param project",
     *    @OA\JsonContent(
     *       required={"name"},
     *       @OA\Property(property="name", type="string", example="project1")
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success"
     *    )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'min:4'],
        ]);

        $project = Project::create([
            'name' => $request->name,
            'user_id' => $request->user()->id
        ]);
    }

    /**
     * @OA\Post(
     * path="/api/projects/{id}",
     * summary="Link project to users",
     * description="Link project to users",
     * operationId="syncProject",
     * tags={"projects"},
     * security={ {"bearer": {} }},
     * @OA\Parameter(
     *    description="ID of project",
     *    in="path",
     *    name="id",
     *    required=true,
     *    example="1",
     *    @OA\Schema(
     *       type="integer",
     *       format="int64"
     *    )
     * ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass users id",
     *    @OA\JsonContent(
     *       required={"user_id"},
     *       @OA\Property(property="user_id", type="integer", example="[1, 2, 3]"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    )
     * )
     */
    public function sync(Request $request, $id)
    {
        $project = Project::find($id);
        $project->users()->sync($request->all());
    }

    /**
     * @OA\Get(
     * path="/api/projects",
     * summary="List projects incl. labels",
     * description="List projects incl. labels",
     * operationId="listProjects",
     * tags={"projects"},
     * security={ {"bearer": {} }},
     * @OA\Response(
     *    response=200,
     *    description="Response Json array",
     *    @OA\JsonContent(
     *       @OA\Property(property="id", type="ineger", example="1"),
     *       @OA\Property(property="name", type="string", example="project1"),
     *       @OA\Property(property="user_id", type="ineger", example="1"),
     *       @OA\Property(property="users", type="JSON", example="[1, 2]"),
     *       @OA\Property(property="labels", type="JSON", example="['Label1','Label2']"),
     *    )
     *   )
     * )
     */
    public function show(Request $request)
    {
        $project = Project::query();

        $project->select('projects.*')
            ->leftJoin('project_user', 'projects.id', '=', 'project_user.project_id')
            ->leftJoin('users', 'project_user.user_id', '=', 'users.id')
            ->where(function ($query) use ($request) {
                $query->where('projects.user_id', $request->user()->id)
                    ->orWhere('users.id', $request->user()->id);
            });

        if (!empty($request->input('filter.user.email'))) {
            $project->where('users.email', $request->input('filter.user.email'));
        }

        if (!empty($request->input('filter.user.continent'))) {
            $project->join('countries', 'countries.id', '=', 'users.country_id')
                ->join('continents', 'continents.id', '=', 'countries.continent_id')
                ->where('continents.code', $request->input('filter.user.continent'));
        }

        if (!empty($request->input('filter.labels'))) {
            $project->join('label_project', 'projects.id', '=', 'label_project.project_id')
                ->join('labels', 'labels.id', '=', 'label_project.label_id')
                ->whereIn('labels.name', explode(',', $request->input('filter.labels')));
        }
        return ProjectResource::collection($project->groupBy('projects.id')->get());
    }

    /**
     * @OA\Delete(
     * path="/api/projects/{project}",
     * summary="Delete project",
     * description="Delete project",
     * operationId="deleteProject",
     * tags={"projects"},
     * security={ {"bearer": {} }},
     *
     * @OA\Parameter(
     *    description="ID of project",
     *    in="path",
     *    name="project",
     *    required=true,
     *    example="1",
     *      @OA\Schema(
     *          type="integer",
     *          format="int64"
     *      )
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *  )
     * )
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->users()->detach();
        $project->labels()->detach();
        $project->delete();
    }
}

