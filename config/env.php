<?php
class AppEnv {
    private static $loaded = false;

    public static function load($path = null) {
        if (self::$loaded) {
            return;
        }

        $path = $path ?: dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
        if (is_readable($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
                    continue;
                }

                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                if ($value !== '' && (
                    ($value[0] === '"' && substr($value, -1) === '"') ||
                    ($value[0] === "'" && substr($value, -1) === "'")
                )) {
                    $value = substr($value, 1, -1);
                }

                if ($key !== '' && getenv($key) === false) {
                    putenv($key . '=' . $value);
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }

        self::$loaded = true;
    }

    public static function get($key, $default = null) {
        self::load();
        $value = getenv($key);
        return ($value === false || $value === '') ? $default : $value;
    }

    public static function bool($key, $default = false) {
        $value = self::get($key, null);
        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }
}
?>
