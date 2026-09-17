<?php
require_once __DIR__ . '/env.php';

// database.php
$host = env('DB_PG_HOST', 'localhost');
$port = env('DB_PG_PORT', '5432');
$dbname = env('DB_PG_DATABASE', 'ESPReL');
$user = env('DB_PG_USER', 'postgres');
$password = env('DB_PG_PASSWORD', '');

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล ESPReL $dbname :" . $e->getMessage());
}

?>