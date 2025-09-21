<?php



$env = [];

// Load from .env file if exists
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // skip comments
        [$name, $value] = array_map('trim', explode('=', $line, 2));
        $env[$name] = trim($value, "\"'");
    }
}

// Fallback to system environment variables
$getEnv = function($key, $default = null) use ($env) {
    if (isset($env[$key])) {
        return $env[$key];
    }
    $val = getenv($key);
    return $val !== false ? $val : $default;
};

// Laravel default DB envs
$driver = $getEnv('DB_CONNECTION', 'mysql');
$host   = $getEnv('DB_HOST', '127.0.0.1');
$port   = $getEnv('DB_PORT', $driver === 'pgsql' ? 5432 : 3306);
$db     = $getEnv('DB_DATABASE', 'laravel');
$user   = $getEnv('DB_USERNAME', 'root');
$pass   = $getEnv('DB_PASSWORD', '');

echo "Checking {$driver} connection to {$host}:{$port}/{$db}...\n";

try {
    switch ($driver) {
        case 'pgsql':
            $dsn = "pgsql:host=$host;port=$port;dbname=$db";
            break;
        case 'sqlsrv':
            $dsn = "sqlsrv:Server=$host,$port;Database=$db";
            break;
        case 'sqlite':
            $dsn = "sqlite:$db";
            break;
        default: // mysql
            $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    }

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    echo "✅ Database connection successful!\n";
} catch (Exception $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
}


phpinfo();


