<?php

use App\Http\Resources\OrderProductResource;
use App\Models\OrderProduct;
use Illuminate\Http\Request;

it('formats order product data correctly', function (): void {
    $orderProduct = OrderProduct::factory()->create([
        'quantity' => 2,
        'price' => 1000,
    ]);

    $resource = new OrderProductResource($orderProduct);
    $request = new Request;
    $result = $resource->toArray($request);

    expect($result['quantity'])->toBe(2)
        ->and($result['price'])->toBe('1000');
});
