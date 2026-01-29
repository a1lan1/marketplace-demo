<?php

declare(strict_types=1);

namespace App\Exceptions\Payment;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentException extends Exception
{
    /**
     * Render the exception into an HTTP response.
     */
    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            $statusCode = match (true) {
                is_int($this->code) && $this->code >= 400 && $this->code < 600 => $this->code,
                default => Response::HTTP_BAD_REQUEST,
            };

            return response()->json(['message' => $this->getMessage()], $statusCode);
        }

        return back()->with('error', $this->getMessage());
    }
}
