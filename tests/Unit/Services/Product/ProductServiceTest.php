<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Contracts\NlpSearchPreprocessingServiceInterface;
use App\Contracts\ProductSearcherInterface;
use App\Contracts\RecommendationServiceInterface;
use App\DTO\ProductDTO;
use App\Models\Product;
use App\Models\User;
use App\Services\Product\ProductService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder as ScoutBuilder;
use Mockery;
use Mockery\MockInterface;

beforeEach(function (): void {
    $this->productSearcherMock = $this->mock(ProductSearcherInterface::class);
    $this->recommendationServiceMock = $this->mock(RecommendationServiceInterface::class);
    $this->nlpSearchPreprocessingServiceMock = $this->mock(NlpSearchPreprocessingServiceInterface::class);

    $this->productService = new ProductService(
        $this->recommendationServiceMock,
        $this->nlpSearchPreprocessingServiceMock,
        $this->productSearcherMock
    );
});

test('get paginated products', function (): void {
    // Arrange
    Product::factory()->count(15)->create();

    // Act
    $result = $this->productService->getPaginatedProducts();

    // Assert
    expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($result->total())->toBe(15)
        ->and($result->perPage())->toBe(12);
});

test('get user products returns paginated list', function (): void {
    // Arrange
    $userMock = $this->mock(User::class);
    // Mock the id attribute access
    $userMock->shouldReceive('getAttribute')->with('id')->andReturn(1);

    $paginatorMock = $this->mock(LengthAwarePaginator::class);
    $relationMock = $this->mock(HasMany::class, function (MockInterface $mock) use ($paginatorMock): void {
        $mock->shouldReceive('select')->once()->with(Mockery::type('array'))->andReturnSelf();
        $mock->shouldReceive('latest')->once()->andReturnSelf();
        $mock->shouldReceive('paginate')
            ->with(10, ['*'], 'page', 1)
            ->once()
            ->andReturn($paginatorMock);
    });

    $userMock->shouldReceive('products')->once()->andReturn($relationMock);

    // Act
    $result = $this->productService->getUserProducts($userMock);

    // Assert
    expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
});

test('store product creates or updates product and uploads image', function (): void {
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
    $relationMock = $this->mock(HasMany::class);

    $userMock->shouldReceive('products')->once()->andReturn($relationMock);
    $relationMock->shouldReceive('updateOrCreate')
        ->with(['id' => null], $productDTO->toArray())
        ->once()
        ->andReturn($productMock);

    $productMock->shouldReceive('uploadCoverImage')->with($productDTO->coverImage)->once();

    // Act
    $result = $this->productService->storeProduct($productDTO);

    // Assert
    expect($result)->toBe($productMock);
});

test('delete product calls delete on the model', function (): void {
    // Arrange
    $productMock = $this->mock(Product::class);
    $productMock->shouldReceive('delete')->once();

    // Act
    $this->productService->deleteProduct($productMock);

    // Assert
    $this->assertTrue(true); // Placeholder assertion
});

test('get autocomplete suggestions preprocesses query and returns products', function (): void {
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

    $expectedProducts = collect([$product1, $product2]);

    $scoutBuilderMock = Mockery::mock(ScoutBuilder::class);
    $scoutBuilderMock->shouldReceive('take')->with($limit)->andReturnSelf();
    $scoutBuilderMock->shouldReceive('get')->andReturn($expectedProducts);

    $this->productSearcherMock
        ->shouldReceive('search')
        ->once()
        ->with($processedQuery)
        ->andReturn($scoutBuilderMock);

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
