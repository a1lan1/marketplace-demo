<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Contracts\LoggableEvent;
use App\Jobs\LogActivityJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogActivityListener implements ShouldQueue
{
    public function handle(LoggableEvent $event): void
    {
        dispatch(new LogActivityJob(
            performedOn: $event->getPerformedOn(),
            causedBy: $event->getCausedBy(),
            description: $event->getDescription(),
            properties: $event->getProperties()
        ));
    }
}
