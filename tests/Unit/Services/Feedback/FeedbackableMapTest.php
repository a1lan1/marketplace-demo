<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Feedback;

use App\Models\Product;
use App\Models\User;
use App\Services\Feedback\FeedbackableMap;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

test('get returns correct model class for valid type', function (): void {
    $map = new FeedbackableMap([
        'product' => Product::class,
        'seller' => User::class,
    ]);

    expect($map->get('product'))->toBe(Product::class);
    expect($map->get('seller'))->toBe(User::class);
});

test('get throws exception for invalid type', function (): void {
    $map = new FeedbackableMap([]);

    expect(fn (): string => $map->get('invalid'))->toThrow(NotFoundHttpException::class);
});
