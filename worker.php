<?php

declare(strict_types=1);

require_once './database_connection.php';
require_once './redis_connection.php';
require_once './functions.php';

set_time_limit(0);

$dbInstance = DatabaseConnection::getInstance();
$dbConnection = $dbInstance->getConnection();

$redisInstance = RedisConnection::getInstance();
$redis = $redisInstance->getConnection();

$redis->subscribe(['create:person:' . gethostname()], function ($redis, $channel, $message) use ($dbConnection) {
    $sql = "INSERT INTO people (uuid, nickname, name, date_of_birth, stack)
            VALUES (:uuid, :nickname, :name, :date_of_birth, :stack)";

    $personData = json_decode($message, true);
    if (! $personData) {
        return;
    }

    $statement = $dbConnection->prepare($sql);
    $statement->execute($personData);
});

$redis->close();
