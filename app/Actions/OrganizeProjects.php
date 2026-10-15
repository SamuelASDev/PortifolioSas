<?php

namespace App\Actions;

use App\Models\Portfolio;
use App\Support\ProjectOrganization;
use Illuminate\Support\Facades\DB;

class OrganizeProjects
{
    public function __construct(private ProjectOrganization $organization) {}

    public function handle(Portfolio $portfolio, array $data): string
    {
        return DB::transaction(function () use ($portfolio, $data) {
            Portfolio::where('user_id', $portfolio->user_id)->lockForUpdate()->findOrFail($portfolio->id);
            $projects = $portfolio->projects()->lockForUpdate()->get();
            $ids = array_map(fn ($item) => (int) $item['id'], $data['projects']);
            $expected = $projects->modelKeys();
            sort($ids);
            sort($expected);
            abort_unless($ids === $expected && hash_equals($this->organization->revision($projects), $data['revision']), 409,
                'A lista mudou em outra tela. Recarregue a página antes de organizar novamente.');
            $byId = $projects->keyBy('id');
            foreach ($data['projects'] as $position => $item) {
                $byId->get($item['id'])->fill(['display_order' => $position, 'is_featured' => (bool) $item['is_featured']])->save();
            }

            return $this->organization->revision($projects);
        });
    }
}
