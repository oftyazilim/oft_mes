<?php

return [
    // .env: DB_QUERYLOG_ENABLED=true to turn on globally
    'enabled' => env('DB_QUERYLOG_ENABLED', true),

    // Which connections' queries to listen to (null = all)
    'connections' => null, // e.g. ['pgsql', 'sqlsrv']

    // Where to write logs (MSSQL)
    'log_connection' => env('DB_QUERYLOG_CONNECTION', 'sqlsrv'),

    // Skip logging queries that match any of these substrings (case-insensitive)
    'ignore_contains' => [
        'users_logs', // prevent recursion
    ],

    // Max SQL length to store (users_logs.komut is varchar(1000))
    'max_sql_length' => 1000,
];
