<?php

namespace Tests\Unit;

use App\Services\ProjectDescription;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ProjectDescriptionTest extends TestCase
{
    public function test_supported_formatting_and_code_are_preserved(): void
    {
        $html = '<h2>Visão geral</h2><h3>Detalhes</h3><p><strong>Negrito</strong> e <em>itálico</em><br />Nova linha.</p>'
            .'<ul><li>Uma tarefa</li></ul><ol start="3"><li>Terceiro passo</li></ol>'
            .'<blockquote><p>Uma citação.</p></blockquote><p><code>$value</code></p>'
            .'<pre><code>&lt;script&gt;exemplo&lt;/script&gt;</code></pre><p><a href="https://example.com">Referência</a></p>';
        $clean = (new ProjectDescription)->sanitize($html);

        foreach (['<h2>Visão geral</h2>', '<h3>Detalhes</h3>', '<strong>Negrito</strong>', '<em>itálico</em>',
            '<ul>', '<ol start="3">', '<blockquote>', '<code>$value</code>', '&lt;script&gt;exemplo&lt;/script&gt;',
            'href="https://example.com"'] as $expected) {
            $this->assertStringContainsString($expected, $clean);
        }
    }

    #[DataProvider('unsafeHtml')]
    public function test_active_content_and_unapproved_attributes_are_removed(string $payload): void
    {
        $clean = (new ProjectDescription)->sanitize($payload);

        $this->assertDoesNotMatchRegularExpression('/<(script|style|iframe|svg|math|img|video|object|embed|form|input)\b/i', $clean);
        $this->assertDoesNotMatchRegularExpression('/\s(on\w+|style|class|id|target)\s*=/i', $clean);
        $this->assertStringNotContainsString('javascript:', strtolower($clean));
    }

    public static function unsafeHtml(): array
    {
        return [
            ['<p onclick="alert(1)" style="color:red" class="fixed" id="overlay">Texto</p><script>alert(1)</script>'],
            ['<img src="https://tracker.example/x" onerror="alert(1)"><iframe src="https://example.com">Frame</iframe>'],
            ['<svg><a xlink:href="javascript:alert(1)">X</a></svg><math><mtext><img src=x onerror=alert(1)></mtext></math>'],
            ['<form action="https://evil.example"><input autofocus onfocus="alert(1)"></form><style>body{display:none}</style>'],
            ['<object data="data:text/html,evil"></object><embed src="x"><video src="x"></video>'],
            ['<p><a href="https://example.com" target="_blank" onclick="alert(1)" style="color:red">Link</a></p>'],
        ];
    }

    #[DataProvider('unsafeUris')]
    public function test_only_explicit_http_and_https_links_are_accepted(string $uri): void
    {
        $clean = (new ProjectDescription)->sanitize('<p><a href="'.$uri.'">Link</a></p>');
        $this->assertStringNotContainsString('href=', $clean);
        $this->assertStringContainsString('Link', $clean);
    }

    public static function unsafeUris(): array
    {
        return [
            ['javascript:alert(1)'], ['java&#x73;cript:alert(1)'], ['java&#10;script:alert(1)'],
            ['data:text/html;base64,PHNjcmlwdD4='], ['vbscript:msgbox(1)'], ['ftp://example.com'],
            ['mailto:person@example.com'], ['//example.com/path'], ['/admin/projects'], ['#section'],
        ];
    }

    public function test_repeated_sanitization_is_stable_and_summaries_are_plain_text(): void
    {
        $service = new ProjectDescription;
        $html = '<h2>Problema</h2><p>Um &amp; dois.</p><ul><li>Primeiro</li><li>Segundo</li></ul>';
        $clean = $service->sanitize($html);

        $this->assertSame($clean, $service->sanitize($clean));
        $this->assertSame('Problema Um & dois. Primeiro Segundo', $service->text($clean, 'html'));
    }

    public function test_legacy_plain_text_is_never_interpreted_as_html(): void
    {
        $service = new ProjectDescription;
        $text = "Exemplo <strong>literal</strong> &amp;\nLinha seguinte.";

        $this->assertStringContainsString('&lt;strong&gt;literal&lt;/strong&gt;', $service->html($text, 'text'));
        $this->assertStringContainsString('&amp;amp;', $service->html($text, 'text'));
        $this->assertStringContainsString('<br />', $service->html($text, 'text'));
        $this->assertSame($text, $service->text($text, 'text'));
        $this->assertSame('', $service->html(null));
    }
}
