<?php

declare(strict_types=1);

require_once './database_connection.php';
require_once './functions.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendJsonResponse(['error' => 'Only GET requests are allowed.'], 405);
    }

    $searchTerm = isset($_GET['t']) ? $_GET['t'] : '';

    if (empty($searchTerm)) {
        sendJsonResponse(['error' => 'Search term is required.'], 400);
    }

    $dbInstance = DatabaseConnection::getInstance();
    $dbConnection = $dbInstance->getConnection();

    $sql = "SELECT * FROM people
            WHERE name LIKE :term OR nickname LIKE :term OR stack LIKE :term
            LIMIT 50";
    $statement = $dbConnection->prepare($sql);

    $searchParam = '%' . $searchTerm . '%';
    $statement->bindParam(':term', $searchParam);
    $statement->execute();

    $peopleList = $statement->fetchAll(\PDO::FETCH_ASSOC);

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
