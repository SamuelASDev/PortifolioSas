<?php

namespace App\Actions;

use App\Models\User;
use App\Services\ProjectCoverStorage;
use Illuminate\Support\Facades\DB;

class DeleteUserAccount
{
    public function __construct(private ProjectCoverStorage $covers) {}

    public function handle(User $user): void
    {
        $projects = DB::transaction(function () use ($user) {
            $owner = User::query()->lockForUpdate()->findOrFail($user->id);
            $projects = $owner->portfolio?->projects()->lockForUpdate()->get() ?? collect();
            $owner->delete();

            return $projects;
        });

        // A exclusão em cascata do banco não dispara eventos dos projetos.
        foreach ($projects as $project) {
            $this->covers->delete($project->portfolio_id, $project->cover_image);
        }
    }
}
