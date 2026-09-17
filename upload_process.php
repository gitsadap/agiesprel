<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'connect.php'; // ควรเชื่อมต่อฐานข้อมูลจริง เช่น MySQL

// กำหนด MIME Types ที่อนุญาต (ป้องกันการอัพโหลดสคริปต์)
$allowed_mimes = [
    'application/pdf',
    'image/jpeg',
    'image/png'
];
$allowed_exts = ['pdf', 'jpg', 'jpeg', 'png'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ป้องกัน IDOR: ใช้รหัสผู้ใช้จากเซสชัน ไม่ให้คนอื่นส่ง POST ปลอมได้
    $user_id = $_SESSION['user_id'] ?? null;
    $upload_type = $_POST['upload_type'] ?? null;

    if (!$user_id) {
        echo json_encode([
            'success' => false,
            'icon' => 'error',
            'title' => 'ไม่ได้รับอนุญาต',
            'text' => 'เซสชันหมดอายุหรือไม่ได้รับอนุญาตให้ทำรายการ'
        ]);
        exit();
    }

    if (!in_array($upload_type, ['elearning', 'nresuan'])) {
        echo json_encode([
            'success' => false,
            'icon' => 'error',
            'title' => 'เกิดข้อผิดพลาด!',
            'text' => 'ประเภทการอัปโหลดไม่ถูกต้อง'
        ]);
        exit();
    }

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

        if ($upload_type === 'elearning') {
            if (isset($_FILES['elearning_file']) && $_FILES['elearning_file']['error'] === UPLOAD_ERR_OK) {
                // ตรวจสอบความปลอดภัยไฟล์
                $tmp_name = $_FILES['elearning_file']['tmp_name'];
                $mimeType = mime_content_type($tmp_name);
                $ext = strtolower(pathinfo($_FILES['elearning_file']['name'], PATHINFO_EXTENSION));

                if (!in_array($mimeType, $allowed_mimes) || !in_array($ext, $allowed_exts)) {
                     throw new Exception("ไม่อนุญาตให้อัปโหลดไฟล์ประเภทนี้ (รองรับเฉพาะ PDF, JPEG, PNG เท่านั้น)");
                }

                // สุ่มชื่อไฟล์ผสม user_id เพื่อป้องกันการเดารายการและ RCE
                $new_filename = uniqid('el_') . '.' . $ext;
                $file_path = "uploads/elearning/" . $user_id . '_' . $new_filename;
                
                if (!move_uploaded_file($tmp_name, $file_path)) {
                    throw new Exception("เกิดข้อผิดพลาดในการบันทึกไฟล์ชั่วคราว");
                }

                $stmt_path = $conn->prepare("INSERT INTO upload_paths (upload_id, file_path) VALUES (:upload_id, :file_path)");
                $stmt_path->bindParam(':upload_id', $upload_id, PDO::PARAM_INT);
                $stmt_path->bindParam(':file_path', $file_path, PDO::PARAM_STR);
                $stmt_path->execute();
            } else {
                throw new Exception("ไฟล์ใบประกาศจากระบบ e-learning ไม่ถูกต้องหรืออัปโหลดล้มเหลว");
            }
        } elseif ($upload_type === 'nresuan') {
            if (isset($_FILES['nresuan_files']) && is_array($_FILES['nresuan_files']['name'])) {
                $num_files = count($_FILES['nresuan_files']['name']);

                if ($num_files !== 7) {
                    throw new Exception("ต้องอัปโหลดไฟล์ใบประกาศให้ครบ 7 องค์ประกอบท่านั้น");
                }

                for ($i = 0; $i < $num_files; $i++) {
                    if ($_FILES['nresuan_files']['error'][$i] === UPLOAD_ERR_OK) {
                        
                        // ตรวจสอบความปลอดภัยไฟล์
                        $tmp_name = $_FILES['nresuan_files']['tmp_name'][$i];
                        $mimeType = mime_content_type($tmp_name);
                        $ext = strtolower(pathinfo($_FILES['nresuan_files']['name'][$i], PATHINFO_EXTENSION));

                        if (!in_array($mimeType, $allowed_mimes) || !in_array($ext, $allowed_exts)) {
                             throw new Exception("พบไฟล์ที่ไม่ได้รับอนุญาต (รองรับเฉพาะ PDF, JPEG, PNG)");
                        }
                        
                        $new_filename = uniqid('nu_') . '.' . $ext;
                        $file_path = "uploads/nresuan/" . $user_id . '_' . $new_filename;
                        
                        if (!move_uploaded_file($tmp_name, $file_path)) {
                             throw new Exception("เกิดข้อผิดพลาดในการบันทึกไฟล์ชั่วคราว");
                        }

                        $stmt_path = $conn->prepare("INSERT INTO upload_paths (upload_id, file_path, file_order) VALUES (:upload_id, :file_path, :file_order)");
                        $stmt_path->bindParam(':upload_id', $upload_id, PDO::PARAM_INT);
                        $stmt_path->bindParam(':file_path', $file_path, PDO::PARAM_STR);
                        $stmt_path->bindValue(':file_order', $i + 1, PDO::PARAM_INT);
                        $stmt_path->execute();
                    } else {
                        throw new Exception("เกิดข้อผิดพลาดระหว่างการอัปโหลดไฟล์ลำดับที่ " . ($i + 1));
                    }
                }
            } else {
                throw new Exception("ไม่มีไฟล์ใบประกาศถูกอัปโหลด");
            }
        }

        $conn->commit();

        echo json_encode([
            'success' => true,
            'icon' => 'success',
            'title' => 'สำเร็จ!',
            'text' => 'บันทึกข้อมูลการอัปโหลดเรียบร้อยแล้ว'
        ]);
    } 
    catch (Exception $e) {
        $conn->rollBack();
        
        // P5: Information Disclosure - Do not echo exact exception into response body 
        // Log original exception if necessary here: error_log($e->getMessage());
        $safe_error_msg = strpos($e->getMessage(), 'ไม่อนุญาตให้อัปโหลด') !== false || strpos($e->getMessage(), 'พบไฟล์ที่ไม่ได้รับอนุญาต') !== false || strpos($e->getMessage(), 'ครบ 7') !== false || strpos($e->getMessage(), 'ไม่ถูกต้อง') !== false ? $e->getMessage() : "เกิดข้อผิดพลาดในระบบ กรุณาติดต่อผู้ดูแลระบบ";
        
        echo json_encode([
            'success' => false,
            'icon' => 'error',
            'title' => 'เกิดข้อผิดพลาด!',
            'text' => $safe_error_msg
        ]);
    }
}

