<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Contracts\NlpSearchPreprocessingServiceInterface;
use App\Models\Product;
use App\Services\Product\ProductService;
use App\Services\RecommendationService;
use Illuminate\Support\Collection;
use Mockery;

/**
 * @runInSeparateProcess
 *
 * @preserveGlobalState disabled
 */
test('get autocomplete suggestions preprocesses query and returns products', function (): void {
    // Arrange
    $searchQuery = '  Test Query  ';
    $processedQuery = 'test query';
    $limit = 5;

    $nlpSearchPreprocessingServiceMock = Mockery::mock(NlpSearchPreprocessingServiceInterface::class);
    $recommendationServiceMock = Mockery::mock(RecommendationService::class);

    $nlpSearchPreprocessingServiceMock
        ->shouldReceive('preprocessQuery')
        ->once()
        ->with($searchQuery)
        ->andReturn($processedQuery);

    // Mock the Product::search() method
    $mockedSearchBuilder = Mockery::mock();
    $mockedSearchBuilder->shouldReceive('take')->with($limit)->andReturnSelf();
    $mockedSearchBuilder->shouldReceive('get')->andReturn(collect([
        (object) ['id' => 1, 'name' => 'Test Product 1'],
        (object) ['id' => 2, 'name' => 'Test Product 2'],
    ]));

    // Use alias to mock static method search
    $productMock = Mockery::mock('alias:'.Product::class);
    $productMock->shouldReceive('search')
        ->once()
        ->with($processedQuery)
        ->andReturn($mockedSearchBuilder);

    $productService = new ProductService(
        $recommendationServiceMock,
        $nlpSearchPreprocessingServiceMock
    );

    // Act
    $result = $productService->getAutocompleteSuggestions($searchQuery, $limit);

    // Assert
    $this->assertInstanceOf(Collection::class, $result);
    $this->assertEquals(2, $result->count());
});
