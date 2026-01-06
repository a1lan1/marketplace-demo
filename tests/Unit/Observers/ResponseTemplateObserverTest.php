<?php

use App\Models\ResponseTemplate;
use App\Observers\ResponseTemplateObserver;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;

it('clears cache on created event', function (): void {
    $taggableStoreMock = Mockery::mock(TaggableStore::class);
    $taggableStoreMock->shouldReceive('flush')->once();

    Cache::shouldReceive('tags')
        ->with(['response_templates'])
        ->once()
        ->andReturn($taggableStoreMock);

    $observer = new ResponseTemplateObserver;
    $template = new ResponseTemplate;

    $observer->created($template);

    expect(true)->toBeTrue();
});

it('clears cache on updated event', function (): void {
    $taggableStoreMock = Mockery::mock(TaggableStore::class);
    $taggableStoreMock->shouldReceive('flush')->once();

    Cache::shouldReceive('tags')
        ->with(['response_templates'])
        ->once()
        ->andReturn($taggableStoreMock);

    $observer = new ResponseTemplateObserver;
    $template = new ResponseTemplate;

    $observer->updated($template);

    expect(true)->toBeTrue();
});

it('clears cache on deleted event', function (): void {
    $taggableStoreMock = Mockery::mock(TaggableStore::class);
    $taggableStoreMock->shouldReceive('flush')->once();

    Cache::shouldReceive('tags')
        ->with(['response_templates'])
        ->once()
        ->andReturn($taggableStoreMock);

    $observer = new ResponseTemplateObserver;
    $template = new ResponseTemplate;

    $observer->deleted($template);

    expect(true)->toBeTrue();
});
