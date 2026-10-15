<?php

namespace App\Actions;

use App\Models\Portfolio;
use App\Models\Project;
use App\Services\ProjectAttachmentStorage;
use App\Services\ProjectCoverStorage;
use App\Services\SharingImageStorage;
use Illuminate\Support\Facades\DB;

class DeleteProject
{
    public function __construct(private ProjectCoverStorage $covers, private ProjectAttachmentStorage $attachments, private SharingImageStorage $sharing) {}

    public function handle(Project $project): void
    {
        [$deleted, $media] = DB::transaction(function () use ($project) {
            Portfolio::query()->lockForUpdate()->findOrFail($project->portfolio_id);
            $record = Project::query()->lockForUpdate()->findOrFail($project->id);
            $media = $record->attachments()->get();
            $record->delete();

            return [$record, $media];
        });

        $this->covers->delete($deleted->portfolio_id, $deleted->cover_image);
        $this->sharing->delete($deleted->portfolio_id, $deleted->share_image_path);
        foreach ($media as $item) {
            $this->attachments->delete($deleted->portfolio_id, $item->path);
        }
    }
}
