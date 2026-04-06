<?php

namespace App\Console\Commands\IGDB;

use App\Services\IgdbService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Throwable;

abstract class AbstractIgdbImport extends Command
{
    public function __construct(protected IgdbService $igdb)
    {
        parent::__construct();
    }

    abstract protected function getEndpoint(): string;

    abstract protected function getQueryBody(): string;

    abstract protected function processItem(array $item): string;

    public function handle(): int
    {
        $limit = 500;
        $cacheKey = 'igdb_import_offset_'.$this->getEndpoint();
        $offset = (int) Cache::get($cacheKey, 0);

        $this->info("Starting import for {$this->getEndpoint()} from offset {$offset}");

        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0];

        try {
            while (true) {
                $query = $this->getQueryBody()." limit {$limit}; offset {$offset};";

                $items = $this->igdb->query($this->getEndpoint(), $query);

                if (empty($items)) {
                    $this->info('Finished import. No more items.');
                    Cache::forget($cacheKey);
                    break;
                }

                foreach ($items as $item) {
                    $status = $this->processItem($item);
                    if (isset($stats[$status])) {
                        $stats[$status]++;
                    }
                }

                $this->info(sprintf(
                    'Processed %d items. Stats - Created: %d | Updated: %d | Skipped: %d',
                    count($items), $stats['created'], $stats['updated'], $stats['skipped']
                ));

                $offset += $limit;
                Cache::put($cacheKey, $offset, now()->addDays(7));

                usleep(400_000);
            }

            $this->info("Successfully finished import for {$this->getEndpoint()}!\n");

            return static::SUCCESS;

        } catch (Throwable $e) {
            $this->error("Import failed: ".$e->getMessage()."\n");

            return static::FAILURE;
        }
    }
}
