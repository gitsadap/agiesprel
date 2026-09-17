<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'connect.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $upload_type = 'elearning'; 

    try {
        $conn->beginTransaction();
        $stmt_upload = $conn->prepare("INSERT INTO uploads (user_id, upload_type) VALUES (:user_id, :upload_type)");
        $stmt_upload->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_upload->bindParam(':upload_type', $upload_type, PDO::PARAM_STR);
        $stmt_upload->execute();
        $upload_id = $conn->lastInsertId();

        if (!$upload_id) {
            throw new Exception("ไม่สามารถบันทึกข้อมูลการอัปโหลดหลักได้");
        }

        if (isset($_FILES['elearning_file']) && $_FILES['elearning_file']['error'] === UPLOAD_ERR_OK) {
            $filename = basename($_FILES['elearning_file']['name']);
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
         
            $new_filename = $user_id . '.' . $extension;
            $file_path = "uploads/elearning/" . $new_filename;

            if (move_uploaded_file($_FILES['elearning_file']['tmp_name'], $file_path)) {
                $stmt_path = $conn->prepare("INSERT INTO upload_paths (upload_id, file_path) VALUES (:upload_id, :file_path)");
                $stmt_path->bindParam(':upload_id', $upload_id, PDO::PARAM_INT);
                $stmt_path->bindParam(':file_path', $file_path, PDO::PARAM_STR);
                $stmt_path->execute();
            } else {
                throw new Exception("เกิดข้อผิดพลาดในการย้ายไฟล์ไปยังโฟลเดอร์ปลายทาง"); 
            }
        } else {
            throw new Exception("อัปโหลดไฟล์ elearning ล้มเหลว");
        }

        $conn->commit();
        echo json_encode([
            'success' => true,
            'icon' => 'success',
            'title' => 'สำเร็จ!',
            'text' => 'บันทึกข้อมูลการอัปโหลดเรียบร้อยแล้ว'
        ]);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode([
            'success' => false,
            'icon' => 'error',
            'title' => 'เกิดข้อผิดพลาด!',
            'text' => $e->getMessage()
        ]);
    }
}

?>