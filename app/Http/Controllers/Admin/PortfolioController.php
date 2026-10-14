<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PortfolioController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        
        // Busca o portfólio do usuário. Se não existir, cria um base.
        $portfolio = $user->portfolio()->firstOrCreate(
            [], // Critério de busca (vazio porque a relação já amarra ao user_id)
            [   // Dados de criação padrão
                'slug' => Str::slug($user->name) . '-' . $user->id,
                'theme' => 'dark',
                'primary_color' => '#6366f1'
            ]
        );

        return view('admin.portfolio.edit', compact('portfolio'));
    }

    public function update(Request $request)
    {
        $portfolio = auth()->user()->portfolio;

        $request->validate([
            'slug' => 'required|string|alpha_dash|max:255|unique:portfolios,slug,' . $portfolio->id,
            'theme' => 'required|in:dark,light',
            'primary_color' => 'required|string|max:7',
        ]);

        $portfolio->update([
            'slug' => Str::slug($request->slug),
            'theme' => $request->theme,
            'primary_color' => $request->primary_color,
        ]);

        return back()->with('status', 'Portfólio atualizado com sucesso!');
    }
}