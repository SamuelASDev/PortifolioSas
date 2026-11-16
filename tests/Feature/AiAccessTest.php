<?php

namespace Tests\Feature;

use App\Actions\EnsurePortfolio;
use App\Mcp\Servers\VerticeServer;
use App\Mcp\Tools\CreateProjectTool;
use App\Mcp\Tools\RequestImageUploadTool;
use App\Mcp\Tools\UpdateProfileTool;
use App\Mcp\Tools\UpdateProjectTool;
use App\Mcp\Tools\UploadImageFromUrlTool;
use App\Models\AiAccessToken;
use App\Models\ProjectMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
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

    public function test_oauth_registration_only_accepts_known_redirect_domains(): void
    {
        $register = fn (string $uri) => $this->postJson('/oauth/register', ['client_name' => 'Claude', 'redirect_uris' => [$uri]]);

        $register('https://evil.example/callback')->assertStatus(400)->assertJsonPath('error', 'invalid_redirect_uri');
        $register('https://claude.ai.evil.example/cb')->assertStatus(400);
        $register('https://claude.ai/api/mcp/auth_callback')->assertSuccessful();
        $register('http://localhost:33418/callback')->assertSuccessful();
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

    public function test_ai_can_upload_a_cover_and_description_images(): void
    {
        Storage::fake('public');
        $png = fn (string $name) => UploadedFile::fake()->createWithContent($name, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a8V8AAAAASUVORK5CYII='));
        $user = $this->user();
        $user->forceFill(['ai_enabled' => true])->save();
        $project = $user->portfolio->projects()->create(['title' => 'App', 'display_order' => 0]);

        VerticeServer::actingAs($user)->tool(RequestImageUploadTool::class, ['project_id' => $project->id, 'kind' => 'cover'])
            ->assertOk()->assertSee(['upload_url', 'curl']);
        VerticeServer::actingAs($user)->tool(UploadImageFromUrlTool::class, ['project_id' => $project->id, 'kind' => 'cover', 'url' => 'http://127.0.0.1/x.png'])
            ->assertHasErrors(['não é público']);

        $link = fn (string $kind) => URL::temporarySignedRoute('ai.upload', now()->addMinutes(10), [$project, $kind, Str::random(32)]);
        $cover = $link('cover');
        $this->post($cover, ['file' => $png('capa.png')])->assertCreated()->assertJsonPath('message', 'Capa definida.');
        $this->assertNotNull($project->fresh()->cover_image);
        $this->post($cover, ['file' => $png('capa.png')])->assertStatus(410);
        $this->post(str_replace('signature=', 'signature=x', $link('cover')), ['file' => $png('a.png')])->assertForbidden();
        $this->post($link('image'), ['file' => UploadedFile::fake()->create('nota.txt', 1, 'text/plain')])->assertUnprocessable();

        $html = $this->post($link('image'), ['file' => $png('print.png')])->assertCreated()->json('html');
        VerticeServer::actingAs($user)->tool(UpdateProjectTool::class, [
            'id' => $project->id, 'description_format' => 'html', 'description' => '<p>Tela inicial</p>'.str_replace('Descreva a imagem', 'Tela inicial', $html),
        ])->assertOk();
        $this->assertSame(1, ProjectMedia::where('project_id', $project->id)->whereNotNull('attached_at')->count());
        $this->assertStringContainsString('alt="Tela inicial"', $project->fresh()->description);

        $user->forceFill(['ai_enabled' => false])->save();
        $this->post($link('image'), ['file' => $png('p.png')])->assertForbidden();
    }

    private function user(): User
    {
        $user = User::factory()->create();
        app(EnsurePortfolio::class)->handle($user);

        return $user->fresh();
    }
}
