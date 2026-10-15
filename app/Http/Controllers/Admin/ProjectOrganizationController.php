<?php

namespace App\Http\Controllers\Admin;

use App\Actions\OrganizeProjects;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrganizeProjectsRequest;
use App\Support\ProjectOrganization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectOrganizationController extends Controller
{
    public function edit(Request $request, ProjectOrganization $organization): View
    {
        $projects = $request->user()->portfolio->projects()->orderBy('display_order')->orderBy('id')->get();

        return view('admin.projects.organize', ['projects' => $projects, 'revision' => $organization->revision($projects)]);
    }

    public function update(OrganizeProjectsRequest $request, OrganizeProjects $organize): JsonResponse
    {
        $revision = $organize->handle($request->user()->portfolio, $request->validated());

        return response()->json(['message' => 'Ordem e destaques salvos.', 'revision' => $revision]);
    }
}
