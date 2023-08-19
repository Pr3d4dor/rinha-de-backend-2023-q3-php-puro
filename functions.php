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

if (! function_exists('guidv4')) {
    function guidv4($data = null)
    {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
