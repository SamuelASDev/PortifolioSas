<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AccountWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_registered_accounts_can_manage_only_their_own_portfolios_and_projects(): void
    {
        $first = $this->registerAccount('Primeira Pessoa', 'first@example.com');
        $this->get(route('admin.portfolio.edit'))->assertOk();
        $this->patch(route('admin.portfolio.update'), [
            'slug' => 'Primeira Pessoa', 'theme' => 'light', 'primary_color' => '#112233',
        ])->assertSessionHasNoErrors();
        $this->post(route('admin.projects.store'), $this->projectData('Projeto da primeira conta'))
            ->assertSessionHasNoErrors();
        $firstProject = $first->portfolio->projects()->sole();
        $this->post(route('logout'))->assertRedirect('/');
        $this->assertGuest();

        $second = $this->registerAccount('Segunda Pessoa', 'second@example.com');
        $this->patch(route('admin.portfolio.update'), [
            'slug' => 'Segunda Pessoa', 'theme' => 'dark', 'primary_color' => '#445566',
        ])->assertSessionHasNoErrors();
        $this->post(route('admin.projects.store'), $this->projectData('Projeto da segunda conta'))
            ->assertSessionHasNoErrors();
        $secondProject = $second->portfolio->projects()->sole();
        $this->get(route('admin.projects.index'))->assertOk()
            ->assertSee($secondProject->title)->assertDontSee($firstProject->title);
        $this->get(route('admin.projects.edit', $firstProject))->assertForbidden();
        $this->post(route('logout'))->assertRedirect('/');

        $this->post(route('login'), ['email' => 'first@example.com', 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($first);
        $this->get(route('admin.projects.index'))->assertOk()
            ->assertSee($firstProject->title)->assertDontSee($secondProject->title);
        $this->put(route('admin.projects.update', $firstProject), $this->projectData('Projeto atualizado'))
            ->assertSessionHasNoErrors();
        $this->delete(route('admin.projects.destroy', $secondProject))->assertForbidden();
        $this->delete(route('admin.projects.destroy', $firstProject))->assertRedirect(route('admin.projects.index'));

        $this->assertModelMissing($firstProject);
        $this->assertModelExists($secondProject);
        $this->assertDatabaseHas('portfolios', ['user_id' => $first->id, 'slug' => 'primeira-pessoa', 'theme' => 'light']);
        $this->assertDatabaseHas('portfolios', ['user_id' => $second->id, 'slug' => 'segunda-pessoa', 'theme' => 'dark']);
    }

    private function registerAccount(string $name, string $email): User
    {
        $this->post(route('register'), [
            'name' => $name, 'email' => $email,
            'password' => 'password', 'password_confirmation' => 'password',
        ])->assertRedirect(route('dashboard'));

        $user = User::where('email', $email)->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->portfolio);

        $this->get(route('admin.portfolio.edit'))->assertRedirect(route('verification.notice'));
        $this->get(URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id, 'hash' => sha1($user->email),
        ]))->assertRedirect();

        return $user->refresh();
    }

    private function projectData(string $title): array
    {
        return ['title' => $title, 'description' => 'Descrição simples', 'display_order' => 0, 'is_featured' => '1'];
    }
}
