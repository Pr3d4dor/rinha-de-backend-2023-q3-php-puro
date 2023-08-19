<?php

declare(strict_types=1);

require_once './database_connection.php';
require_once './functions.php';

try {
    // Check if the request method is GET
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendJsonResponse(['error' => 'Only GET requests are allowed.'], 405);
    }

    // Get the search term from the query parameter
    $searchTerm = isset($_GET['t']) ? $_GET['t'] : '';

    // Return 400 Bad Request if the search term is empty
    if (empty($searchTerm)) {
        sendJsonResponse(['error' => 'Search term is required.'], 400);
    }

    // Connect to the database
    $dbInstance = DatabaseConnection::getInstance();
    $dbConnection = $dbInstance->getConnection();

    // Prepare the SQL query with LIKE operator for search
    $sql = "SELECT * FROM people
            WHERE name LIKE :term OR nickname LIKE :term OR stack LIKE :term
            LIMIT 50";
    $statement = $dbConnection->prepare($sql);

    // Bind parameters and execute the query
    $searchParam = '%' . $searchTerm . '%';
    $statement->bindParam(':term', $searchParam);
    $statement->execute();

    $peopleList = $statement->fetchAll(\PDO::FETCH_ASSOC);

    // Prepare the response data
    $responseData = [];
    foreach ($peopleList as $personData) {
        $responseData[] = [
            'id' => $personData['uuid'],
            'apelido' => $personData['nickname'],
            'nome' => $personData['name'],
            'nascimento' => $personData['date_of_birth'],
            'stack' => json_decode($personData['stack'], true)
        ];
    }

    sendJsonResponse($responseData, 200);
} catch (\PDOException $e) {
    fwrite(fopen('php://stderr', 'wb'), $e->getMessage());
    sendJsonResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
}
