<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ป้องกัน IDOR โดยใช้ Session ID แทน POST
    $user_id = $_SESSION['user_id'] ?? null;

    // ตรวจสอบว่า user_id ถูกต้อง
    if ($user_id === null || !is_numeric($user_id) || $user_id <= 0) {
        echo json_encode([
            'success' => false,
            'icon' => 'error',
            'title' => 'ไม่ได้รับอนุญาต',
            'text' => 'เซสชันหมดอายุหรือไม่พบข้อมูลผู้ใช้'
        ]);
        exit();
    }

    $uploadDir = "uploads/sign/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $p12Dir = "uploads/sign/ce/";
    if (!is_dir($p12Dir)) {
        mkdir($p12Dir, 0755, true);
    }

    $file = $_FILES['sign'] ?? null;
    $p12 = $_FILES['p12_file'] ?? null;

    $allowedTypes = ['image/jpeg', 'image/png'];
    $maxFileSize = 2 * 1024 * 1024; // 2MB

    try {
        $conn->beginTransaction();

        // ไปดึง path เก่ามาเพื่อเตรียมลบก่อน (เผื่ออัพเดท) / หรือเก็บค่า null หากไม่มี
        $stmt_get_old = $conn->prepare("SELECT sign, ce FROM users WHERE user_id = :user_id");
        $stmt_get_old->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_get_old->execute();
        $old_paths = $stmt_get_old->fetch(PDO::FETCH_ASSOC);

        $sign_path = $old_paths['sign'] ?? null;
        $p12_path = $old_paths['ce'] ?? null;

        // อัปโหลดไฟล์ลายเซ็น
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $mimeType = mime_content_type($file['tmp_name']);
            if (!in_array($mimeType, $allowedTypes)) {
                throw new Exception("ประเภทไฟล์ไม่ถูกต้อง อนุญาตเฉพาะ JPEG และ PNG เท่านั้น");
            }

            if ($file['size'] > $maxFileSize) {
                throw new Exception("ขนาดไฟล์ใหญ่เกินไป อนุญาตสูงสุด 2MB");
            }

            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            // ป้องกัน File Enumeration
            $sign_filename = uniqid('sig_') . '.' . $extension;
            $sign_path = $uploadDir . $sign_filename;

            if (!move_uploaded_file($file['tmp_name'], $sign_path)) {
                throw new Exception("เกิดข้อผิดพลาดในการอัปโหลดไฟล์ลายเซ็น");
            }
        }

        // อัปโหลดไฟล์ p12
        if ($p12 && $p12['error'] === UPLOAD_ERR_OK) {
            $p12_extension = strtolower(pathinfo($p12['name'], PATHINFO_EXTENSION));

            if ($p12_extension !== 'p12') {
                throw new Exception("ต้องเป็นไฟล์ .p12 เท่านั้น");
            }

            // ป้องกัน File Enumeration
            $p12_filename = uniqid('ce_') . '.p12';
            $p12_path = $p12Dir . $p12_filename;

            if (!move_uploaded_file($p12['tmp_name'], $p12_path)) {
                throw new Exception("เกิดข้อผิดพลาดในการอัปโหลดไฟล์ใบรับรอง (.p12)");
            }
        }

        // บันทึก path ลงฐานข้อมูล
        $stmt = $conn->prepare("UPDATE users SET sign = :sign_path, ce = :p12_path WHERE user_id = :user_id");
        $stmt->bindParam(':sign_path', $sign_path);
        $stmt->bindParam(':p12_path', $p12_path);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $conn->commit();
        echo json_encode([
            'success' => true,
            'icon' => 'success',
            'title' => 'สำเร็จ!',
            'text' => 'อัปโหลดลายเซ็นและใบรับรองเรียบร้อยแล้ว'
        ]);
    } catch (Exception $e) {
        $conn->rollBack();
        $safe_error_msg = strpos($e->getMessage(), 'ประเภทไฟล์ไม่ถูกต้อง') !== false || strpos($e->getMessage(), 'ขนาดไฟล์ใหญ่เกินไป') !== false || strpos($e->getMessage(), 'ต้องเป็นไฟล์ .p12') !== false ? $e->getMessage() : "เกิดข้อผิดพลาดในระบบ กรุณาติดต่อผู้ดูแลระบบ";

        echo json_encode([
            'success' => false,
            'icon' => 'error',
            'title' => 'เกิดข้อผิดพลาด!',
            'text' => $safe_error_msg
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'icon' => 'warning',
        'title' => 'คำเตือน!',
        'text' => 'ไม่รองรับ Method อื่น'
    ]);
}
?>
