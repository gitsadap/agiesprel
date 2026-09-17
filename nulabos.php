<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once 'function.php';

use setasign\Fpdi\Tcpdf\Fpdi;

$user_id = $_SESSION['user_id'] ; 
$p12Password = $_SESSION['p12'] ;
$lab_id = $_SESSION['labid'] ;
$purpose = $_SESSION['purpose'] ;
$phone = $_SESSION['phone'] ;


if (isset($_SESSION['pdf'])) {
    $outputPath = $_SESSION['pdf'];

    if (substr($outputPath, 0, 2) === './') {
        $outputPath = substr($outputPath, 2);
    }
    $outputPath = __DIR__.'/'.$outputPath;
}


$labname = $_SESSION['labname'] ;

$p12 = $conn->prepare("SELECT ce FROM users WHERE user_id = :user_id");
$p12->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$p12->execute();

if ($ce = $p12->fetch(PDO::FETCH_ASSOC)) {
    $p12File = $ce['ce'];

} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'icon' => 'error',
        'title' => 'เกิดข้อผิดพลาด!',
        'text' => $e->getMessage()
    ]);
    exit();
}

  
    $p12Data = [];
    $p12Raw = file_get_contents($p12File);
    if (!openssl_pkcs12_read($p12Raw, $p12Data, $p12Password)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'icon' => 'error', 'title' => 'เกิดข้อผิดพลาด!', 'text' => 'โปรดตรวจสอบรหัสยืนยันตัวตนอีกครั้ง']);
        exit();
       
    }

    // ดึงข้อมูลจากใบรับรอง
    $certSubject = openssl_x509_parse($p12Data['cert']);
    $signerName = $certSubject['subject']['CN'] ?? '';
    $signerEmail = $certSubject['subject']['emailAddress'] ?? '';
    $signerOrg = $certSubject['subject']['O'] ?? '';


        // สร้าง PDF ด้วย TCPDF
        $pdf = new Fpdi('P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->setPrintHeader(false); // ไม่สร้าง Header
        $pdf->setPrintFooter(false); // ไม่สร้าง Footer
        $pdf->SetMargins(0, 0, 0);   // ไม่เว้นระยะขอบ
        $pdf->AddPage();

        // ใช้ Template PDF เดิม
       
        $pageCount = $pdf->setSourceFile($outputPath);
        $templateId = $pdf->importPage(1);
        $pdf->useTemplate($templateId, 0, 0, 210);


        // เตรียมข้อมูลลายเซ็นดิจิทัล
        $info = [
            'Name' => $signerName,
            'Location' => $signerOrg,
            'Reason' => 'ลงนามใบ NU-LAB-02 ของห้อง'.$labname.'เพื่อขอเอกสารรับรองนักวิจัย (NU-LAB-01)',
            'ContactInfo' => $signerEmail,
        ];

        $pdf->setSignature(
            $p12Data['cert'],
            $p12Data['pkey'],
            $p12Password,
            '',
            1,
            $info
        );

        // บันทึก PDF
        

        $pdf->Output($outputPath, 'F');

        
    try {
        $conn->beginTransaction();
        header('Content-Type: application/json');
        $stmt_upload = $conn->prepare("INSERT INTO request (user_id, lab_id,purpose,phone,stage,path) VALUES (:user_id, :lab_id, :purpose, :phone, :stage, :path)");
        $stmt_upload->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_upload->bindParam(':lab_id', $lab_id, PDO::PARAM_STR);
        $stmt_upload->bindParam(':purpose', $purpose, PDO::PARAM_INT);
        $stmt_upload->bindParam(':phone', $phone, PDO::PARAM_STR);
        $stage = 1;
        $stmt_upload->bindParam(':stage', $stage, PDO::PARAM_INT);
        $stmt_upload->bindParam(':path', $_SESSION['pdf'], PDO::PARAM_STR);
        
        $stmt_upload->execute();

        $conn->commit();
        unset($_SESSION['p12']);   // ลบ $_SESSION['p12']
        unset($_SESSION['labid']); // ลบ $_SESSION['labid']
        unset($_SESSION['purpose']); // ลบ $_SESSION['purpose']
        unset($_SESSION['phone']);   // ลบ $_SESSION['phone']
        echo json_encode([
            'success' => true,
            'icon' => 'success',
            'title' => 'สำเร็จ!',
            'text' => 'บันทึกข้อมูลการอัปโหลด NU-LAB-02 เรียบร้อยแล้ว',
            'showRating' => true,
            'user_id' => $user_id
        ]);

    } catch (Exception $e) {
        $conn->rollBack();
            unset($_SESSION['p12']);   // ลบ $_SESSION['p12']
            unset($_SESSION['labid']); // ลบ $_SESSION['labid']
            unset($_SESSION['purpose']); // ลบ $_SESSION['purpose']
            unset($_SESSION['phone']);   // ลบ $_SESSION['phone']
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'icon' => 'error',
            'title' => 'เกิดข้อผิดพลาด!',
            'text' => $e->getMessage()
        ]);
    }
