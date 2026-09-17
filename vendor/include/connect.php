<?php
require_once dirname(__DIR__, 2) . '/env.php';

$host = env('DB_MYSQL_HOST', '10.10.58.16');
$port = (int) env('DB_MYSQL_PORT', 3306);
$user = env('DB_MYSQL_USER', 'gitsadap');
$pw = env('DB_MYSQL_PASSWORD', '');
$dbname = env('DB_MYSQL_DATABASE', 'db_user');

// เชื่อมต่อฐานข้อมูลด้วย mysqli
$c = mysqli_connect($host, $user, $pw, $dbname, $port);
if (!$c) {
    die("<h3>ERROR : ไม่สามารถเชื่อมต่อฐานข้อมูลได้: " . mysqli_connect_error() . "</h3>");
}
mysqli_set_charset($c, "utf8"); // ตั้งค่า charset เป็น utf8 เพื่อรองรับภาษาไทย

//echo "เชื่อมต่อฐานข้อมูล '$dbname' สำเร็จ.<br><br>";

?>