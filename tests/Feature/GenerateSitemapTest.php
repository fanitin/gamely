<?php

namespace Tests\Feature;

use Tests\TestCase;

class GenerateSitemapTest extends TestCase
{
    private string $sitemapPath;

    private string $robotsPath;

    private ?string $sitemapBackup = null;

    private ?string $robotsBackup = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sitemapPath = public_path('sitemap.xml');
        $this->robotsPath = public_path('robots.txt');

        $this->sitemapBackup = file_exists($this->sitemapPath)
            ? file_get_contents($this->sitemapPath)
            : null;

        $this->robotsBackup = file_exists($this->robotsPath)
            ? file_get_contents($this->robotsPath)
            : null;
    }

    protected function tearDown(): void
    {
        $this->restore($this->sitemapPath, $this->sitemapBackup);
        $this->restore($this->robotsPath, $this->robotsBackup);

        parent::tearDown();
    }

    private function restore(string $path, ?string $contents): void
    {
        if ($contents === null) {
            if (file_exists($path)) {
                unlink($path);
            }

            return;
        }

        file_put_contents($path, $contents);
    }

    public function test_it_writes_every_public_route_to_the_sitemap(): void
    {
        $this->artisan('sitemap:generate')->assertSuccessful();

        $this->assertFileExists($this->sitemapPath);

        $xml = file_get_contents($this->sitemapPath);

        foreach (['home', 'classic', 'screenshots', 'character', 'legal.privacy', 'legal.terms', 'legal.cookie'] as $name) {
            $this->assertStringContainsString(
                '<loc>'.route($name).'</loc>',
                $xml,
                "Expected {$name} in the sitemap"
            );
        }

        $this->assertSame(7, substr_count($xml, '<loc>'));
    }

    public function test_it_does_not_expose_api_routes_in_the_sitemap(): void
    {
        $this->artisan('sitemap:generate')->assertSuccessful();

        $this->assertStringNotContainsString('/api/', file_get_contents($this->sitemapPath));
    }

    public function test_it_writes_robots_with_disallow_rules_and_sitemap_reference(): void
    {
        $this->artisan('sitemap:generate')->assertSuccessful();

        $this->assertFileExists($this->robotsPath);

        $robots = file_get_contents($this->robotsPath);

        $this->assertStringContainsString('User-agent: *', $robots);
        $this->assertStringContainsString('Disallow: /api/', $robots);
        $this->assertStringContainsString('Disallow: /build/', $robots);
        $this->assertStringContainsString('Sitemap: '.url('sitemap.xml'), $robots);
    }

    public function test_it_uses_the_configured_app_url_for_absolute_urls(): void
    {
        url()->forceRootUrl('https://gamely.example');

        $this->artisan('sitemap:generate')->assertSuccessful();

        $xml = file_get_contents($this->sitemapPath);

        $this->assertStringContainsString('gamely.example', $xml);
        $this->assertStringNotContainsString('localhost', $xml);
        $this->assertStringContainsString(
            'Sitemap: '.url('sitemap.xml'),
            file_get_contents($this->robotsPath)
        );
    }
}
