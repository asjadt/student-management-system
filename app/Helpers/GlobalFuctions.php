<?php

if (!function_exists('log_message')) {
    function log_message(mixed $message, string $fileName = 'debug.log'): void
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        $fullPath = storage_path("logs/{$fileName}");

        // Convert non-string messages to JSON
        if (!is_string($message)) {
            $message = json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        file_put_contents($fullPath, "[{$timestamp}] {$message}\n", FILE_APPEND);
    }
}
