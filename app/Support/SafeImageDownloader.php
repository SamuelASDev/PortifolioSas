<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Baixa imagens de URLs públicas com proteção contra SSRF: só http/https,
 * só IPs públicos (o IP verificado é fixado na conexão), sem redirecionamentos
 * e com limite de tamanho.
 */
class SafeImageDownloader
{
    /** @return UploadedFile|string O arquivo baixado ou a mensagem de erro. */
    public function download(string $url): UploadedFile|string
    {
        $parts = parse_url($url);
        $host = $parts['host'] ?? '';
        $scheme = strtolower($parts['scheme'] ?? '');
        if (! in_array($scheme, ['http', 'https'], true) || $host === '' || isset($parts['user'])) {
            return 'Use um endereço http ou https público.';
        }
        $ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return 'Esse endereço não é público. Envie a imagem com request-image-upload-tool.';
        }
        $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);
        $limit = config('portfolio.attachments.max_size_kb') * 1024;
        $path = tempnam(sys_get_temp_dir(), 'vtx-img');

        try {
            $response = Http::timeout(15)->withoutRedirecting()
                ->withOptions([
                    'sink' => $path,
                    'curl' => [CURLOPT_RESOLVE => ["{$host}:{$port}:{$ip}"], CURLOPT_MAXFILESIZE => $limit, CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS],
                    // Corta a transferência se passar do limite (servidores que não informam o tamanho).
                    'progress' => function ($total, $downloaded) use ($limit) {
                        if ($downloaded > $limit) {
                            throw new \RuntimeException('Imagem acima do limite.');
                        }
                    },
                ])
                ->get($url);
        } catch (Throwable) {
            @unlink($path);

            return 'Não foi possível baixar a imagem (endereço fora do ar, lento demais ou arquivo acima de 10 MB).';
        }
        if (! $response->successful() || filesize($path) === 0 || filesize($path) > $limit) {
            @unlink($path);

            return 'Não foi possível baixar a imagem (resposta '.$response->status().'). Confira se o link abre direto a imagem.';
        }

        return new UploadedFile($path, basename($parts['path'] ?? 'imagem') ?: 'imagem', null, null, true);
    }
}
