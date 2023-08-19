<?php

declare(strict_types=1);

class DatabaseConnection
{
    private static ?DatabaseConnection $instance = null;
    private \PDO $connection;

    private function __construct()
    {
        $dsn = sprintf(
            '%s:host=%s;port=%s;dbname=%s',
            getenv('DB_CONNECTION') ?: 'mysql',
            getenv('DB_HOST') ?: '127.0.0.1',
            getenv('DB_PORT') ?: 3306,
            getenv('DB_NAME') ?: 'rinha',
        );

        if (getenv('DB_CONNECTION') === 'mysql') {
            $dsn .= sprintf(";charset=%s", getenv('DB_CHARSET') ?: 'utf8mb4');
        }

        $username = getenv('DB_USER') ?: 'rinha';
        $password = getenv('DB_PASSWORD') ?: 'rinha';

        $this->connection = new \PDO($dsn, $username, $password, [
            \PDO::ATTR_PERSISTENT => true
        ]);
        $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public static function getInstance(): DatabaseConnection
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnection(): \PDO
    {
        return $this->connection;
    }
}
