<?php

namespace App\Actions;

use App\Models\Portfolio;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SetPortfolioPublication
{
    public function handle(Portfolio $portfolio, bool $publish): void
    {
        DB::transaction(function () use ($portfolio, $publish) {
            $record = Portfolio::where('user_id', $portfolio->user_id)->lockForUpdate()->findOrFail($portfolio->id);

            if ($publish && blank($record->professional_name)) {
                throw ValidationException::withMessages(['is_published' => 'Salve um nome profissional antes de publicar.']);
            }

            $record->update(['is_published' => $publish]);
        });
    }
}
