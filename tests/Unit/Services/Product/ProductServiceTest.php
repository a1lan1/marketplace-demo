<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Product;

use App\Contracts\NlpSearchPreprocessingServiceInterface;
use App\Contracts\RecommendationServiceInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\DTO\ProductDTO;
use App\Models\Product;
use App\Models\User;
use App\Services\Product\ProductService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Mockery;

beforeEach(function (): void {
    $this->recommendationServiceMock = $this->mock(RecommendationServiceInterface::class);
    $this->nlpSearchPreprocessingServiceMock = $this->mock(NlpSearchPreprocessingServiceInterface::class);
    $this->productRepositoryMock = $this->mock(ProductRepositoryInterface::class);

    $this->productService = new ProductService(
        $this->recommendationServiceMock,
        $this->nlpSearchPreprocessingServiceMock,
        $this->productRepositoryMock
    );
});

test('get paginated products', function (): void {
    // Arrange
    $paginatorMock = $this->mock(LengthAwarePaginator::class);
    $this->productRepositoryMock
        ->shouldReceive('getPaginated')
        ->once()
        ->with(12, 1)
        ->andReturn($paginatorMock);

    // Act
    $result = $this->productService->getPaginatedProducts();

    // Assert
    expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
});

test('get user products returns paginated list', function (): void {
    // Arrange
    $userMock = $this->mock(User::class);
    $paginatorMock = $this->mock(LengthAwarePaginator::class);

    $this->productRepositoryMock
        ->shouldReceive('getForUser')
        ->once()
        ->with($userMock, 10, 1)
        ->andReturn($paginatorMock);

    // Act
    $result = $this->productService->getUserProducts($userMock);

    // Assert
    expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
});

test('store product creates or updates product', function (): void {
    // Arrange
    $userMock = $this->mock(User::class);
    $productDTO = new ProductDTO(
        user: $userMock,
        name: 'New Product',
        description: 'A new product description',
        price: 10000,
        stock: 10,
        coverImage: UploadedFile::fake()->image('cover.jpg')
    );

    $productMock = $this->mock(Product::class);

    $this->productRepositoryMock
        ->shouldReceive('store')
        ->once()
        ->with($productDTO)
        ->andReturn($productMock);

    // Act
    $result = $this->productService->storeProduct($productDTO);

    // Assert
    expect($result)->toBe($productMock);
});

test('delete product calls delete on the repository', function (): void {
    // Arrange
    $productMock = $this->mock(Product::class);
    $this->productRepositoryMock
        ->shouldReceive('delete')
        ->once()
        ->with($productMock);

    // Act
    $this->productService->deleteProduct($productMock);

    // Assert
    $this->assertTrue(true); // Placeholder assertion
});

test('get autocomplete suggestions preprocesses query and returns mapped products', function (): void {
    // Arrange
    $searchQuery = '  Test Query  ';
    $processedQuery = 'test query';
    $limit = 5;

    $this->nlpSearchPreprocessingServiceMock
        ->shouldReceive('preprocessQuery')
        ->once()
        ->with($searchQuery)
        ->andReturn($processedQuery);

    $product1 = Mockery::mock(Product::class);
    $product1->shouldReceive('only')->with('id', 'name')->andReturn(['id' => 1, 'name' => 'Test Product 1']);

    $product2 = Mockery::mock(Product::class);
    $product2->shouldReceive('only')->with('id', 'name')->andReturn(['id' => 2, 'name' => 'Test Product 2']);

    $rawProductsFromRepo = collect([$product1, $product2]);

    $this->productRepositoryMock
        ->shouldReceive('searchSuggestions')
        ->once()
        ->with($processedQuery, $limit)
        ->andReturn($rawProductsFromRepo);

    // Act
    $result = $this->productService->getAutocompleteSuggestions($searchQuery, $limit);

    // Assert
    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->count())->toBe(2)
        ->and($result->all())->toBe([
            ['id' => 1, 'name' => 'Test Product 1'],
            ['id' => 2, 'name' => 'Test Product 2'],
        ]);
});
