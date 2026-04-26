<?php
declare(strict_types=1);

function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        throw new RuntimeException(".env file not found at: {$path}");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip comments and blank lines
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $line, 2));

        // Strip surrounding quotes if present
        $value = trim($value, '"\'');

        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
}

/**
 * Helper: fetch a value from $_ENV with an optional default.
 */
function env(string $key, mixed $default = null): mixed
{
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

// ── Boot ─────────────────────────────────────────────────────
$envPath = dirname(__DIR__) . '/.env';
loadEnv($envPath);

// ── Connection factory ────────────────────────────────────────
/**
 * Returns a live, error-checked mysqli connection.
 *
 * @param string $prefix  ENV key prefix, e.g. 'DB' or 'DB_STUDENT'
 */
function getConnection(string $prefix = 'DB'): mysqli
{
    $host     = env("{$prefix}_HOST",     '127.0.0.1');
    $port     = (int) env("{$prefix}_PORT", 3306);
    $database = env("{$prefix}_DATABASE", '');
    $username = env("{$prefix}_USERNAME", 'root');
    $password = env("{$prefix}_PASSWORD", '');

    $conn = new mysqli($host, $username, $password, $database, $port);

    if ($conn->connect_error) {
        http_response_code(500);
        error_log("[DB] Connection failed ({$prefix}): " . $conn->connect_error);
        die(json_encode(['error' => 'Database connection failed. Check server logs.']));
    }

    $conn->set_charset('utf8mb4');

    return $conn;
}

// ── Pre-wired connections ─────────────────────────────────────
/** Main portfolio DB */
function portfolioDb(): mysqli
{
    return getConnection('DB');
}

/** Student profile DB */
function studentDb(): mysqli
{
    return getConnection('DB_STUDENT');
}