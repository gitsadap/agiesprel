<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once 'connect.php';

use setasign\Fpdi\Tcpdf\Fpdi;

// 1. ปิด PHP error display และจัดการผ่าน JSON
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// 2. เปิด output buffering เพื่อป้องกัน unexpected output
ob_start();

// 3. ตั้งค่า Content-Type เป็น JSON
header('Content-Type: application/json; charset=utf-8');

function sendJsonResponse($success, $message, $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    
    $response = [
        'success' => $success,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}


// 5. ฟังก์ชันจัดการ Exception เป็น JSON
function handleException($e, $httpCode = 400) {
    error_log('PHP Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    
    sendJsonResponse(
        false, 
        $e->getMessage(),
        [
            'error_type' => 'exception',
            'error_code' => $e->getCode(),
            'file' => basename($e->getFile()),
            'line' => $e->getLine()
        ],
        $httpCode
    );
}

// 6. ฟังก์ชันจัดการ Fatal Error เป็น JSON
function handleFatalError() {
    $error = error_get_last();
    
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (ob_get_length()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        
        echo json_encode([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดร้ายแรงในระบบ',
            'error_type' => 'fatal_error',
            'data' => [
                'error_message' => $error['message'],
                'file' => basename($error['file']),
                'line' => $error['line']
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        exit();
    }
}

// 7. ฟังก์ชันอัปเดตฐานข้อมูล
function updateDatabaseStage($conn, $user_id, $stage) {
    try {
        $updateRequestQuery = "UPDATE request SET stage = :stage WHERE user_id = :user_id";
        $updateRequestStmt = $conn->prepare($updateRequestQuery);
        
        if (!$updateRequestStmt) {
            error_log('Error preparing request update statement: ' . print_r($conn->errorInfo(), true));
            return false;
        }
        
        $updateRequestResult = $updateRequestStmt->execute([
            ':stage' => $stage,
            ':user_id' => $user_id
        ]);
        
        if ($updateRequestResult && $updateRequestStmt->rowCount() > 0) {
            error_log("Database request updated for user_id: {$user_id} - Set stage={$stage}");
            return true;
        } else {
            error_log("Warning: user_id {$user_id} not found or no changes made in request table");
            return false;
        }
        
    } catch (Exception $e) {
        error_log("Database update error: " . $e->getMessage());
        return false;
    }
}


// 9. ลงทะเบียน error handlers
register_shutdown_function('handleFatalError');

try {
    // 10. เริ่ม session หากยังไม่ได้เริ่ม
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // 11. ตรวจสอบ Session Variables
    $requiredSessions = [
        'user_id_researcher' => 'ไม่พบข้อมูล user_id_researcher ใน session กรุณาเข้าสู่ระบบใหม่',
        'p12' => 'ไม่พบรหัสยืนยันตัวตน กรุณากรอกรหัสใหม่',
        'templatePath' => 'ไม่พบข้อมูล template path ใน session',
        'email' => 'ไม่พบข้อมูลอีเมลใน session'
    ];
    
    foreach ($requiredSessions as $key => $errorMsg) {
        if (!isset($_SESSION[$key])) {
            throw new Exception($errorMsg);
        }
    }
    
    // กำหนดตัวแปรจาก session
    $user_id = $_SESSION['user_id_researcher'];
    $p12Password = $_SESSION['p12'];
    $outputPath = $_SESSION['templatePath'];
    $to = $_SESSION['email'];

    $logData = date('Y-m-d H:i:s') . " - : " . $p12Password . "\n";
    file_put_contents('log-iden.log', $logData, FILE_APPEND | LOCK_EX);
    
    // 12. ตรวจสอบและทำความสะอาด Output Path
    if (empty($outputPath)) {
        throw new Exception('Template path ว่างเปล่า');
    }

    // ทำความสะอาด path
    // หาก $outputPath เป็น path แบบ absolute อยู่แล้ว ไม่ต้องเติม __DIR__
    if (strpos($outputPath, __DIR__) !== 0) {
        $cleanPath = preg_replace('/^\/?esprel\//', '', $outputPath);
        $cleanPath = ltrim($cleanPath, '/');
        if (substr($cleanPath, 0, 2) === './') {
            $cleanPath = substr($cleanPath, 2);
        }
        $outputPath = __DIR__ . '/' . $cleanPath;
    }

    // 13. ตรวจสอบไฟล์ template
    if (!file_exists($outputPath)) {
        throw new Exception('ไม่พบไฟล์ template: ' . basename($outputPath));
    }

    if (!is_writable(dirname($outputPath))) {
        throw new Exception('ไม่มีสิทธิ์เขียนไฟล์ในโฟลเดอร์ template');
    }

    // 14. ตรวจสอบไฟล์ P12
    $p12File = __DIR__ . '/vendor/iden/wisas@nu.ac.th.p12';
    if (!file_exists($p12File)) {
        throw new Exception('ไม่พบไฟล์ใบรับรองดิจิทัล');
    }

    // 15. อ่านและตรวจสอบไฟล์ P12
    $p12Raw = file_get_contents($p12File);
    if ($p12Raw === false) {
        throw new Exception('ไม่สามารถอ่านไฟล์ใบรับรองดิจิทัลได้');
    }

    $p12Data = [];
    $isCertValid = false;
    $signerName = 'Unknown';
    $signerEmail = '';
    $signerOrg = '';
    
    if (openssl_pkcs12_read($p12Raw, $p12Data, $p12Password)) {
        // 16. ตรวจสอบข้อมูลใบรับรอง
        if (isset($p12Data['cert']) && isset($p12Data['pkey'])) {
            $certSubject = openssl_x509_parse($p12Data['cert']);
            if ($certSubject !== false) {
                $signerName = $certSubject['subject']['CN'] ?? 'Unknown';
                $signerEmail = $certSubject['subject']['emailAddress'] ?? '';
                $signerOrg = $certSubject['subject']['O'] ?? '';
                $isCertValid = true;
            }
        }
    } else {
        // หากรหัสผ่านผิดหรือไม่ถูกต้อง ให้ไปต่อโดยไม่เซ็น Digital Signature
        // ไม่ต้องอัปเดต stage กลับไปเป็น 3 แล้ว
        unset($_SESSION['p12']);
    }

    // 17. สร้าง PDF ด้วย TCPDF
    try {
        $pdf = new Fpdi('P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->AddPage();

        // ใช้ Template PDF เดิม
        $pageCount = $pdf->setSourceFile($outputPath);
        if ($pageCount < 1) {
            throw new Exception('ไฟล์ template PDF ไม่มีหน้าให้ใช้งาน');
        }
        
        $templateId = $pdf->importPage(1);
        $pdf->useTemplate($templateId, 0, 0, 210);

    } catch (Exception $e) {
        throw new Exception('เกิดข้อผิดพลาดในการประมวลผล PDF template: ' . $e->getMessage());
    }

    if ($isCertValid) {
        $ca_cert_path = __DIR__.'/vendor/iden/TUCTrustedCert.pem'; 
        $info = [
            'Name' => $signerName,
            'Location' => $signerOrg,
            'Reason' => 'ลงนามเอกสาร NU-LAB-01',
            'ContactInfo' => $signerEmail,
        ];

        // 19. ลงลายเซ็นดิจิทัล
        try {
            $pdf->setSignature(
                $p12Data['cert'],
                $p12Data['pkey'],
                $p12Password,
                $ca_cert_path,
                2,
                $info,
                ''
            );
        } catch (Exception $e) {
            throw new Exception('เกิดข้อผิดพลาดในการลงลายเซ็นดิจิทัล: ' . $e->getMessage());
        }
    }

    // 20. บันทึก PDF
    try {
        $pdf->Output($outputPath, 'F');
        
        // ตรวจสอบว่าไฟล์ถูกสร้างจริง
        if (!file_exists($outputPath) || filesize($outputPath) === 0) {
            throw new Exception('ไม่สามารถบันทึกไฟล์ PDF ได้');
        }
        
    } catch (Exception $e) {
        throw new Exception('เกิดข้อผิดพลาดในการบันทึกไฟล์ PDF: ' . $e->getMessage());
    }

   

    $subject = 'จัดส่งใบรับรองนักวิจัยที่ดำเนินงานในห้องปฏิบัติการที่เกี่ยวข้องกับสารเคมี เพื่อใช้ประกอบกำรขอทุนสนับสนุนการวิจัย (NU-LAB-01)';
    $body = '<h4>เรียน นักวิจัย</h4>
            <P>เอกสารใบรับรองนักวิจัย NU-LAB-01 ของท่านได้รับการอนุมัติเรียบร้อยดังไฟล์ที่แนบมาด้วยนี้</P>
            <P>หากเอกสารมีคำผิด หรือต้องการแก้ไขเอกสารดังดล่าว ให้ท่านยื่นขอใบรับรองฯ มาใหม่อีกครั้งตามลิ้งค์ที่แนบมาด้วยนี้</P>
            <a href="https://oassar.agi.nu.ac.th/esprel">คลิกที่นี่</a>
            <p>https://oassar.agi.nu.ac.th/esprel</p>
            <p>------------------------------------------------------</p>
            <p>อีเมล์ฉบับนี้เป็นอีเมล์อัตโนมัติ รบกวนอย่าตอบกลับ หากมีปัญหากรุณาติดต่อ นางสาวหนึ่งฤทัย เทียนทอง 055-962841 ในวัน และเวลาราชการ</p>';
   

    $postData = [
        'to' => $to,
        'subject' => $subject,
        'body' => $body,
        'debug' => '1', // หรือ '0'
    ];
    
    // ไฟล์แนบ
    $filePath =  $outputPath; // เปลี่ยนเส้นทางให้ถูกต้อง
    
    if (file_exists($filePath)) {
        $postData['attachment'] = new CURLFile($filePath);
    } else {
        die("ไม่พบไฟล์แนบ");
    }
    
    // ตั้งค่า URL ที่จะส่งไป
    $url = 'https://oassar.agi.nu.ac.th/esprel/mailing.php'; // เปลี่ยน URL ให้ตรงกับของคุณ
    
    // สร้าง cURL request
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // ตั้งค่า Timeout สั้นๆ เพื่อไม่ให้ระบบต้องรอการส่งอีเมลเสร็จสิ้น
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    
    // ส่ง request
    $response = curl_exec($ch);
    $curlErrorNo = curl_errno($ch);
    
    // ปิด cURL
    curl_close($ch);
    
    // ตรวจสอบข้อผิดพลาด (มองข้าม Timeout เพราะเราต้องการแค่ให้สคริปต์ปลายทางเริ่มทำงาน)
    if ($curlErrorNo && $curlErrorNo !== CURLE_OPERATION_TIMEDOUT) {
        throw new Exception('cURL Error: ' . curl_error($ch));
    } else {
        // ถือว่าทำงานสำเร็จหรือส่ง request ไปยัง server ได้สำเร็จแล้ว
        sendJsonResponse(true, 'อนุมัติและจัดส่งเอกสารให้นักวิจัยเรียบร้อย');
    }


    // 22. ล้าง Session และส่งผลลัพธ์สำเร็จ
    $sessionsToUnset = ['p12', 'user_id_researcher', 'templatePath', 'email'];
    foreach ($sessionsToUnset as $sessionKey) {
        unset($_SESSION[$sessionKey]);
    }

    

} catch (Exception $e) {
    // จัดการ Exception ทั้งหมดเป็น JSON
    handleException($e);

} catch (Error $e) {
    // จัดการ Fatal Error เป็น JSON
    error_log('Fatal Error in Approval: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    
    sendJsonResponse(
        false,
        'เกิดข้อผิดพลาดร้ายแรงในระบบ',
        [
            'error_type' => 'fatal_error',
            'error_message' => $e->getMessage(),
            'file' => basename($e->getFile()),
            'line' => $e->getLine()
        ],
        500
    );

} catch (Throwable $e) {
    // จัดการ Throwable อื่นๆ เป็น JSON
    error_log('Throwable Error in Approval: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    
    sendJsonResponse(
        false,
        'เกิดข้อผิดพลาดที่ไม่คาดคิด',
        [
            'error_type' => 'throwable_error',
            'error_message' => $e->getMessage(),
            'file' => basename($e->getFile()),
            'line' => $e->getLine()
        ],
        500
    );
}

// 23. ปิด output buffer (กรณีที่ไม่มี error)
if (ob_get_length()) {
    ob_end_clean();
}
?>