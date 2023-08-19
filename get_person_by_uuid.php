<?php

declare(strict_types=1);

require_once './redis_connection.php';
require_once './functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJsonResponse(['error' => 'Only GET requests are allowed.'], 405);
}

$urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', $urlPath);
$uuid = end($pathParts);
if (! $uuid) {
    sendJsonResponse(['error' => 'Missing UUID parameter.'], 400);
}

$redisInstance = RedisConnection::getInstance();
$redis = $redisInstance->getConnection();

$personData = $redis->get('person:' . $uuid);
if (! $personData) {
    sendJsonResponse(['error' => 'Person not found.'], 404);
}

$personData = json_decode($personData, true);

$responseData = [
    'id' => $personData['uuid'],
    'apelido' => $personData['nickname'],
    'nome' => $personData['name'],
    'nascimento' => $personData['date_of_birth'],
    'stack' => json_decode($personData['stack'], true)
];

sendJsonResponse($responseData, 200);
