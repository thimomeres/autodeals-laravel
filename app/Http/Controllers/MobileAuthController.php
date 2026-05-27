<?php

namespace App\Http\Controllers;

use App\Http\Resources\MobileCustomerResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class MobileAuthController extends Controller
{
    private const MOBILE_TOKEN_NAME = 'mobile-app';

    /**
     * POST /api/register — daftar pembeli mobile (tabel customers).
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[\pL\pM\pN\s.\'\-]+$/u'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email'],
            'password' => ['required', 'string', Password::min(8)],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $customer = Customer::create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'password' => $validated['password'],
            'phone' => isset($validated['phone']) ? trim($validated['phone']) : null,
        ]);

        $token = $customer->createToken(self::MOBILE_TOKEN_NAME)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil.',
            'data' => $this->authPayload($customer, $token),
        ], 201);
    }

    /**
     * POST /api/login — autentikasi pembeli mobile (Sanctum token).
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $customer = Customer::where('email', strtolower(trim($validated['email'])))->first();

        if (! $customer || ! Hash::check($validated['password'], $customer->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
            ], 401);
        }

        $customer->tokens()->where('name', self::MOBILE_TOKEN_NAME)->delete();
        $token = $customer->createToken(self::MOBILE_TOKEN_NAME)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => $this->authPayload($customer, $token),
        ], 200);
    }

    /**
     * POST /api/logout — cabut token aktif (Bearer Sanctum).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ], 200);
    }

    private function authPayload(Customer $customer, string $token): array
    {
        return [
            'user' => new MobileCustomerResource($customer),
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }
}
