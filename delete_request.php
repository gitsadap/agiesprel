<?php
// ตั้งค่า Header เป็น JSON
header('Content-Type: application/json');
require_once 'function.php';
// ตรวจสอบว่า Method เป็น POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ตรวจสอบว่ามี user_id ส่งมาหรือไม่
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        try {    
            $sql = "DELETE FROM request WHERE user_id = :user_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT); // หรือ PDO::PARAM_STR ถ้า user_id เป็น varchar
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                // ลบข้อมูลสำเร็จ
                try{
                    $sql = "DELETE FROM lab_cert WHERE cert = :user_id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT); // หรือ PDO::PARAM_STR ถ้า user_id เป็น varchar
                    $stmt->execute();
        
                    if ($stmt->rowCount() > 0) {
                        // ลบข้อมูลสำเร็จ
                        echo json_encode(['success' => true, 'message' => 'เอกสารถูกยกเลิกเรียบร้อยแล้ว']);
                    } else {
                        // ไม่พบเอกสารที่ต้องการลบ
                        echo json_encode(['success' => true, 'message' => 'ยกเลิกเอกสาร NU02 เเล้ว ยังไม่มีเอกสาร NU01 ในระบบ']);
                    }
                    
                } catch (PDOException $e) {
                    // เกิดข้อผิดพลาดในการเชื่อมต่อหรือการ Query
                    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการลบข้อมูล NU01: ' . $e->getMessage()]);
                }

            } else {
                // ไม่พบเอกสารที่ต้องการลบ
                echo json_encode(['success' => false, 'message' => 'ไม่พบเอกสารที่ตรงกับ User ID นี้']);
            }

        } catch (PDOException $e) {
            // เกิดข้อผิดพลาดในการเชื่อมต่อหรือการ Query
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการลบข้อมูลใบ NU02: ' . $e->getMessage()]);
        }
     
        // ปิดการเชื่อมต่อ
        $conn = null;

    } else {
        // ไม่มีการส่ง user_id มา
        echo json_encode(['success' => false, 'message' => 'ไม่ได้ระบุ User ID ที่ต้องการยกเลิก']);
    }
} else {
    // Method ไม่ใช่ POST
    echo json_encode(['success' => false, 'message' => 'Method ไม่ถูกต้อง (ควรเป็น POST)']);
}
?>