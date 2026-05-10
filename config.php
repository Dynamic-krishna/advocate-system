<?php
// Load .env file
$env_file = __DIR__ . '/.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, '#') === 0 || empty($line)) continue;

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        $value = trim($value, '"\'');

        putenv("$name=$value");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

// Database configuration
$host     = getenv('HOST') ?: 'localhost';
$dbname   = getenv('DBNAME') ?: 'advocate_db';
$user     = getenv('USER') ?: 'postgres';
$password = getenv('PASS') ?: '';
$port     = getenv('DB_PORT') ?: '5432';

// Encryption configuration
$ENCRYPTION_KEY = getenv('ENCRYPTION_KEY');
$ENCRYPTION_METHOD = getenv('ENCRYPTION_METHOD') ?: 'AES-256-CBC';

// Encryption functions
function encryptData($data)
{
    global $ENCRYPTION_KEY, $ENCRYPTION_METHOD;
    if (empty($data)) return null;
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($ENCRYPTION_METHOD));
    $encrypted = openssl_encrypt($data, $ENCRYPTION_METHOD, $ENCRYPTION_KEY, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

function decryptData($data)
{
    global $ENCRYPTION_KEY, $ENCRYPTION_METHOD;
    if (empty($data)) return null;
    $decoded = base64_decode($data);
    list($encrypted_data, $iv) = explode('::', $decoded, 2);
    return openssl_decrypt($encrypted_data, $ENCRYPTION_METHOD, $ENCRYPTION_KEY, 0, $iv);
}

// Database connection (NO ECHO HERE!)
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

    // For Render deployment
    if (getenv('RENDER') !== false) {
        $dsn .= ";sslmode=require";
    }

    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create tables if they don't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS advocate_registration (
        id SERIAL PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        password VARCHAR(255) NOT NULL,
        enrollment_number VARCHAR(50) UNIQUE NOT NULL,
        mobile TEXT NOT NULL,
        email TEXT NOT NULL,
        state VARCHAR(100) NOT NULL,
        district VARCHAR(100) NOT NULL,
        pin_code VARCHAR(20) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS advocate_details (
        id SERIAL PRIMARY KEY,
        advocate_id INTEGER UNIQUE NOT NULL,
        date_of_birth DATE,
        date_of_enrollment DATE,
        photograph_path TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (advocate_id) REFERENCES advocate_registration(id) ON DELETE CASCADE
    )");

    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // NO ECHO STATEMENTS HERE!

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
