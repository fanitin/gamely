<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

#[Signature('sitemap:generate')]
#[Description('Generate public/sitemap.xml and public/robots.txt from the application routes')]
class GenerateSitemap extends Command
{
    private const ROUTES = [
        ['name' => 'home', 'priority' => 1.0, 'frequency' => Url::CHANGE_FREQUENCY_DAILY],
        ['name' => 'classic', 'priority' => 0.9, 'frequency' => Url::CHANGE_FREQUENCY_DAILY],
        ['name' => 'screenshots', 'priority' => 0.9, 'frequency' => Url::CHANGE_FREQUENCY_DAILY],
        ['name' => 'character', 'priority' => 0.9, 'frequency' => Url::CHANGE_FREQUENCY_DAILY],
        ['name' => 'legal.privacy', 'priority' => 0.3, 'frequency' => Url::CHANGE_FREQUENCY_YEARLY],
        ['name' => 'legal.terms', 'priority' => 0.3, 'frequency' => Url::CHANGE_FREQUENCY_YEARLY],
        ['name' => 'legal.cookie', 'priority' => 0.3, 'frequency' => Url::CHANGE_FREQUENCY_YEARLY],
    ];

    private const DISALLOWED_PATHS = ['/api/', '/build/'];

    public function handle(): int
    {
        $sitemap = Sitemap::create();

        foreach (self::ROUTES as $route) {
            $sitemap->add(
                Url::create(route($route['name']))
                    ->setLastModificationDate(now())
                    ->setChangeFrequency($route['frequency'])
                    ->setPriority($route['priority'])
            );
        }

        $sitemap->writeToFile(public_path('sitemap.xml'));
        $this->info('Wrote '.count(self::ROUTES).' urls to public/sitemap.xml');

        $this->writeRobots();
        $this->info('Wrote public/robots.txt');

        return self::SUCCESS;
    }

    private function writeRobots(): void
    {
        $lines = ['User-agent: *', 'Allow: /'];

        foreach (self::DISALLOWED_PATHS as $path) {
            $lines[] = 'Disallow: '.$path;
        }

        $lines[] = '';
        $lines[] = 'Sitemap: '.url('sitemap.xml');
        $lines[] = '';

        file_put_contents(public_path('robots.txt'), implode(PHP_EOL, $lines));
    }
}
