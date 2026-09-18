<?php

namespace App\Services;

class EnvironmentWriter
{
    /**
     * Update (or append) one or more keys in the .env file.
     *
     * @param  array<string, string>  $values
     */
    public static function update(array $values): void
    {
        $path = base_path('.env');

        $content = file_exists($path) ? file_get_contents($path) : '';

        foreach ($values as $key => $value) {
            $line = $key.'='.static::formatValue($value);

            if (preg_match('/^'.preg_quote($key, '/').'=.*$/m', $content)) {
                $content = preg_replace('/^'.preg_quote($key, '/').'=.*$/m', $line, $content);
            } else {
                $content = rtrim($content)."\n".$line."\n";
            }
        }

        file_put_contents($path, $content);
    }

    protected static function formatValue(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (preg_match('/\s|#|"/', $value)) {
            return '"'.str_replace('"', '\\"', $value).'"';
        }

        return $value;
    }
}
