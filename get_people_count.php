<?php

declare(strict_types=1);

require_once './database_connection.php';
require_once './functions.php';

try {
    // Check if the request method is GET
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendJsonResponse(['error' => 'Only GET requests are allowed.'], 405);
    }

    // Connect to the database
    $dbInstance = DatabaseConnection::getInstance();
    $dbConnection = $dbInstance->getConnection();

    // Prepare the SQL query to count people
    $sql = "SELECT COUNT(*) FROM people";
    $statement = $dbConnection->prepare($sql);
    $statement->execute();

    $count = $statement->fetchColumn();

    // Send the plain text response
    sendResponse(strval($count), 200, ['Content-Type' => 'text/plain']);
} catch (\PDOException $e) {
    fwrite(fopen('php://stderr', 'wb'), $e->getMessage());
    sendJsonResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
}
