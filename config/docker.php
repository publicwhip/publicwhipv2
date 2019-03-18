<?php
declare(strict_types=1);

$definitions = require __DIR__ . DIRECTORY_SEPARATOR . 'develop.php';
return array_merge(
    $definitions,
    [
        'settings.db' => [
            'driver' => 'mysql',
            'host' => 'mariadb',
            'database' => 'publicwhip-db',
            'username' => 'publicwhip-user',
            'password' => 'password',
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => ''
        ],
        'settings.mail' => [
            'transport' => 'sendmail',
            'host' => 'mailhog',
            'port' => 1025,
            'username' => '',
            'password' => ''
        ]
    ]
);
