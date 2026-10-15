<?php

use App\Http\Controllers\Admin\PortfolioAppearanceController;
use App\Http\Controllers\Admin\PortfolioController;
use App\Http\Controllers\Admin\PortfolioPreviewController;
use App\Http\Controllers\Admin\PortfolioPublicationController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ProjectMediaController;
use App\Http\Controllers\Admin\ProjectOrganizationController;
use App\Http\Controllers\Admin\SharingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicPortfolioController;
use App\Http\Middleware\EnsureUserHasPortfolio;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified', EnsureUserHasPortfolio::class])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- PAINEL DE ADMINISTRAÇÃO (SaaS) ---
    Route::prefix('admin')->name('admin.')->middleware(['verified', EnsureUserHasPortfolio::class])->group(function () {

        // Configurações do Portfólio (Slug, Cores, Tema)
        Route::get('/portfolio', [PortfolioController::class, 'edit'])->name('portfolio.edit');
        Route::get('/portfolio/preview', [PortfolioPreviewController::class, 'show'])->name('portfolio.preview');
        Route::get('/portfolio/preview/projects/{project}', [PortfolioPreviewController::class, 'project'])->whereNumber('project')->name('portfolio.preview.project');
        Route::patch('/portfolio/appearance', [PortfolioAppearanceController::class, 'update'])->name('portfolio.appearance.update');
        Route::patch('/portfolio/sharing', [SharingController::class, 'portfolio'])->name('portfolio.sharing.update');
        Route::patch('/portfolio', [PortfolioController::class, 'update'])->name('portfolio.update');
        Route::patch('/portfolio/publication', [PortfolioPublicationController::class, 'update'])->name('portfolio.publication.update');

        // CRUD de Projetos
        Route::get('projects/organize', [ProjectOrganizationController::class, 'edit'])->name('projects.organize');
        Route::patch('projects/organize', [ProjectOrganizationController::class, 'update'])->name('projects.organize.update');
        Route::patch('projects/{project}/sharing', [SharingController::class, 'project'])->whereNumber('project')->name('projects.sharing.update');
        Route::post('project-media', [ProjectMediaController::class, 'store'])->middleware('throttle:30,1')->name('project-media.store');
        Route::post('project-media/video', [ProjectMediaController::class, 'video'])->middleware('throttle:30,1')->name('project-media.video');
        Route::resource('projects', ProjectController::class)->except('show');
    });
});

require __DIR__.'/auth.php';

// --- VITRINE PÚBLICA (Deve ficar sempre no final) ---
Route::get('/{slug}/projects/{project}', [PublicPortfolioController::class, 'project'])->whereNumber('project')->name('portfolio.projects.show');
Route::get('/{slug}', [PublicPortfolioController::class, 'show'])->name('portfolio.show');
