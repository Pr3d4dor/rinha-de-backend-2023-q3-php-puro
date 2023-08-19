<?php

if (! function_exists('sendResponse')) {
    function sendResponse(string $content, int $statusCode, array $headers = []): void
    {
        foreach ($headers as $header => $value) {
            header(sprintf("%s: %s", $header, $value));
        }

        http_response_code($statusCode);
        echo $content;
        exit;
    }
}

if (! function_exists('sendJsonResponse')) {
    function sendJsonResponse(array $response, int $statusCode, array $headers = []): void
    {
        sendResponse(
            json_encode($response),
            $statusCode,
            array_merge(
                $headers,
                [
                    'Content-Type' => 'application/json'
                ]
            )
        );
    }
}
