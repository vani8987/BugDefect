<?php

namespace Core;

class SessionManager
{
    public static function configure(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        $driver = self::env('SESSION_DRIVER', 'file');
        $lifetime = self::positiveInt(self::env('SESSION_LIFETIME', '86400'), 86400);
        $sessionName = self::env('SESSION_NAME', 'BUGDEFECTSESSID');

        if ($sessionName !== '') {
            session_name($sessionName);
        }

        ini_set('session.gc_maxlifetime', (string) $lifetime);
        ini_set('session.cookie_lifetime', (string) $lifetime);
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');

        if ($driver === 'redis') {
            self::configureRedis();
        }
    }

    private static function configureRedis(): void
    {
        if (!extension_loaded('redis')) {
            throw new \RuntimeException('Redis session driver requires the php redis extension.');
        }

        $host = self::env('REDIS_HOST', 'redis');
        $port = self::positiveInt(self::env('REDIS_PORT', '6379'), 6379);
        $password = self::env('REDIS_PASSWORD', '');
        $database = self::positiveInt(self::env('REDIS_DATABASE', '0'), 0);
        $prefix = self::env('REDIS_SESSION_PREFIX', 'bugdefect_session:');

        $query = [
            'database' => $database,
            'prefix' => $prefix,
        ];

        if ($password !== '') {
            $query['auth'] = $password;
        }

        ini_set('session.save_handler', 'redis');
        ini_set(
            'session.save_path',
            sprintf('tcp://%s:%d?%s', $host, $port, http_build_query($query))
        );
    }

    private static function env(string $key, string $default = ''): string
    {
        $value = $_ENV[$key] ?? getenv($key);

        return is_string($value) && $value !== '' ? $value : $default;
    }

    private static function positiveInt(string $value, int $default): int
    {
        $intValue = filter_var($value, FILTER_VALIDATE_INT);

        return $intValue !== false && $intValue >= 0 ? (int) $intValue : $default;
    }
}
