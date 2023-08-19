<?php

declare(strict_types=1);

$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestPath = $_SERVER['REQUEST_URI'];

// Remove query parameters if present
$requestPath = strtok($requestPath, '?');

// Define routes and corresponding handlers
$routes = [
    'POST /pessoas' => 'handlePostPessoa',
    'GET /pessoas' => 'handleGetPessoas',
    'GET /pessoas/[a-zA-Z0-9\-]+' => 'handleGetPessoaById',
    'GET /contagem-pessoas' => 'handleContagemPessoas'
];

function handlePostPessoa()
{
    include_once './create_person.php';
}

function handleGetPessoas()
{
    include_once './get_people_list.php';
}

function handleGetPessoaById()
{
    include_once './get_person_by_uuid.php';
}

function handleContagemPessoas()
{
    include_once './get_people_count.php';
}

// Find and call the appropriate handler based on the route
foreach ($routes as $route => $handler) {
    [$routeMethod, $routePath] = explode(' ', $route);
    if ($requestMethod === $routeMethod && preg_match('#^' . $routePath . '$#', $requestPath)) {
        $handler();
        break;
    }
}

// If no route matches, return a 404 response
http_response_code(404);
echo '404 Not Found';
