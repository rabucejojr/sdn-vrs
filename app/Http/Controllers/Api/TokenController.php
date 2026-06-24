<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'token_name' => ['required', 'string', 'max:100'],
        ]);

        $token = $request->user()->createToken($request->token_name);

        return response()->json([
            'token'      => $token->plainTextToken,
            'name'       => $request->token_name,
            'id'         => $token->accessToken->id,
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()->get(['id', 'name', 'last_used_at', 'created_at']);

        return response()->json($tokens);
    }

    public function destroy(Request $request, int $tokenId): JsonResponse
    {
        $request->user()->tokens()->where('id', $tokenId)->delete();

        return response()->json(['message' => 'Token revoked.']);
    }
}
