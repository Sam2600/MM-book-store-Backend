<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    public function success(?string $message = null, mixed $data = null): JsonResponse
    {
        return response()->json([
            "status" => "OK",
            "form_access" => true,
            "data" => $data,
            "message" => $message
        ], 200);
    }

    public function error(string $message, mixed $data = null): JsonResponse
    {
        return response()->json([
            "status" => "NG",
            "message" => $message,
            "data" => $data
        ], 500);
    }

    public function badRequest(string $message): JsonResponse
    {
        return response()->json([
            "status" => "NG",
            "message" => $message
        ], 400);
    }

    public function unAuthorized(string $message): JsonResponse
    {
        return response()->json([
            "status" => "NG",
            "message" => $message,
        ], 401);
    }

    public function notFound(string $message): JsonResponse
    {
        return response()->json([
            "status" => "NG",
            "message" => $message,
        ], 404);
    }

    public function forBidden(string $message): JsonResponse
    {
        return response()->json([
            "status" => "NG",
            "message" => $message,
        ], 403);
    }
}
