<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Koneksi Database Sumber (SQL Server via ODBC)
    |--------------------------------------------------------------------------
    |
    | Konfigurasi ini digunakan untuk menyambungkan aplikasi ke database
    | sumber (SQL Server) di host terpisah melalui driver ODBC "ODBC Driver
    | 11 for SQL Server", menggunakan SQL Server Authentication (username
    | & password). Membutuhkan ekstensi PHP `pdo_odbc` aktif dan driver
    | ODBC tersebut ter-install di server aplikasi ini.
    |
    */

    'driver' => env('MDB_DRIVER', 'ODBC Driver 11 for SQL Server'),

    'host' => env('MDB_HOST', ''),

    'port' => env('MDB_PORT', '1433'),

    'database' => env('MDB_DATABASE', ''),

    'username' => env('MDB_USERNAME', ''),

    'password' => env('MDB_PASSWORD', ''),

];
