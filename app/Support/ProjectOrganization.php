<?php

namespace App\Support;

use Illuminate\Support\Collection;

class ProjectOrganization
{
    public function revision(Collection $projects): string
    {
        return hash('sha256', $projects->sortBy('id')->map(fn ($project) => [
            $project->id, $project->display_order, $project->is_featured, (string) $project->updated_at,
        ])->values()->toJson());
    }
}
