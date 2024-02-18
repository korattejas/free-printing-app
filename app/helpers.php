<?php

if (!function_exists('logError')) {
    function logError($controllerName, $functionName, $exception): void
    {
        tap(logger(), function ($logger) use ($controllerName, $functionName, $exception) {
            $logger->error(
                "$controllerName:$functionName: Exception occurred",
                [
                    'error_message' => $exception->getMessage(),
                    'request' => request()->all(),
                    'user' => auth('api')->user(),
                ]
            );
        });
    }
}
