<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\MediaCollection;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Mockery;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

it('can upload a cover image', function (): void {
    Event::fake();
    // Arrange
    $product = Mockery::mock(Product::class)->makePartial();
    $product->id = 1;

    $file = UploadedFile::fake()->image('cover.jpg');

    $fileAdderMock = Mockery::mock(FileAdder::class);
    $fileAdderMock->shouldReceive('usingFileName')->with($file->hashName())->once()->andReturnSelf();
    $mediaMock = Mockery::mock(Media::class);
    $mediaMock->shouldReceive('getPathRelativeToRoot')->andReturn('1/cover.jpg'); // Mock this for the listener
    $mediaMock->shouldReceive('getFullUrl')->andReturn('http://example.com/cover.jpg'); // Mock this for the listener

    $fileAdderMock->shouldReceive('toMediaCollection')
        ->with(MediaCollection::ProductCoverImage->value)
        ->once()
        ->andReturn($mediaMock);

    $product->shouldReceive('addMedia')
        ->with(Mockery::on(function ($arg) use ($file): bool {
            return $arg instanceof UploadedFile && $arg->hashName() === $file->hashName();
        }))
        ->once()
        ->andReturn($fileAdderMock);

    // Act
    $product->uploadCoverImage($file);

    // Assertions for media library interaction are done via Mockery expectations
    $this->assertTrue(true); // Placeholder assertion
});

it('returns the cover image URL', function (): void {
    Event::fake();
    // Arrange
    $product = Product::factory()->create();
    $file = UploadedFile::fake()->image('cover.jpg');
    $product->uploadCoverImage($file);

    // Act
    $coverImageUrl = $product->fresh()->cover_image;

    // Assert
    expect($coverImageUrl)->toContain($file->hashName());
});

it('returns a gravatar if no cover image is present', function (): void {
    // Arrange
    $product = Product::factory()->create(['name' => 'Test Product']);

    // Act
    $coverImageUrl = $product->cover_image;

    // Assert
    expect($coverImageUrl)->toContain('gravatar.com');
    expect($coverImageUrl)->toContain(md5(strtolower(trim($product->name))));
});
