<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $upload_type = 'nu';

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

        if (isset($_FILES['nresuan_Files']) && is_array($_FILES['nresuan_Files']['name'])) {
            $num_files = count($_FILES['nresuan_Files']['name']);

            if ($num_files !== 7) {
                throw new Exception("ต้องอัปโหลดไฟล์ใบประกาศให้ครบ 7 องค์ประกอบเท่านั้น");
            }

            for ($i = 0; $i < $num_files; $i++) {
                if ($_FILES['nresuan_Files']['error'][$i] === UPLOAD_ERR_OK) {
                   
                   
                    $filename = basename($_FILES['nresuan_Files']['name'][$i]);
                    $extension  = pathinfo($filename, PATHINFO_EXTENSION);
                    $file_path = "uploads/nresuan/" . $user_id. "_องค์ที่" . $i+1 .".".$extension;


                   
                    if (move_uploaded_file($_FILES['nresuan_Files']['tmp_name'][$i], $file_path)) {
                        $stmt_path = $conn->prepare("INSERT INTO upload_paths (upload_id, file_path, file_order) VALUES (:upload_id, :file_path, :file_order)");
                        $stmt_path->bindParam(':upload_id', $upload_id, PDO::PARAM_INT);
                        $stmt_path->bindParam(':file_path', $file_path, PDO::PARAM_STR);
                        $stmt_path->bindValue(':file_order', $i + 1, PDO::PARAM_INT);
                        $stmt_path->execute();
                    } else {
                        throw new Exception("เกิดข้อผิดพลาดในการย้ายไฟล์ลำดับที่ " . ($i + 1) . " ไปยังโฟลเดอร์ปลายทาง");
                    }
                } else {
                    throw new Exception("เกิดข้อผิดพลาดระหว่างการอัปโหลดไฟล์ลำดับที่ " . ($i + 1) . ". Error Code: " . $_FILES['nresuan_Files']['error'][$i]);
                }
            }
        } else {
            throw new Exception("ไม่มีไฟล์ใบประกาศถูกอัปโหลด");
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