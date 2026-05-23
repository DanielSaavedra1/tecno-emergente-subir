<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function success(array $data = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            ...$data,
        ], $status);
    }

    public static function error(string $code, string $message, int $status): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ], $status);
    }
}
