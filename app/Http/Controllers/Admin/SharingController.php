<?php

namespace App\Http\Controllers\Admin;

use App\Actions\SaveSharing;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSharingRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;

class SharingController extends Controller
{
    public function portfolio(UpdateSharingRequest $request, SaveSharing $save): RedirectResponse
    {
        return $this->save($request, $save);
    }

    public function project(UpdateSharingRequest $request, Project $project, SaveSharing $save): RedirectResponse
    {
        return $this->save($request, $save, $project);
    }

    private function save(UpdateSharingRequest $request, SaveSharing $save, ?Project $project = null): RedirectResponse
    {
        $save->handle($request->user()->portfolio, $request->safe()->only(['share_title', 'share_description']), $project,
            $request->file('share_image'), $request->boolean('remove_share_image'));

        return back()->with('status', 'Configurações de compartilhamento salvas.');
    }
}
