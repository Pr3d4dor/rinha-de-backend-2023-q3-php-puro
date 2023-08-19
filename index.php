<?php

declare(strict_types=1);

$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestPath = $_SERVER['REQUEST_URI'];

$requestPath = strtok($requestPath, '?');

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

foreach ($routes as $route => $handler) {
    [$routeMethod, $routePath] = explode(' ', $route);
    if ($requestMethod === $routeMethod && preg_match('#^' . $routePath . '$#', $requestPath)) {
        $handler();
        break;
    }
}

http_response_code(404);
echo '404 Not Found';
