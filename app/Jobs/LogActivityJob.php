<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogActivityJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public ?Model $performedOn,
        public ?Model $causedBy,
        public string $description,
        public array $properties = []
    ) {}

    public function handle(): void
    {
        activity()
            ->performedOn($this->performedOn)
            ->causedBy($this->causedBy)
            ->withProperties($this->properties)
            ->log($this->description);
    }
}
