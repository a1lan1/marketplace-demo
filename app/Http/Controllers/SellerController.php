<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\SellerServiceInterface;
use App\Http\Resources\SellerResource;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class SellerController extends Controller
{
    public function __construct(private readonly SellerServiceInterface $sellerService) {}

    public function show(User $seller): Response
    {
        $seller = $this->sellerService->getSellerWithProducts($seller);

        return Inertia::render('Seller/Show', [
            'seller' => SellerResource::make($seller),
        ]);
    }
}
