<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();

require_once __DIR__ . '/vendor/autoload.php';

use setasign\Fpdi\Fpdi;
use setasign\Fpdf\Fpdf; 
require_once 'connect.php';
require_once './vendor/include/connect.php';

try {
   
    $user_id  = $_SESSION['user_id_researcher'] ?? $_GET['user_id'] ?? null;
    $lab_id = $_SESSION['lab_id_app'] ?? $_GET['lab_id'] ?? null;
    $request_id = $_SESSION['request_id_app'] ?? $_GET['request_id'] ?? null; // Make sure you have the request ID
    


$query = "SELECT * FROM lab WHERE id = :lab_id";
$stmt = $conn->prepare($query);
$stmt->execute(['lab_id' => $lab_id]);

$lab = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lab) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลห้องปฏิบัติการ']);
    exit;
}


$dict= $lab['dep'] ?? '';
$query = "SELECT dep FROM department WHERE code = :dict";
$stmt = $conn->prepare($query);
$stmt->execute(['dict' => $dict]);
$dep = $stmt->fetch(PDO::FETCH_ASSOC);

mysqli_set_charset($c, "utf8");

$query = "SELECT * FROM user WHERE user_id = ?"; // เปลี่ยน placeholder เป็น ?
$stmt = $c->prepare($query);

if ($stmt) {
    $stmt->bind_param("i", $user_id); // "i" หมายถึง integer, เปลี่ยนตามประเภทข้อมูลของ user_id
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
} else {
    // จัดการกรณีที่ prepare ล้มเหลว
    echo "Error preparing statement: " . $c->error;
}

if(isset($row['acad_pos_id'])){
    $pos_id= $row['acad_pos_id'];
    $query = "SELECT th_name FROM academic_position WHERE acad_pos_id = ?";
    $stmt = $c->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $pos_id); // "i" หมายถึง integer
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $POS = $result->fetch_column();
            $result->free();
        }
        $stmt->close();
    } else {
        $POS = '';
        
        echo "Error preparing statement: " . $c->error;
    }
}

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

function getExpireDate() {
    return getThaiDate("+1 year");
}

if($POS){$tag=$POS.' ดร.';} else{$tag=NULL;}

$name =  $tag.htmlspecialchars($row['fname'] ?? '').' '.htmlspecialchars($row['lname'] ?? '');

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

$data = [
    'labname' => $lab['name'],
    'department' => $userDepartmentStr, // ใช้ภาควิชาของนักวิจัย
    'fac' => 'คณะเกษตรศาสตร์ฯ',
    'nu' => 'มหาวิทยาลัยนเรศวร',
    'mhesi' => 'กระทรวงการอุดมศึกษา วิทยาศาสตร์ วิจัยและนวัตกรรม',
    'licence' => $lab['license'],
    'score' => $lab['score'],
    'name' => $name,
    'head' => $lab['manager'],
    // NU-LAB-01 uses lab head signature (เดิมดึงจาก lab.sign)
    'signature' => $lab['sign'],
    'wisa' =>'ดร.วิสาข์ สุพรรณไพบูลย์',
    'date' => getThaiDate()
];
//---------------------------------------------------------------------------------------

// ตั้งค่าชื่อไฟล์ PDF
$templatePath = __DIR__ . '/NU-LAB-01.pdf';
$dbPath = './export/01/NU-LAB-01-'.$lab['room'].'-'.$user_id .'.pdf';
$outputPath = __DIR__ . '/export/01/NU-LAB-01-'.$lab['room'].'-'.$user_id .'.pdf';

// สร้าง FPDI instance
$pdf = new FPDI();

if (!file_exists($templatePath)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'NU-01-Template PDF not found: ' . $templatePath]);
    exit;
}

// Check if images exist


if (!file_exists($data['signature'])) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'NU-01-Signature image not found: ' . $data['signature']]);
    exit;
}



// เพิ่มหน้าใหม่
$pdf->AddPage();

// ตั้งค่าไฟล์ PDF ต้นฉบับ
try{
$pdf->setSourceFile($templatePath);
$tplIdx = $pdf->importPage(1);
$pdf->useTemplate($tplIdx, 0, 0, 210);
}catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Error importing template: ' . $e->getMessage()]);
    exit;
}


// ตั้งค่าฟอนต์
$pdf->AddFont('THSarabunNew', '', 'THSarabunNew.php');
$pdf->SetFont('THSarabunNew', '', 16);
$pdf->SetTextColor(0, 0, 0);

function convertThai($text) {
    return iconv('UTF-8', 'cp874', $text);
}

$pdf->SetXY(60, 77);
$pdf->Write(0, convertThai($data['labname']));

$pdf->SetXY(59, 87);
$pdf->Write(0, convertThai($data['department']));

$pdf->SetXY(142, 87);
$pdf->Write(0, convertThai($data['fac']));

$pdf->SetXY(56, 97);
$pdf->Write(0, convertThai($data['nu']));

$pdf->SetXY(48, 107);
$pdf->Write(0, convertThai($data['mhesi']));


$pdf->SetXY(98, 117);
$pdf->Write(0, convertThai($data['licence']));

$pdf->SetXY(78, 126);
$pdf->Write(0, convertThai($data['score']));


$pdf->Image($data['signature'], 115, 188, 30, 12);


$pdf->SetXY(75, 165);
$pdf->Write(0, convertThai($data['name']));


$pdf->SetXY(110, 202);
$pdf->Write(0, convertThai($data['head']));

$pdf->SetXY(121, 210);
$pdf->Write(0, convertThai($data['labname']));

$pdf->SetXY(115, 216);
$pdf->Write(0, convertThai($data['date']));


$pdf->SetXY(110, 241);
$pdf->Write(0, convertThai($data['wisa']));

if (!is_dir(dirname($outputPath))) {
    if (!@mkdir(dirname($outputPath), 0777, true)) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Cannot create output NU01 directory']);
        exit;
    }
}

if (!is_writable(dirname($outputPath))) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Output NU01 folder is not writable: ' . dirname($outputPath)]);
    exit;
}

// Save PDF
try {
    $pdf->Output($outputPath, 'F');
    file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - NU01 created successfully at: " . $outputPath . "\n", FILE_APPEND);
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Error saving NU01: ' . $e->getMessage()]);
    exit;
}

try {
    $updateQuery = "INSERT INTO lab_cert (cert, created_at, file, issue_date, expiry_date,status,lab_id) 
                    VALUES (:user_id, NOW(), :file, :issue_date, :expiry_date,:status,:lab_id)";
    
    $updateStmt = $conn->prepare($updateQuery);

    if ($updateStmt) {
        $updateResult = $updateStmt->execute([
            ':user_id' => $user_id,
            ':file' => $dbPath,
            ':issue_date' => $data['date'],
            ':expiry_date' => getExpireDate(),
            ':status' => '3',
            ':lab_id' => $lab_id
        ]);

        if ($updateResult && $updateStmt->rowCount() > 0) {
            file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Database updated for request ID: " . $request_id . " with PostgreSQL NOW()\n", FILE_APPEND);
        } else {
            file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Warning: Request ID " . $request_id . " not found or no changes made.\n", FILE_APPEND);
        }
    } else {
        file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Error preparing update statement: " . print_r($conn->errorInfo(), true) . "\n", FILE_APPEND);
    }

} catch (Exception $e) {
    file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Database update error: " . $e->getMessage() . "\n", FILE_APPEND);
}



echo json_encode(['success' => true, 'message' => 'อนุมัติสำเร็จ']);

} 

catch (Exception $e) {
    // Log the exception
    file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);

    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
//echo "สร้างไฟล์สำเร็จ: <a href='$outputPath'>ดาวน์โหลดที่นี่</a>";
?>
