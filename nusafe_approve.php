<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();

require_once __DIR__ . '/vendor/autoload.php';

use setasign\Fpdi\Fpdi;
use setasign\Fpdf\Fpdf; 
require_once 'connect.php';
require_once './vendor/include/connect.php';

$input = json_decode(file_get_contents('php://input'), true);

$path = $input['path'];
$user_id = $input['user_id'];
$P12_password = $input['P12_password'];
$email_to = $input['email'];


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

// ทางเลือกที่ 1: แปลง PNG เป็น JPEG (มีโอกาสสำเร็จสูง)
function convertPngToJpeg($pngPath, $jpegPath) {
    if (!extension_loaded('gd')) {
        return false;
    }
    
    $image = @imagecreatefrompng($pngPath);
    if (!$image) {
        return false;
    }
    
    // แปลงเป็น transparent background เป็นสีขาว
    $width = imagesx($image);
    $height = imagesy($image);
    $white = imagecolorallocate($image, 255, 255, 255);
    $temp = imagecreatetruecolor($width, $height);
    imagefill($temp, 0, 0, $white);
    imagecopy($temp, $image, 0, 0, 0, 0, $width, $height);
    
    // บันทึกเป็น JPEG
    $result = imagejpeg($temp, $jpegPath, 90);
    
    // ล้างหน่วยความจำ
    imagedestroy($image);
    imagedestroy($temp);
    
    return $result && file_exists($jpegPath);
}

try {
    $templatePath = $path ?? null;;
    $user_id = $user_id  ?? null;
    
    if (!$templatePath) {
        throw new Exception('ไม่พบ path ของไฟล์ template');
    }
    
    if (!$user_id) {
        throw new Exception('ไม่พบ user_id');
    }

    // Clean path and ensure absolute path
    $cleanPath = preg_replace('/^\/?esprel\//', '', $templatePath);
    $cleanPath = ltrim($cleanPath, '/');
    if (substr($cleanPath, 0, 2) === './') {
        $cleanPath = substr($cleanPath, 2);
    }
    $templatePath = __DIR__ . '/' . $cleanPath;
    
    // ตรวจสอบว่าไฟล์ template มีอยู่จริง
    if (!file_exists($templatePath)) {
        throw new Exception('NU-01-Template PDF not found: ' . $templatePath);
    }

    // กำหนด output path ใหม่
    $outputPath = $templatePath;
    // ใช้แบบเดิม (ไม่บังคับ pantipk สำหรับ NU-LAB-01 ในขั้นนี้)
    $originalSign = __DIR__ . '/sign/wisa.png';
    
    // ตรวจสอบไฟล์ลายเซ็น
    if (!file_exists($originalSign)) {
        throw new Exception('NU-01-Signature image not found: ' . $originalSign);
    }

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

    $pdf->Image($originalSign, 115, 225, 45, 0);

    
    // เพิ่มวันที่
    $pdf->SetXY(115, 254);
    $pdf->Write(0, convertThai(getThaiDate()));

    // เพิ่มข้อความหมดอายุ
    $pdf->SetXY(60, 267);
    $pdf->SetFont('THSarabunNew', '', 18);
    $pdf->Write(0, convertThai('ใบรับรองฉบับนี้มีอายุ 1 ปี (หมดอายุ ' . getExpireDate() . ')'));
    $pdf->SetFont('THSarabunNew', '', 16);

    // บันทึกไฟล์ PDF
    $pdf->Output($outputPath, 'F');

    // ตรวจสอบว่าไฟล์ถูกสร้างสำเร็จ
    if (!file_exists($outputPath)) {
        throw new Exception('Error saving NU01: ไม่สามารถสร้างไฟล์ได้');
    }

    // ลบไฟล์ที่แปลงแล้วถ้ามี

    // Log success
    file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - NU01 created successfully at: " . $outputPath . "\n", FILE_APPEND);

    // Update database
    $updateQuery = "UPDATE lab_cert SET issue_date = NOW(), expiry_date = :expiry_date, status = :status WHERE cert = :user_id";
    $updateStmt = $conn->prepare($updateQuery);

    if ($updateStmt) {
        $expiry_date = date('Y-m-d', strtotime('+1 year')); // ควรเก็บในรูปแบบ Y-m-d ในฐานข้อมูล
        $updateResult = $updateStmt->execute([
            ':user_id' => $user_id,
            ':expiry_date' => $expiry_date,
            ':status' => 6
        ]);

        if ($updateResult && $updateStmt->rowCount() > 0) {
            file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Database updated for user_id: " . $user_id . "\n", FILE_APPEND);
        } else {
            file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Warning: user_id " . $user_id . " not found or no changes made.\n", FILE_APPEND);
        }
    } else {
        file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Error preparing update statement: " . print_r($conn->errorInfo(), true) . "\n", FILE_APPEND);
    }


   

    $updateRequestQuery = "UPDATE request SET stage = 6 WHERE user_id = :user_id";
    $updateRequestStmt = $conn->prepare($updateRequestQuery);

    if ($updateRequestStmt) {
        $updateRequestResult = $updateRequestStmt->execute([
            ':user_id' => $user_id
        ]);
        
        if ($updateRequestResult && $updateRequestStmt->rowCount() > 0) {
            file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Database request updated for user_id: " . $user_id . " - Set stage=6\n", FILE_APPEND);
        } else {
            file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Warning: user_id " . $user_id . " not found or no changes made in request table.\n", FILE_APPEND);
        }
    } else {
        file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Error preparing request update statement: " . print_r($conn->errorInfo(), true) . "\n", FILE_APPEND);
    }

    try{
        $_SESSION['user_id_researcher'] = $user_id;
        $_SESSION['templatePath'] =  $templatePath;
        $_SESSION['p12'] =  $P12_password ;
        $_SESSION['email'] =  $email_to ;
        header('Location: nusafece.php');
        exit();
         
    }catch (Exception $e) {
        file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - nulab01-nusafeBoard sending data error " . $e->getMessage() . "\n", FILE_APPEND);
        // Don't exit here, we still want to report PDF success even if DB update fails
    }
    

} catch (Exception $e) {
    // Log the exception
    file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);

    // Clean output buffer and return error
    if (ob_get_contents()) {
        ob_clean();
    }
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดหน้า nusafe_approve: ' . $e->getMessage()]);
}
?>
