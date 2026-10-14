<?php

namespace App\Actions;

use App\Models\User;
use App\Services\PortfolioPhotoStorage;
use App\Services\ProjectCoverStorage;
use Illuminate\Support\Facades\DB;

class DeleteUserAccount
{
    public function __construct(private ProjectCoverStorage $covers, private PortfolioPhotoStorage $photos) {}

    public function handle(User $user): void
    {
        [$portfolio, $projects] = DB::transaction(function () use ($user) {
            $owner = User::query()->lockForUpdate()->findOrFail($user->id);
            $portfolio = $owner->portfolio()->lockForUpdate()->first();
            $projects = $portfolio?->projects()->lockForUpdate()->get() ?? collect();
            $owner->delete();

            return [$portfolio, $projects];
        });

        // A exclusão em cascata do banco não dispara eventos dos projetos.
        foreach ($projects as $project) {
            $this->covers->delete($project->portfolio_id, $project->cover_image);
        }

        if ($portfolio) {
            $this->photos->delete($portfolio->id, $portfolio->photo_path);
        }
    }
}
