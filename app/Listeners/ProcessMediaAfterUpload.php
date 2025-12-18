<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\MediaCollection;
use App\Jobs\DispatchImageUploadedToKafka;
use App\Models\Product;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class ProcessMediaAfterUpload
{
    public function handle(MediaHasBeenAddedEvent $event): void
    {
        $model = $event->media->model;

        if ($model instanceof Product && $event->media->collection_name === MediaCollection::ProductCoverImage->value) {
            dispatch(new DispatchImageUploadedToKafka($model->id, '/storage/'.$event->media->getPathRelativeToRoot()));
        }
    }
}
