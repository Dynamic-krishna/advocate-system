<?php
// 1. A simple function to load variables from your .env file
$env_file = __DIR__ . '/.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Skip comments
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}

// 2. Fetch the variables. 
// NOTE: Make sure these names match EXACTLY what you wrote inside your .env file!
$host     = getenv('HOST');
$dbname   = getenv('DBNAME');
$user     = getenv('USER');
$password = getenv('PASS');
$port     = getenv('DB_PORT') ?: '5432'; // Default to 5432 if port is missing

// Check if host actually loaded to prevent that exact error
if (!$host) {
    die("Error: .env file not loaded or HOST variable is missing.");
}

define('ENCRYPTION_KEY', 'your-secret-key-32-bytes-long!!!');
define('ENCRYPTION_METHOD', 'AES-256-CBC');

// Function to encrypt data
function encryptData($data)
{
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(ENCRYPTION_METHOD));
    $encrypted = openssl_encrypt($data, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

// Function to decrypt data
function decryptData($data)
{
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
}

// 3. The completely fixed DSN string
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

try {
    // Pass the completed $dsn variable into PDO
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $table_sql = "CREATE TABLE IF NOT EXISTS advocate_registration (
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
    )";

    $table_advocate_details = "CREATE TABLE IF NOT EXISTS advocate_details (
        id SERIAL PRIMARY KEY,
        advocate_id INTEGER UNIQUE NOT NULL,
        date_of_birth DATE,
        date_of_enrollment DATE,
        photo_path TEXT,
        FOREIGN KEY (advocate_id) REFERENCES advocate_registration(id) ON DELETE CASCADE
    )";

    $pdo->exec($table_sql);
    $pdo->exec($table_advocate_details);

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // session_start();

    echo "✅ Database connection successful!";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
