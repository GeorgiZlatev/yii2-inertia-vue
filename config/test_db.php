<?php

declare(strict_types=1);

use yii\db\Connection;

$driver = getenv('TEST_DB_CONNECTION') ?: 'sqlite';

if ($driver === 'mysql') {
    $host = getenv('TEST_DB_HOST') ?: '127.0.0.1';
    $port = getenv('TEST_DB_PORT') ?: '3306';
    $database = getenv('TEST_DB_DATABASE') ?: 'app_test';
    $charset = getenv('TEST_DB_CHARSET') ?: 'utf8mb4';

    return [
        'class' => Connection::class,
        'dsn' => sprintf('mysql:host=%s;port=%s;dbname=%s', $host, $port, $database),
        'username' => getenv('TEST_DB_USERNAME') ?: 'root',
        'password' => getenv('TEST_DB_PASSWORD') ?: '',
        'charset' => $charset,
    ];
}

return [
    'class' => Connection::class,
    'dsn' => 'sqlite:' . dirname(__DIR__) . '/tests/support/data/test.sqlite',
];
