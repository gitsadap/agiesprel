<?php

require_once './vendor/include/connect.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once 'function.php';

use setasign\Fpdi\Fpdi;
use setasign\Fpdf\Fpdf; 

// request.php is called via AJAX (expects JSON). Avoid leaking HTML notices/warnings.
ini_set('display_errors', '0');

$loggedInUserId = $_SESSION['user_id'] ?? null; 

if (!$loggedInUserId) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'icon' => 'error', 'title' => 'ไม่ได้รับอนุญาต', 'text' => 'เซสชันหมดอายุหรือไม่พบข้อมูลผู้ใช้']);
    exit();
}

$lab_id = $_POST['labselect'] ?? '';
$purpose = $_POST['purpose'] ?? '';
// บังคับใช้ user_id จาก session แทนเพื่อป้องกัน IDOR
$user_id = $loggedInUserId;
    $phone = $_POST['phone'] ?? '';
    $head = $_POST['head'] ?? '';
    $dr = $_POST['dr'] ?? '';
    $sign = getSignStatus($conn, $loggedInUserId);
    $POS2 = $POS ?? 'นักวิทยาศาสตร์';
    $p12Password = $_POST['p12_password'] ?? '';
    
    $query = "SELECT * FROM lab WHERE id = :lab_id";
    $stmt = $conn->prepare($query);
    $stmt->execute(['lab_id' => $lab_id]);

    $lab = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lab) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'icon' => 'error', 'title' => 'เกิดข้อผิดพลาด!', 'text' => 'ไม่พบข้อมูลห้องปฏิบัติการ']);
        exit();
    }

    $dict = $lab['dep'] ?? '';
    $queryDep = "SELECT dep FROM department WHERE code = :dict";
    $stmtDep = $conn->prepare($queryDep);
    $stmtDep->execute(['dict' => $dict]);
    $dep = $stmtDep->fetch(PDO::FETCH_ASSOC);

  


    function getThaiDate($date = "now") {
        $thai_months = [
            "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
            "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
        ];

        $timestamp = strtotime($date);
        $day = date("j", $timestamp);
        $month = date("n", $timestamp) - 1; // index array เริ่มจาก 0
        $year = date("Y", $timestamp) + 543; // แปลง ค.ศ. เป็น พ.ศ.

        return "$day {$thai_months[$month]} $year";
    }



    $user_type = $_SESSION['user_type'] ?? '';

    if ($user_type === 'student') {
        $fullName = ($_SESSION["std_prefix"] ?? '') . ($_SESSION["std_firstname"] ?? '') . " " . ($_SESSION["std_lastname"] ?? '');
        $userPos = "นิสิต"; 
        $userDepartment = $_SESSION["std_department"] ?? 'คณะเกษตรศาสตร์ฯ';
        $userEmail = !empty($_SESSION["STDMAIL"]) ? $_SESSION["STDMAIL"] . '@nu.ac.th' : '';
    } else {
        $fullName = htmlspecialchars($dr ?? '') . htmlspecialchars($row['fname'] ?? '') . ' ' . htmlspecialchars($row['lname'] ?? '');
        $userPos = $POS2 ?? 'นักวิทยาศาสตร์';
        $userDepartment = $DEP ?? 'คณะเกษตรศาสตร์ฯ';
        $userEmail = htmlspecialchars($row['email'] ?? '');
    }

    $data = [
        'name' => $fullName,
        'position' => $userPos,
        'department' => $userDepartment,
        'phone' => $phone,
        'email' => $userEmail,
        'lab_name' => $lab['name'] ?? '',
        'lab_id' => $lab['license'] ?? '',
        'ldepartment' => $dep['dep'] ?? '', // ภาควิชาของห้องปฏิบัติการ ดึงจากฐานข้อมูล
        'head' => $lab['manager'] ?? '',
        'check' => './vendor/image/check.png',
        'fac' => 'คณะเกษตรศาสตร์ฯ', 
        'nu' => 'มหาวิทยาลัยนเรศวร',
        'mhesi' => 'กระทรวงการอุดมศึกษา วิทยาศาสตร์ วิจัยและนวัตกรรม',
        'purpose' => 'วิจัย',
        'signature' => $sign
    ];

    $templatePath = 'NU-LAB-02.pdf';
    // Word-exported PDF can use XRef streams / object streams that FPDI free parser cannot read.
    // We keep a rewritten template (classic xref) for FPDI to import.
    $templatePathFpdi = 'NU-LAB-02-fpdi-rewritten.pdf';
    if (is_file($templatePathFpdi)) {
        $templatePath = $templatePathFpdi;
    }
    $outputPath = './export/02/NU-LAB-02-'.$lab['room'].'-'.$loggedInUserId.'.pdf';

    $pdf = new FPDI();

    try {
        $pdf->AddPage();

        $pdf->setSourceFile($templatePath);
        $tplIdx = $pdf->importPage(1);
        $pdf->useTemplate($tplIdx, 0, 0, 210);
    } catch (Throwable $e) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'icon' => 'error',
            'title' => 'เกิดข้อผิดพลาด!',
            'text' => 'ไม่สามารถอ่านไฟล์ Template PDF ได้: ' . $e->getMessage()
        ]);
        exit();
    }

    $pdf->AddFont('THSarabunNew', '', 'THSarabunNew.php');
    $pdf->SetFont('THSarabunNew', '', 16);
    $pdf->SetTextColor(0, 0, 0);

    function convertThai($text) {
        return iconv('UTF-8', 'cp874', $text);
    }



    $pdf->SetXY(29, 48);
    $pdf->Write(0, convertThai($data['name']));

    $pdf->SetXY(98, 48);
    $pdf->Write(0, convertThai($data['position']));

    $pdf->SetXY(146, 48);
    $pdf->Write(0, convertThai($data['department']));

    $pdf->SetXY(30, 55);
    $pdf->Write(0, convertThai($data['phone']));

    $pdf->SetXY(82, 55);
    $pdf->Write(0, convertThai($data['email']));

    $pdf->SetXY(45, 63);
    $pdf->Write(0, convertThai($data['lab_name']));

    $pdf->SetXY(46, 70);
    $pdf->Write(0, convertThai($data['ldepartment']));

    $pdf->SetXY(132, 70);
    $pdf->Write(0, convertThai($data['fac']));

    $pdf->SetXY(45, 78);
    $pdf->Write(0, convertThai($data['nu']));

    $pdf->SetXY(40, 86);
    $pdf->Write(0, convertThai($data['mhesi']));




    $pdf->SetXY(94, 93);
    $pdf->Write(0, convertThai($data['lab_id']));

    if($purpose =='R'){$pdf->Image($data['check'], 72, 97, 6, 6);}
    if($purpose =='T'){$pdf->Image($data['check'], 88, 97, 6, 6);}
    if($purpose =='S'){$pdf->Image($data['check'], 122, 97, 6, 6);}

    //$pdf->Image($data['check'], 158, 97, 6, 6);




    $pdf->Image($data['signature'], 150, 145, 40, 25);

    $pdf->SetXY(144, 168);
    $pdf->Write(0, convertThai($data['name']));

    $pdf->SetXY(158, 183);
    $pdf->Write(0, convertThai(getThaiDate()));

    $pdf->SetXY(40, 246);
    $pdf->Write(0, convertThai($data['head']));

/*
    if($้head =='1'){
        $pdf->SetXY(135, 246);
        $pdf->Write(0, convertThai('ผศ.ดร.วรสิทธิ์ โทจำปา')); 
    }
    if($้head =='2'){
        $pdf->SetXY(137, 246);
        $pdf->Write(0, convertThai('ดร.เจษฎา วิชาพร')); 
    }

*/
    $pdf->Output($outputPath, 'F');


        $_SESSION['p12'] = $p12Password;
        $_SESSION['pdf'] = $outputPath;
        $_SESSION['labname'] = $data['lab_name'];
        $_SESSION['labid'] = $lab_id;
        $_SESSION['purpose'] = $purpose;
        $_SESSION['phone'] = $data['phone'];

        // Continue the flow in-process so the AJAX caller always receives JSON.
        require __DIR__ . '/nulabos.php';
        exit;
     

?>
