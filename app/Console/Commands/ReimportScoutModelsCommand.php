<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Location;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ReimportScoutModelsCommand extends Command
{
    protected $signature = 'scout:reimport-all';

    protected $description = 'Re-imports all searchable models';

    /**
     * @var array<class-string>
     */
    protected array $searchableModels = [
        Product::class,
        Location::class,
    ];

    public function handle(): int
    {
        $this->info('Starting re-import of all searchable models...');

        foreach ($this->searchableModels as $model) {
            $this->line(sprintf('Importing <comment>%s</comment>...', $model));

            Artisan::call('scout:import', ['model' => $model], $this->getOutput());

            $this->info(sprintf('Finished importing <comment>%s</comment>.', $model));
        }

        $this->info('All searchable models have been re-imported successfully!');

        return self::SUCCESS;
    }
}
