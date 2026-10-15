<?php

namespace App\Services;

use App\Models\Portfolio;
use App\Models\Project;
use App\Models\ProjectMedia;
use Illuminate\Database\Eloquent\Collection;

class ProjectMediaManager
{
    public function available(Portfolio $portfolio, ?Project $project, ?string $token): Collection
    {
        return ProjectMedia::where('portfolio_id', $portfolio->id)
            ->where(function ($query) use ($project, $token) {
                $query->where(function ($attached) use ($project) {
                    $attached->where('project_id', $project?->id ?? 0)->whereNotNull('attached_at');
                });
                if ($token) {
                    $query->orWhere(function ($pending) use ($project, $token) {
                        $pending->whereNull('attached_at')->where('upload_token', $token)
                            ->where(fn ($owner) => $owner->whereNull('project_id')->orWhere('project_id', $project?->id ?? 0));
                    });
                }
            })->get();
    }
}
