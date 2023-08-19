<?php

declare(strict_types=1);

class RedisConnection
{
    private static ?RedisConnection $instance = null;
    private \Redis $connection;

    private function __construct()
    {
        $redisHost = getenv('REDIS_HOST') ?: '127.0.0.1';
        $redisPort = getenv('REDIS_PORT') ?: 6379;

        $this->connection = new \Redis();
        $this->connection->connect($redisHost, intval($redisPort));
    }

    public static function getInstance(): RedisConnection
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnection(): \Redis
    {
        return $this->connection;
    }
}
