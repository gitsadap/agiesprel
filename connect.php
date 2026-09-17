<?php
require_once __DIR__ . '/env.php';

$host = env('DB_PG_HOST', '10.10.58.21');
$port = env('DB_PG_PORT', '5432');
$dbname = env('DB_PG_DATABASE', 'ESPReL');
$user = env('DB_PG_USER', 'agi');
$password = env('DB_PG_PASSWORD', '');

// เชื่อมต่อฐานข้อมูล
$conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

?>

