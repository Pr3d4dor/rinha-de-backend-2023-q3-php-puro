<?php

declare(strict_types=1);

require_once './redis_connection.php';
require_once './functions.php';

function validateData(array $data, array $rules, \Redis $redis)
{
    $errors = [];

    foreach ($rules as $field => $fieldRules) {
        foreach ($fieldRules as $rule) {
            if (! is_callable($rule)) {
                $ruleParts = explode(':', $rule);
                $ruleName = $ruleParts[0];
                $ruleValue = isset($ruleParts[1]) ? $ruleParts[1] : null;
            }

            switch ($ruleName) {
                case 'required':
                    if (! isset($data[$field])) {
                        $errors[] = "$field is required.";
                    }
                    break;

                case 'string':
                    if (isset($data[$field]) && ! is_string($data[$field])) {
                        $errors[] = "$field invalid type.";
                    }
                    break;

                case 'max':
                    if (isset($data[$field]) && strlen(strval($data[$field])) > $ruleValue) {
                        $errors[] = "$field must be at most $ruleValue characters.";
                    }
                    break;

                case 'date_format':
                    if (isset($data[$field])) {
                        $date = \DateTime::createFromFormat($ruleValue, $data[$field]);
                        if (! $date || $date->format($ruleValue) !== $data[$field]) {
                            $errors[] = "$field must be a valid date in format $ruleValue.";
                        }
                    }
                    break;

                case 'sometimes':
                    if (! isset($data[$field])) {
                        continue 2;
                    }
                    break;

                case 'array':
                    if (isset($data[$field]) && ! is_array($data[$field])) {
                        $errors[] = "$field must be an array.";
                    }
                    break;

                case 'nullable':
                    break;

                case 'redis_unique':
                    if ($redis->get('person:' . $data[$field])) {
                        $errors[] = "Existent nickname!";
                    };
                    break;
                case 'array_of_strings':
                    if (isset($data[$field]) && is_array($data[$field])) {
                        foreach ($data[$field] as $value) {
                            if (isset($value) && ! is_string($value)) {
                                $errors[] = "$field invalid type.";
                            }
                        }
                    }
                    break;
            }
        }
    }

    if (! empty($errors)) {
        return $errors;
    }

    return null;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['error' => 'Only POST requests are allowed.'], 405);
}

$requestData = json_decode(file_get_contents('php://input'), true);
if (! $requestData) {
    sendJsonResponse(['error' => 'Invalid JSON data.'], 400);
}

$redisInstance = RedisConnection::getInstance();
$redis = $redisInstance->getConnection();

$errors = validateData(
    $requestData,
    [
        'apelido' => [
            'required',
            'string',
            'max:32',
            'redis_unique',
        ],
        'nome' => ['required', 'string', 'max:100'],
        'nascimento' => ['required', 'date_format:Y-m-d'],
        'stack' => ['sometimes', 'array', 'nullable', 'array_of_strings'],
    ],
    $redis
);
if ($errors) {
    foreach ($errors as $error) {
        if (strpos($error, 'invalid type') === 0) {
            sendJsonResponse(['error' => $error], 400);
        } else {
            sendJsonResponse(['error' => $error], 422);
        }
    }
}

$uuid = uniqid();

$personData = [
    'uuid' => $uuid,
    'nickname' => $requestData['apelido'],
    'name' => $requestData['nome'],
    'date_of_birth' => $requestData['nascimento'],
    'stack' => json_encode($requestData['stack']),
];

$encodedPersonData = json_encode($personData);

$redis->publish('create:person:' . gethostname(), $encodedPersonData);
$redis->set('person:' . $personData['uuid'], $encodedPersonData);
$redis->set('person:' . $personData['nickname'], $encodedPersonData);

sendJsonResponse(
    ['message' => 'Person inserted successfully.'],
    201,
    [
        'Location' => '/pessoas/' . $uuid
    ]
);
