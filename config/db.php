<?php

declare(strict_types=1);

use yii\db\Connection;

$driver = getenv('DB_CONNECTION') ?: 'mysql';

if ($driver === 'mysql') {
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $database = getenv('DB_DATABASE') ?: 'yii2_inertia_vue_cv';
    $charset = getenv('DB_CHARSET') ?: 'utf8mb4';

    return [
        'class' => Connection::class,
        'dsn' => sprintf('mysql:host=%s;port=%s;dbname=%s', $host, $port, $database),
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => $charset,
    ];
}

return [
    'class' => Connection::class,
    'dsn' => 'sqlite:' . dirname(__DIR__) . '/runtime/db.sqlite',
];
