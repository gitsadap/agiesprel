
<?php

ini_set('display_errors', 0);
session_start();

require_once __DIR__ . '/vendor/autoload.php';

use setasign\Fpdi\Fpdi;
use setasign\Fpdf\Fpdf; 
require_once 'connect.php';
require_once './vendor/include/connect.php';

function getThaiDate($date = "now") {
    $thai_months = [
        "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
        "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
    ];

    $timestamp = strtotime($date);
    $day = date("j", $timestamp);
    $month = date("n", $timestamp) - 1;
    $year = date("Y", $timestamp) + 543;

    return "$day {$thai_months[$month]} $year";
}

function getExpireDate() {
    return getThaiDate("+1 year");
}

function convertThai($text) {
    return iconv('UTF-8', 'cp874', $text);
}


try {

    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;
    $path = isset($_POST['path']) ? $_POST['path'] : null;
    $reason = isset($_POST['reason']) ? $_POST['reason'] : null;

    // Clean path and ensure absolute path
    $cleanPath = preg_replace('/^\/?esprel\//', '', $path);
    $cleanPath = ltrim($cleanPath, '/');
    if (substr($cleanPath, 0, 2) === './') {
        $cleanPath = substr($cleanPath, 2);
    }
    $templatePath = __DIR__ . '/' . $cleanPath;

    if (!$templatePath) {
        throw new Exception('ไม่พบ path ของไฟล์ template');
    }
    
    if (!$user_id) {
        throw new Exception('ไม่พบ user_id');
    }

    // ตรวจสอบว่าไฟล์ template มีอยู่จริง
    if (!file_exists($templatePath)) {
        throw new Exception('NU-01-Template PDF not found: ' . $templatePath);
    }

    // กำหนด output path ใหม่
    $outputPath = $templatePath;
  // 

    // สร้าง directory หากไม่มี
    if (!is_dir(dirname($outputPath))) {
        if (!@mkdir(dirname($outputPath), 0777, true)) {
            throw new Exception('Cannot create output NU01 directory');
        }
    }

    // ตรวจสอบสิทธิ์การเขียนไฟล์
    if (!is_writable(dirname($outputPath))) {
        throw new Exception('Output NU01 folder is not writable: ' . dirname($outputPath));
    }

    // สร้าง FPDI instance
    $pdf = new FPDI();
    $pdf->AddPage();

    // Import template
    $pdf->setSourceFile($templatePath);
    $tplIdx = $pdf->importPage(1);
    $pdf->useTemplate($tplIdx, 0, 0, 210);

    // ตั้งค่าฟอนต์
    $pdf->AddFont('THSarabunNew', '', 'THSarabunNew.php');
    $pdf->SetFont('THSarabunNew', '', 16);
    $pdf->SetTextColor(0, 0, 0);


    
    // เพิ่มวันที่
    $pdf->SetXY(115, 254);
    $pdf->Write(0, convertThai(getThaiDate()));

    // เพิ่มข้อความหมดอายุ
    $pdf->SetXY(40, 267);
    $pdf->SetFont('THSarabunNew', '', 20);
    $pdf->Write(0, convertThai('ไม่อนุมัติ เนื่องจาก'.$reason));
    $pdf->SetFont('THSarabunNew', '', 16);

    // บันทึกไฟล์ PDF
    $pdf->Output($outputPath, 'F');

    // ตรวจสอบว่าไฟล์ถูกสร้างสำเร็จ
    if (!file_exists($outputPath)) {
        throw new Exception('Error saving NU01: ไม่สามารถสร้างไฟล์ได้');
    }

    // ลบไฟล์ที่แปลงแล้วถ้ามี
    if (file_exists($convertedSignJpg)) {
        unlink($convertedSignJpg);
    }

    // Log success
    file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - NU01 created successfully at: " . $outputPath . "\n", FILE_APPEND);

    // Update database
    $updateQuery = "UPDATE lab_cert SET issue_date = NOW(), status = :status WHERE cert = :user_id";
    $updateStmt = $conn->prepare($updateQuery);

    if ($updateStmt) {
        $expiry_date = date('Y-m-d', strtotime('+1 year')); // ควรเก็บในรูปแบบ Y-m-d ในฐานข้อมูล
        $updateResult = $updateStmt->execute([
            ':user_id' => $user_id,
            ':status' => 7
        ]);

        if ($updateResult && $updateStmt->rowCount() > 0) {
            file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Database updated for user_id: " . $user_id . "\n", FILE_APPEND);
        } else {
            file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Warning: user_id " . $user_id . " not found or no changes made.\n", FILE_APPEND);
        }
    } else {
        file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Error preparing update statement: " . print_r($conn->errorInfo(), true) . "\n", FILE_APPEND);
    }

    // Return success response
    ob_clean();
    echo json_encode(['success' => true, 'message' => 'ไม่อนุมัติ และสร้างเอกสาร NU-01 เรียบร้อยแล้ว', 'file' => $outputPath]);

} catch (Exception $e) {
    // Log the exception
    file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);

    // Clean output buffer and return error
    if (ob_get_contents()) {
        ob_clean();
    }
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
?>

