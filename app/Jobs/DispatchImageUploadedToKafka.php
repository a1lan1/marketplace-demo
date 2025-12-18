<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Kafka\Producers\ImageUploadedProducer;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchImageUploadedToKafka implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $productId,
        private readonly string $imageUrl
    ) {}

    /**
     * @throws Exception
     */
    public function handle(ImageUploadedProducer $producer): void
    {
        $producer->publish($this->productId, $this->imageUrl);
    }
}
