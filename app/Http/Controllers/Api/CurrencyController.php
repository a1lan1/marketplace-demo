<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\Services\CurrencyServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\CurrencyRatesResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function __construct(protected CurrencyServiceInterface $currencyService) {}

    /**
     * @throws Exception
     */
    public function rates(Request $request): JsonResponse|CurrencyRatesResource
    {
        $base = $request->input('base', 'USD');

        try {
            $data = $this->currencyService->getRates($base);

            return CurrencyRatesResource::make($data);
        } catch (Exception) {
            return response()->json(['error' => 'Failed to fetch rates'], 503);
        }
    }
}
