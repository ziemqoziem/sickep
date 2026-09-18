<?php

namespace App\Services;

use PDO;

class MdbConnection
{
    /**
     * Open a PDO ODBC connection to the source SQL Server using the
     * currently configured settings (config/mdb.php, backed by .env
     * and editable via the Setting Koneksi DB page).
     */
    public static function connect(): PDO
    {
        $dsn = sprintf(
            'odbc:Driver={%s};Server=%s,%s;Database=%s;Uid=%s;Pwd=%s;',
            config('mdb.driver'),
            config('mdb.host'),
            config('mdb.port'),
            config('mdb.database'),
            static::escapeOdbcValue((string) config('mdb.username')),
            static::escapeOdbcValue((string) config('mdb.password'))
        );

        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    /**
     * Wrap a DSN value in braces so ODBC tolerates special characters
     * (spaces, semicolons, etc.) inside usernames/passwords.
     */
    public static function escapeOdbcValue(string $value): string
    {
        return '{'.str_replace('}', '}}', $value).'}';
    }
}
