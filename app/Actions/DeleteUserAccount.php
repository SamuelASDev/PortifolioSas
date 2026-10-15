<?php

namespace App\Actions;

use App\Models\ProjectMedia;
use App\Models\User;
use App\Services\PortfolioPhotoStorage;
use App\Services\ProjectAttachmentStorage;
use App\Services\ProjectCoverStorage;
use Illuminate\Support\Facades\DB;

class DeleteUserAccount
{
    public function __construct(private ProjectCoverStorage $covers, private PortfolioPhotoStorage $photos, private ProjectAttachmentStorage $attachments) {}

    public function handle(User $user): void
    {
        [$portfolio, $projects, $media] = DB::transaction(function () use ($user) {
            $owner = User::query()->lockForUpdate()->findOrFail($user->id);
            $portfolio = $owner->portfolio()->lockForUpdate()->first();
            $projects = $portfolio?->projects()->lockForUpdate()->get() ?? collect();
            $media = $portfolio ? ProjectMedia::where('portfolio_id', $portfolio->id)->get() : collect();
            $owner->delete();

            return [$portfolio, $projects, $media];
        });

        // A exclusão em cascata do banco não dispara eventos dos projetos.
        foreach ($projects as $project) {
            $this->covers->delete($project->portfolio_id, $project->cover_image);
        }

        if ($portfolio) {
            $this->photos->delete($portfolio->id, $portfolio->photo_path);
            foreach ($media as $item) {
                $this->attachments->delete($portfolio->id, $item->path);
            }
        }
    }
}
