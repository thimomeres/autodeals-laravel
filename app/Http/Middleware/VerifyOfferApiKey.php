<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyOfferApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = config('services.offer_api.key');

        if (empty($configuredKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Offer API is not configured. Set OFFER_API_KEY in .env.',
            ], 503);
        }

        $providedKey = (string) $request->header('X-API-Key', '');

        if (! hash_equals($configuredKey, $providedKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing API key.',
            ], 401);
        }

        return $next($request);
    }
}
