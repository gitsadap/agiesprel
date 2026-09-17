<?php
require_once __DIR__ . '/env.php';

// ข้อมูลการเชื่อมต่อ
$host = env('DB_PG_HOST', '10.10.58.21');
$port = env('DB_PG_PORT', '5432');
$dbname = env('DB_PG_DATABASE', 'ESPReL');
$user = env('DB_PG_USER', 'agi');
$password = env('DB_PG_PASSWORD', '');

// ตั้งค่าที่เก็บไฟล์ backup
$backupDir = __DIR__ . '/backup/';
$backupFile = $backupDir . 'backup_' . date('Ymd_His') . '.sql';

// ตรวจสอบว่าโฟลเดอร์ backup มีอยู่หรือยัง
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

// สร้างคำสั่ง pg_dump
$command = "PGPASSWORD=\"$password\" pg_dump -h $host -p $port -U $user -F c -b -v -f \"$backupFile\" $dbname";

// รันคำสั่ง
putenv("PGPASSWORD=$password");
$output = [];
$returnVar = 0;
exec($command, $output, $returnVar);

// ตรวจสอบผลลัพธ์
if ($returnVar === 0) {
    echo "✅ Backup เสร็จสมบูรณ์: $backupFile\n";
} else {
    echo "❌ Backup ล้มเหลว (Code: $returnVar)\n";
    echo implode("\n", $output);
}
?>
