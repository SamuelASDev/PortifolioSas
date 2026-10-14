<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Importação dos nossos Controladores modulares
use App\Http\Controllers\Admin\PortfolioController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\PublicPortfolioController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- PAINEL DE ADMINISTRAÇÃO (SaaS) ---
    Route::prefix('admin')->name('admin.')->group(function () {
        
        // Configurações do Portfólio (Slug, Cores, Tema)
        Route::get('/portfolio', [PortfolioController::class, 'edit'])->name('portfolio.edit');
        Route::post('/portfolio', [PortfolioController::class, 'update'])->name('portfolio.update');

        // CRUD de Projetos
        Route::resource('projects', ProjectController::class);
    });
});

require __DIR__.'/auth.php';

// --- VITRINE PÚBLICA (Deve ficar sempre no final) ---
Route::get('/{slug}', [PublicPortfolioController::class, 'show'])->name('portfolio.show');