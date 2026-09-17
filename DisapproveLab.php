<?php
// At the very beginning, before anything else:
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0); 

// Output buffering to catch any unexpected output
ob_start();

header('Content-Type: application/json');

require_once __DIR__ . '/vendor/autoload.php';
use setasign\Fpdi\Fpdi;
use setasign\Fpdf\Fpdf;
require_once 'connect.php';
require_once './vendor/include/connect.php';

try {

    // Log received data for debugging
    file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Received GET data: " . print_r($_GET, true) . "\n", FILE_APPEND);

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

    // Extract data from GET parameters
    $path = $_GET['path'] ?? null;
    $user_id = $_GET['user_id'] ?? null;
    $reason = $_GET['reason'] ?? null;
    $lab_id = $_GET['lab_id'] ?? 3;
    $request_id = $_GET['id'] ?? null; // Make sure you have the request ID

    // Log extracted data
    file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Extracted GET data: " . print_r([
        'path' => $path,
        'user_id' => $user_id,
        'reason' => $reason,
        'lab_id' => $lab_id,
        'id' => $request_id
    ], true) . "\n", FILE_APPEND);

    // Get user's name from MySQL
    $userName = '';
    try {
        mysqli_set_charset($c, "utf8");
        $userQuery = "SELECT fname, lname FROM user WHERE user_id = ?";
        $userStmt = mysqli_prepare($c, $userQuery);
        if ($userStmt) {
            mysqli_stmt_bind_param($userStmt, "i", $user_id);
            mysqli_stmt_execute($userStmt);
            $result = mysqli_stmt_get_result($userStmt);
            if ($userData = mysqli_fetch_assoc($result)) {
                $userName = $userData['fname'] . ' ' . $userData['lname'];
            }
            mysqli_stmt_close($userStmt);
        }
    } catch (Exception $e) {
        file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - User query error: " . $e->getMessage() . "\n", FILE_APPEND);
    }

    // Fetch lab data
    $query = "SELECT * FROM lab WHERE id = :lab_id";
    $stmt = $conn->prepare($query);
    $stmt->execute(['lab_id' => $lab_id]);
    $lab = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lab) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลห้องปฏิบัติการ']);
        exit;
    }

    $data = [
        'manager' => $lab['manager'],
        'check' => './vendor/image/check.png',
        'signature' => $lab['sign'],
        
    ];

    // Path handling
    $originalPath = $path;

    // Make sure we have a valid path
    if (empty($originalPath)) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Path is empty']);
        exit;
    }

    // Fix path handling using __DIR__ to ensure absolute paths
    $cleanPath = preg_replace('/^\/?esprel\//', '', $originalPath);
    $cleanPath = ltrim($cleanPath, '/');
    $templatePath = __DIR__ . '/' . $cleanPath;
    
    file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Template path: " . $templatePath . "\n", FILE_APPEND);

    // Create directory for the output if it doesn't exist
    $outputDir = dirname($templatePath);
    if (!file_exists($outputDir)) {
        @mkdir($outputDir, 0777, true);
    }

    $outputPath = $templatePath;

    file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Output path: " . $outputPath . "\n", FILE_APPEND);

    // Check if template exists
    if (!file_exists($templatePath)) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Template PDF not found: ' . $templatePath]);
        exit;
    }

    // Check if images exist
    if (!file_exists($data['check'])) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Check image not found: ' . $data['check']]);
        exit;
    }

    if (!file_exists($data['signature'])) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Signature image not found: ' . $data['signature']]);
        exit;
    }

    // Create PDF
    $pdf = new FPDI();
    $pdf->AddPage();

    // Try to import the template
    try {
        $pageCount = $pdf->setSourceFile($templatePath);
        $tplIdx = $pdf->importPage(1);
        $pdf->useTemplate($tplIdx, 0, 0, 210);
    } catch (Exception $e) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Error importing template: ' . $e->getMessage()]);
        exit;
    }

    // Add font
    $pdf->AddFont('THSarabunNew', '', 'THSarabunNew.php');
    $pdf->SetFont('THSarabunNew', '', 16);
    $pdf->SetTextColor(0, 0, 0);

    // Thai encoding function
    function convertThai($text) {
             return iconv('UTF-8', 'TIS-620//TRANSLIT//IGNORE', $text);
    }

    // Add images
    try {
        $pdf->Image($data['check'], 15, 214, 6, 6);
        $pdf->Image($data['signature'], 48, 226, 30, 16);
    } catch (Exception $e) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Error adding images: ' . $e->getMessage()]);
        exit;
    }

    // Place user name (fetched from DB)
    $pdf->SetXY(144, 168);
    $pdf->Write(0, convertThai($userName));

    // Place date
    $pdf->SetXY(42, 262);
    $pdf->Write(0, convertThai(getThaiDate()));



    // Place dean name
    $pdf->SetXY(47, 217);
    $pdf->Write(0, convertThai($reason));

    // Ensure output directory is writable
    if (!is_dir(dirname($outputPath))) {
        if (!@mkdir(dirname($outputPath), 0777, true)) {
            ob_end_clean();
            echo json_encode(['success' => false, 'message' => 'Cannot create output directory']);
            exit;
        }
    }

    if (!is_writable(dirname($outputPath))) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Output folder is not writable: ' . dirname($outputPath)]);
        exit;
    }

    // Save PDF
    try {
        $pdf->Output($outputPath, 'F');
        file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - PDF created successfully at: " . $outputPath . "\n", FILE_APPEND);
    } catch (Exception $e) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Error saving PDF: ' . $e->getMessage()]);
        exit;
    }

    // Update request status in database
    try {
        $updateQuery = "UPDATE request SET stage = 4, timestamp = NOW() WHERE id = :request_id";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->execute(['request_id' => $request_id]);
        file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Database updated for request ID: " . $request_id . " with PostgreSQL NOW()\n", FILE_APPEND);

    } catch (Exception $e) {
        file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Database update error: " . $e->getMessage() . "\n", FILE_APPEND);
        // Don't exit here, we still want to report PDF success even if DB update fails
    }

    // Clean output buffer and send success response
    $output = ob_get_clean();
    if (!empty($output)) {
        // Log unexpected output
        file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Unexpected output: " . $output . "\n", FILE_APPEND);
    }

    // Send success response
    echo json_encode(['success' => true, 'message' => 'อนุมัติสำเร็จและสร้าง PDF เรียบร้อยแล้ว']);

} catch (Exception $e) {
    // Log the exception
    file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);

    // Clear buffer and return error
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
?>