<?php

namespace App\Http\Controllers\Admin;

use App\Actions\SetPortfolioPublication;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePortfolioPublicationRequest;
use Illuminate\Http\RedirectResponse;

class PortfolioPublicationController extends Controller
{
    public function update(UpdatePortfolioPublicationRequest $request, SetPortfolioPublication $publication): RedirectResponse
    {
        $publish = $request->boolean('is_published');
        $publication->handle($request->user()->portfolio, $publish);

        return to_route('admin.portfolio.edit')->with('status', $publish
            ? 'Portfólio publicado! Seu link já pode ser compartilhado.'
            : 'Portfólio despublicado. Suas páginas públicas estão indisponíveis.');
    }
}
