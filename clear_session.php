<?php
session_start();

$inactive_time = 3 * 60 * 60; // 3 ชั่วโมง ในหน่วยวินาที
$session_lifetime_key = 'last_activity';

if (isset($_SESSION[$session_lifetime_key]) && (time() - $_SESSION[$session_lifetime_key] > $inactive_time)) {
    // Session หมดอายุ
    session_unset();     // ลบตัวแปร Session ทั้งหมด
    session_destroy();   // ทำลาย Session
    echo json_encode(['cleared' => true, 'message' => 'Session หมดอายุเนื่องจากไม่มีการใช้งาน']);
    exit();
} else {
    // อัปเดตเวลาล่าสุดที่ใช้งาน Session
    $_SESSION[$session_lifetime_key] = time();
    echo json_encode(['cleared' => false]);
    exit();
}
?>