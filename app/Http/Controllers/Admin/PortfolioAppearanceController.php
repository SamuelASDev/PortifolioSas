<?php

namespace App\Http\Controllers\Admin;

use App\Actions\SavePortfolioAppearance;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePortfolioAppearanceRequest;
use Illuminate\Http\JsonResponse;

class PortfolioAppearanceController extends Controller
{
    public function update(UpdatePortfolioAppearanceRequest $request, SavePortfolioAppearance $save): JsonResponse
    {
        $save->handle($request->user()->portfolio, $request->validated());

        return response()->json(['message' => 'Aparência salva com sucesso.']);
    }
}
