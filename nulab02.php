<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0); 


require_once __DIR__ . '/vendor/autoload.php';

use setasign\Fpdi\Fpdi;
use setasign\Fpdf\Fpdf; 
require_once 'connect.php';

session_start();
$loggedInUserId = $_SESSION['user_id'] ?? null;
if (!$loggedInUserId) {
    die("กรุณาเข้าสู่ระบบก่อน");
}

$lab_id = $_POST['labselect'] ?? '3';
$purpose = $_POST['purpose'] ?? '1';
$head = $_POST['head'] ?? '1';

// Get Lab Info
$query = "SELECT * FROM lab WHERE id = :lab_id";
$stmt = $conn->prepare($query);
$stmt->execute(['lab_id' => $lab_id]);

$lab = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lab) {
    die("ไม่พบข้อมูลห้องปฏิบัติการ");
}

// Get Lab Department Info
$dict = $lab['dep'] ?? '';
$query = "SELECT dep FROM department WHERE code = :dict";
$stmt = $conn->prepare($query);
$stmt->execute(['dict' => $dict]);
$dep = $stmt->fetch(PDO::FETCH_ASSOC);

// Get User (Researcher) Info
// Note: Assuming a researcher workflow here since this script requires MySQL user table.
$queryUser = "SELECT * FROM user WHERE user_id = :user_id";
$stmtUser = $conn->prepare($queryUser);
$stmtUser->execute(['user_id' => $loggedInUserId]);
$row = $stmtUser->fetch(PDO::FETCH_ASSOC);

// Get User Department String from depart_id
$userDepartmentStr = 'ไม่ระบุภาควิชา';
if (!empty($row['depart_id'])) {
    $qDep = "SELECT dep FROM department WHERE code = :d_id";
    $sDep = $conn->prepare($qDep);
    $sDep->execute(['d_id' => $row['depart_id']]);
    $uDepName = $sDep->fetchColumn();
    if ($uDepName) {
        $userDepartmentStr = $uDepName;
    }
}

// Get Academic Position
$POS = '';
if(!empty($row['acad_pos_id'])){
    $pos_id = $row['acad_pos_id'];
    $queryPos = "SELECT th_name FROM academic_position WHERE acad_pos_id = :pos_id";
    $stmtPos = $conn->prepare($queryPos);
    $stmtPos->execute(['pos_id' => $pos_id]);
    $POS = $stmtPos->fetchColumn();
}

$tag = $POS ? $POS . ' ดร.' : '';
$fullName = $tag . htmlspecialchars($row['fname'] ?? '') . ' ' . htmlspecialchars($row['lname'] ?? '');

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

$data = [
    'name' => $fullName,
    'position' => 'นักวิทยาศาสตร์', // หรือค่าอัตโนมัติตามตำแหน่งสายงานของคุณ
    'department' => $userDepartmentStr,
    'phone' => $_POST['phone'] ?? 'ไม่ระบุเบอร์โทร',
    'email' => $row['email'] ?? '',
    'lab_name' => $lab['name'] ?? '',
    'lab_id' => $lab['license'] ?? '',
    'ldepartment' => $dep['dep'] ?? '',
    'head' => $lab['manager'] ?? '',
    'check' => './vendor/image/check.png',
    'fac' => 'คณะเกษตรศาสตร์ฯ',
    'nu' => 'มหาวิทยาลัยนเรศวร',
    'mhesi' => 'กระทรวงการอุดมศึกษา วิทยาศาสตร์ วิจัยและนวัตกรรม',
    'purpose' => 'วิจัย',
    'signature' => './sign/' . ($row['email'] ? explode('@', $row['email'])[0] : 'default') . '.png'
];


$templatePath = 'NU-LAB-02.pdf';
$outputPath = './export/02/NU-LAB-02-'.$lab['room'].'-'.$data['name'].'.pdf';

$pdf = new FPDI();

$pdf->AddPage();

$pdf->setSourceFile($templatePath);
$tplIdx = $pdf->importPage(1);
$pdf->useTemplate($tplIdx, 0, 0, 210);

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

$pdf->SetXY(130, 48);
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

//$pdf->SetXY(40, 246);
//$pdf->Write(0, convertThai($data['head']));


if($head =='1'){
    $pdf->SetXY(135, 246);
    $pdf->Write(0, convertThai('ผศ.ดร.วรสิทธิ์ โทจำปา')); 
}
if($head =='2'){
    $pdf->SetXY(137, 246);
    $pdf->Write(0, convertThai('ดร.เจษฎา วิชาพร')); 
}


$pdf->Output($outputPath, 'F');


function readP12Data($p12File, $password) {
    $certs = array();
    if (openssl_pkcs12_read(file_get_contents($p12File), $certs, $password)) {
        return $certs;
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        isset($_FILES['p12_file']) && $_FILES['p12_file']['error'] === UPLOAD_ERR_OK &&
        isset($_POST['p12_password'])) {

      
        $p12FilePath = $_FILES['p12_file']['tmp_name'];
        $p12Password = $_POST['p12_password'];

        $p12Data = readP12Data($p12FilePath, $p12Password);

        if ($p12Data && isset($p12Data['cert']) && isset($p12Data['pkey'])) {
            $certificate = $p12Data['cert'];
            $privateKey = $p12Data['pkey'];

            // สร้าง Instance ของ Fpdi เพื่อนำเข้า PDF
         
            // ดึงข้อมูลจาก Subject ของใบรับรอง
            $signerName = $p12Data['subject']['CN'] ?? '';
            $signerEmail = $p12Data['subject']['emailAddress'] ?? '';
            $signerOrg = $p12Data['subject']['O'] ?? '';

            // ตั้งค่าข้อมูลการลงนาม (Digital Certificate Information)
            $info = array(
                'Name' => $signerName,
                'Location' => 'คณะเกษตรศาสตร์ มหาวิทยาลัยนเรศวร',
                'Reason' => 'ลงนามเอกสารเพื่อขอใบรับรองนักวิจัย',
                'ContactInfo' => $signerEmail,
                'Company' => $signerOrg,
            );

            // ตั้งค่าการลงนามดิจิทัล (ใช้ Instance ของ TCPDF)
            $pdfSign->setSignature($certificate, $privateKey, $p12Password, '', 1, $info);

            // บันทึกและดาวน์โหลดเอกสาร (ใช้ Instance ของ TCPDF)
            $signedFilename = pathinfo($pdf, PATHINFO_FILENAME) . '_signed.pdf';
            $pdfSign->Output($pdf, 'D');

            unlink($pdfFilePath);
            unlink($p12FilePath);

        } else {
            echo "เกิดข้อผิดพลาดในการอ่านไฟล์ P12";
        }

    } else {
        echo "โปรดอัปโหลดไฟล์ PDF และ P12 พร้อมรหัสผ่าน";
    }
    header("Location: $outputPath");
}

?>
