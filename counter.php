<?php
// ตำแหน่งของไฟล์ที่เก็บจำนวนผู้เข้าชม
$counterFile = 'counter.txt';

// ตรวจสอบว่าไฟล์มีอยู่หรือไม่
if (!file_exists($counterFile)) {
    // ถ้าไม่มีให้สร้างไฟล์ใหม่และกำหนดค่าเริ่มต้นเป็น 0
    file_put_contents($counterFile, '0');
}

// อ่านค่าจำนวนผู้เข้าชมจากไฟล์
$counter = (int) file_get_contents($counterFile);

// ตรวจสอบการเรียกผ่าน API พารามิเตอร์
$action = isset($_GET['action']) ? $_GET['action'] : 'get';

// ทำการอัพเดทตามคำสั่ง
if ($action === 'increment') {
    // เพิ่มจำนวนผู้เข้าชม
    $counter++;
    // บันทึกค่าลงในไฟล์
    file_put_contents($counterFile, $counter);
}

// ส่งค่ากลับเป็น JSON
header('Content-Type: application/json');
echo json_encode([
    'counter' => $counter,
    'timestamp' => time(),
    'formatted_time' => date('d/m/Y H:i:s')
]);
?>