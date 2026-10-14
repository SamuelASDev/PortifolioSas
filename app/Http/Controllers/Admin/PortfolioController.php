<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePortfolioRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.portfolio.edit', ['portfolio' => $request->user()->portfolio]);
    }

    public function update(UpdatePortfolioRequest $request): RedirectResponse
    {
        $request->user()->portfolio->update($request->validated());

        return back()->with('status', 'Portfólio atualizado com sucesso!');
    }
}
