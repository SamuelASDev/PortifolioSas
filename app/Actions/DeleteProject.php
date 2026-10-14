<?php

namespace App\Actions;

use App\Models\Project;
use App\Services\ProjectCoverStorage;
use Illuminate\Support\Facades\DB;

class DeleteProject
{
    public function __construct(private ProjectCoverStorage $covers) {}

    public function handle(Project $project): void
    {
        $deleted = DB::transaction(function () use ($project) {
            $record = Project::query()->lockForUpdate()->findOrFail($project->id);
            $record->delete();

            return $record;
        });

        $this->covers->delete($deleted->portfolio_id, $deleted->cover_image);
    }
}
