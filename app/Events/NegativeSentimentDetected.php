<?php

declare(strict_types=1);

namespace App\Events;

use App\Contracts\Sentimentable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NegativeSentimentDetected
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Sentimentable $model) {}
}
