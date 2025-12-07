<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Mockery;
use Spatie\MediaLibrary\MediaCollections\FileAdder;

it('can upload a cover image', function (): void {
    // Arrange
    $product = Mockery::mock(Product::class)->makePartial();
    $file = UploadedFile::fake()->image('cover.jpg');

    $fileAdderMock = Mockery::mock(FileAdder::class);
    $fileAdderMock->shouldReceive('usingFileName')->with($file->hashName())->once()->andReturnSelf();
    $fileAdderMock->shouldReceive('toMediaCollection')->with('product.cover-image')->once();

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
    // Arrange
    $product = Product::factory()->create();
    $file = UploadedFile::fake()->image('cover.jpg');
    $product->uploadCoverImage($file);

    // Act
    $coverImageUrl = $product->cover_image;

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
