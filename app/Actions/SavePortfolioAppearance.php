<?php

namespace App\Actions;

use App\Models\Portfolio;
use Illuminate\Support\Facades\DB;

class SavePortfolioAppearance
{
    public function handle(Portfolio $portfolio, array $attributes): Portfolio
    {
        return DB::transaction(function () use ($portfolio, $attributes) {
            $record = Portfolio::where('user_id', $portfolio->user_id)->lockForUpdate()->findOrFail($portfolio->id);
            $appearance = $attributes['appearance'];
            $appearance['base_size'] = (int) $appearance['base_size'];
            $appearance['title_scale'] = (int) $appearance['title_scale'];
            $record->update([
                'theme' => $attributes['theme'], 'primary_color' => $attributes['primary_color'],
                'appearance' => $appearance,
            ]);

            return $record;
        });
    }
}
