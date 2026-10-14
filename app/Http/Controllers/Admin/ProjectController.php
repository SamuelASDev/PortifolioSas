<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Project::class);
        $projects = $request->user()->portfolio->projects()
            ->orderBy('display_order')->orderBy('id')->paginate(10);

        return view('admin.projects.index', compact('projects'));
    }

    public function create(): View
    {
        Gate::authorize('create', Project::class);

        return view('admin.projects.create', ['project' => new Project(['display_order' => 0, 'is_featured' => false])]);
    }

    public function store(SaveProjectRequest $request): RedirectResponse
    {
        $request->user()->portfolio->projects()->create($request->validated());

        return to_route('admin.projects.index')->with('status', 'Projeto criado com sucesso!');
    }

    public function edit(Project $project): View
    {
        Gate::authorize('update', $project);

        return view('admin.projects.edit', compact('project'));
    }

    public function update(SaveProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated());

        return to_route('admin.projects.index')->with('status', 'Projeto atualizado com sucesso!');
    }

    public function destroy(Project $project): RedirectResponse
    {
        Gate::authorize('delete', $project);
        $project->delete();

        return to_route('admin.projects.index')->with('status', 'Projeto excluído com sucesso!');
    }
}
