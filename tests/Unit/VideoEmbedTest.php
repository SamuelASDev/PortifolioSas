<?php

namespace Tests\Unit;

use App\Services\ProjectDescription;
use App\Services\VideoEmbed;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class VideoEmbedTest extends TestCase
{
    #[DataProvider('validVideos')]
    public function test_video_urls_are_normalized_without_fetching_remote_content(string $url, string $expected): void
    {
        $this->assertSame($expected, (new VideoEmbed)->resolve($url)['src']);
    }

    public static function validVideos(): array
    {
        $youtube = 'https://www.youtube-nocookie.com/embed/abcdefghijk';

        return [
            ['https://www.youtube.com/watch?v=abcdefghijk&autoplay=1', $youtube],
            ['https://youtu.be/abcdefghijk?t=10', $youtube],
            ['https://youtube.com/shorts/abcdefghijk', $youtube],
            ['https://www.youtube-nocookie.com/embed/abcdefghijk', $youtube],
            ['https://vimeo.com/123456789', 'https://player.vimeo.com/video/123456789'],
            ['https://player.vimeo.com/video/123456789', 'https://player.vimeo.com/video/123456789'],
        ];
    }

    #[DataProvider('invalidVideos')]
    public function test_unknown_hosts_and_malformed_video_urls_are_rejected(string $url): void
    {
        $this->assertNull((new VideoEmbed)->resolve($url));
    }

    public static function invalidVideos(): array
    {
        return [
            ['javascript:alert(1)'], ['https://youtube.com.evil.example/watch?v=abcdefghijk'],
            ['https://youtube.com@evil.example/watch?v=abcdefghijk'], ['https://evil.example/video'],
            ['https://www.youtube.com/watch?v[]=abcdefghijk'], ['https://youtu.be/invalid'],
            ['https://vimeo.com/not-a-number'], ['https://www.youtube.com:444/embed/abcdefghijk'],
            ['//www.youtube.com/embed/abcdefghijk'], ['https://player.vimeo.com/video/123/evil'],
        ];
    }

    public function test_iframe_output_is_canonical_and_cannot_keep_injected_attributes(): void
    {
        $html = (new ProjectDescription)->sanitize('<p>Antes</p><iframe src="https://www.youtube.com/embed/abcdefghijk?autoplay=1" srcdoc="evil" onload="alert(1)" allow="camera;microphone"></iframe><p>Depois</p>');
        $this->assertStringContainsString('src="https://www.youtube-nocookie.com/embed/abcdefghijk"', $html);
        $this->assertStringContainsString('allowfullscreen', $html);
        $this->assertStringNotContainsString('srcdoc', $html);
        $this->assertStringNotContainsString('onload', $html);
        $this->assertStringNotContainsString('autoplay', $html);
        $this->assertStringNotContainsString('camera', $html);
        $this->assertStringContainsString('Antes', $html);
        $this->assertStringContainsString('Depois', $html);
    }
}
