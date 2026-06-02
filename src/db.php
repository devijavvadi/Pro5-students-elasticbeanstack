<?php
// Fetches the values injected by Terraform from the server environment
$host     = getenv('DB_HOST') ?: '127.0.0.1';
$dbname   = getenv('DB_NAME') ?: 'school_db';
$username = getenv('DB_USER') ?: 'admin';
$password = getenv('DB_PASS') ?: 'Geethanshi1234'; // Matches your local fallback

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // Graceful error message without leaking secure credentials
    die("Database connection failed. Please check your configuration my change.");
}
