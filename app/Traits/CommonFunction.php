<?php

namespace App\Traits;

trait CommonFunction
{
    public function sendResponse(int $code, string $message, $data = []): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => true,
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public function sendError($code, string $message): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => false,
            'code' => $code,
            'message' => $message,
        ]);
    }


}
