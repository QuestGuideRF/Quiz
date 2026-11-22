<?php
declare(strict_types=1);
namespace App\Services;
final class SitemapService
{
    public function __construct(
        private readonly string $basePath,
        private readonly array $appConfig
    ) {
    }
    public function sync(array $tests): void
    {
        $baseUrl = rtrim((string) ($this->appConfig['base_url'] ?? ''), '/');
        if ($baseUrl === '') {
            return;
        }
        $urls = [
            [
                'loc' => $baseUrl . '/',
                'lastmod' => date('c'),
                'priority' => '1.0',
            ],
        ];
        foreach ($tests as $test) {
            $urls[] = [
                'loc' => $baseUrl . '/test/' . $test['slug'],
                'lastmod' => date('c', strtotime((string) ($test['updated_at'] ?? 'now'))),
                'priority' => '0.8',
            ];
        }
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        $urlset = $xml->createElement('urlset');
        $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        foreach ($urls as $item) {
            $url = $xml->createElement('url');
            $url->appendChild($xml->createElement('loc', $item['loc']));
            $url->appendChild($xml->createElement('lastmod', $item['lastmod']));
            $url->appendChild($xml->createElement('priority', $item['priority']));
            $urlset->appendChild($url);
        }
        $xml->appendChild($urlset);
        $xml->save($this->basePath . '/sitemap.xml');
    }
}