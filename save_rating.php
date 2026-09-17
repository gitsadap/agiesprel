<?php
// save_rating.php - แบบเรียบง่ายแต่ครบถ้วน
// ปิดการแสดงข้อผิดพลาดบนหน้าเว็บ
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// ตั้งค่า Content-Type เป็น JSON
header('Content-Type: application/json');

try {
    // รับค่า POST
    $score = isset($_POST['score']) ? intval($_POST['score']) : null;
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;
    
    // ตรวจสอบว่าข้อมูลครบถ้วน
    if ($score === null || $user_id === null) {
        throw new Exception("ข้อมูลไม่ครบถ้วน - ต้องระบุ score และ user_id");
    }
    
    // ตรวจสอบว่าค่า score อยู่ในช่วง 1-5
    if ($score < 1 || $score > 5) {
        throw new Exception("คะแนนต้องอยู่ระหว่าง 1-5");
    }
    
    // เชื่อมต่อฐานข้อมูล
    require_once __DIR__ . '/connect.php';

    $table_check = $conn->query("SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'satisfy'
    )");
    
    $table_exists = $table_check->fetchColumn();
    
    if (!$table_exists) {
        // สร้างตาราง satisfy
        $conn->exec("
            CREATE TABLE satisfy (
                id SERIAL PRIMARY KEY,
                user_id INTEGER NOT NULL,
                score INTEGER NOT NULL CHECK (score >= 1 AND score <= 5),
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }
    
    // เริ่ม transaction
    $conn->beginTransaction();
    
    // เตรียมและประมวลผลคำสั่ง SQL
    $stmt = $conn->prepare("INSERT INTO satisfy (user_id, score) VALUES (:user_id, :score)");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':score', $score, PDO::PARAM_INT);
    
    $result = $stmt->execute();
    
    if (!$result) {
        throw new Exception("การบันทึกล้มเหลว");
    }
    
    // ยืนยันการทำรายการ
    $conn->commit();
    
    // ส่งผลลัพธ์สำเร็จกลับ
    echo json_encode([
        'success' => true,
        'message' => 'บันทึกคะแนนความพึงพอใจเรียบร้อยแล้ว'
    ]);
    
} catch (Exception $e) {
    // กรณีเกิดข้อผิดพลาด
    error_log("Error in save_rating.txt: " . $e->getMessage());
    
    // ยกเลิกการทำรายการในฐานข้อมูล
    if (isset($conn) && $conn instanceof PDO && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    // ส่งข้อความผิดพลาดกลับในรูปแบบ JSON
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
?>