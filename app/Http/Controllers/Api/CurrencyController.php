<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\Services\CurrencyServiceInterface;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function __construct(protected CurrencyServiceInterface $currencyService) {}

    public function rates(Request $request): JsonResponse
    {
        $base = $request->input('base', 'USD');

        try {
            $data = $this->currencyService->getRates($base);

            return response()->json($data);
        } catch (Exception $exception) {
            return response()->json(['error' => 'Failed to fetch rates'], 503);
        }
    }
}
