<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use App\Constants\Api\ApiConstant;

trait ApiResponse
{
    public function __construct(
        private ApiConstant $apiConstant
    ){}

    public function success(?string $message = null, mixed $data = null): JsonResponse
    {
        return response()->json([
            "status" => $this->apiConstant::OK,
            "message" => $message,
            "data" => $data,
        ], 200);
    }

    public function error(string $message, mixed $data = null): JsonResponse
    {
        return response()->json([
            "status" => $this->apiConstant::NG,
            "message" => $message,
            "data" => $data
        ], 500);
    }

    public function badRequest(string $message): JsonResponse
    {
        return response()->json([
            "status" => $this->apiConstant::NG,
            "message" => $message
        ], 400);
    }

    public function unAuthorized(string $message): JsonResponse
    {
        return response()->json([
            "status" => $this->apiConstant::NG,
            "message" => $message,
        ], 401);
    }

    public function notFound(string $message): JsonResponse
    {
        return response()->json([
            "status" => $this->apiConstant::NG,
            "message" => $message,
        ], 404);
    }

    public function forBidden(string $message): JsonResponse
    {
        return response()->json([
            "status" => $this->apiConstant::NG,
            "message" => $message,
        ], 403);
    }
}
