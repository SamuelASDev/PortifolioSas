<?php

namespace App\Actions;

use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnsurePortfolio
{
    public function handle(User $user): Portfolio
    {
        if ($portfolio = $user->portfolio()->first()) {
            return $portfolio;
        }

        return DB::transaction(function () use ($user) {
            $owner = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($portfolio = $owner->portfolio()->first()) {
                return $portfolio;
            }

            do {
                $slug = Str::limit(Str::slug($owner->name) ?: 'portfolio', 180, '').'-'.Str::uuid();
            } while (Portfolio::where('slug', $slug)->exists());

            return $owner->portfolio()->create([
                'slug' => $slug, 'theme' => 'dark', 'primary_color' => '#6366f1',
                'professional_name' => $owner->name, 'is_published' => false, 'show_featured_carousel' => false,
            ]);
        });
    }
}
