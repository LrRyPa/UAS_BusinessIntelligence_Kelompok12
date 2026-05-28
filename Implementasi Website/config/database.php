<?php

return [
    'sqlite' => [
        'driver'   => 'sqlite',
        'database' => __DIR__ . '/../database/mexican_toys_dws.db',
    ],

    'mysql' => [
        'driver'   => 'mysql',
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'database' => 'mexican_toys_dws',
        'username' => 'root',
        'password' => '',
        'charset'  => 'utf8mb4',
    ],
];
