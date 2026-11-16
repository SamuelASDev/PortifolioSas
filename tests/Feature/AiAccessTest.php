<?php

namespace Tests\Feature;

use App\Actions\EnsurePortfolio;
use App\Mcp\Servers\VerticeServer;
use App\Mcp\Tools\CreateProjectTool;
use App\Mcp\Tools\UpdateProfileTool;
use App\Mcp\Tools\UpdateProjectTool;
use App\Models\AiAccessToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_control_page_toggles_access_and_manages_tokens(): void
    {
        $user = $this->user();
        $this->actingAs($user)->get(route('admin.ai.show'))->assertOk()->assertSee('Integrações com IA')->assertSee(url('/mcp'));
        $this->patch(route('admin.ai.update'), ['enabled' => 1])->assertRedirect();
        $this->assertTrue($user->fresh()->ai_enabled);

        $this->post(route('admin.ai.tokens.store'), ['name' => 'Claude Code'])->assertSessionHas('ai_token');
        $plain = session('ai_token');
        $token = $user->aiAccessTokens()->first();
        $this->assertStringStartsWith('vtx_', $plain);
        $this->assertSame(hash('sha256', $plain), $token->token_hash);
        $this->get(route('admin.ai.show'))->assertSee($plain);

        $other = $this->user();
        $this->actingAs($other)->delete(route('admin.ai.tokens.destroy', $token))->assertNotFound();
        $this->actingAs($user)->delete(route('admin.ai.tokens.destroy', $token))->assertRedirect();
        $this->assertModelMissing($token);
    }

    public function test_mcp_endpoint_requires_a_valid_token_and_enabled_access(): void
    {
        $user = $this->user();
        [, $plain] = AiAccessToken::issue($user, 'Teste');
        $call = fn (?string $bearer) => $this->withHeaders(array_filter(['Authorization' => $bearer ? "Bearer {$bearer}" : null, 'Accept' => 'application/json, text/event-stream']))
            ->postJson('/mcp', ['jsonrpc' => '2.0', 'id' => 1, 'method' => 'ping']);

        $call(null)->assertUnauthorized()->assertHeader('WWW-Authenticate');
        $call('vtx_invalido')->assertUnauthorized();
        $call($plain)->assertForbidden();

        $user->forceFill(['ai_enabled' => true])->save();
        $call($plain)->assertOk();
        $this->assertNotNull($user->aiAccessTokens()->first()->last_used_at);
    }

    public function test_tools_create_and_update_only_the_users_own_content(): void
    {
        $user = $this->user();
        $foreign = $this->user()->portfolio->projects()->create(['title' => 'Alheio', 'display_order' => 0]);

        VerticeServer::actingAs($user)->tool(CreateProjectTool::class, [
            'title' => 'Jogo de nave', 'description' => "Feito em Godot.\n\nRoda no navegador.", 'category' => 'game-web',
            'status' => 'in_development', 'tags' => 'Godot, JavaScript',
        ])->assertOk()->assertSee('Projeto criado.');
        $project = $user->portfolio->projects()->firstOrFail();
        $this->assertSame(['Godot', 'JavaScript'], $project->tags->pluck('name')->sort()->values()->all());

        VerticeServer::actingAs($user)->tool(UpdateProjectTool::class, ['id' => $project->id, 'status' => 'completed'])->assertOk();
        $this->assertSame('completed', $project->fresh()->status);
        $this->assertSame('Jogo de nave', $project->fresh()->title);

        VerticeServer::actingAs($user)->tool(UpdateProjectTool::class, ['id' => $foreign->id, 'title' => 'Hack'])->assertHasErrors();
        $this->assertSame('Alheio', $foreign->fresh()->title);
        VerticeServer::actingAs($user)->tool(CreateProjectTool::class, ['title' => 'X', 'status' => 'quase'])->assertHasErrors();

        VerticeServer::actingAs($user)->tool(UpdateProfileTool::class, ['about' => 'Minha história.'])->assertOk();
        $this->assertSame('Minha história.', $user->portfolio->fresh()->about);
    }

    private function user(): User
    {
        $user = User::factory()->create();
        app(EnsurePortfolio::class)->handle($user);

        return $user->fresh();
    }
}
