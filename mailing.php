<?php
require_once __DIR__ . '/env.php';
require_once __DIR__.'/vendor/autoload.php';
require __DIR__.'/vendor/PHPMailer/src/PHPMailer.php';
require __DIR__.'/vendor/PHPMailer/src/SMTP.php';
require __DIR__.'/vendor/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Class EmailService
 * จัดการการส่งอีเมลทั้งหมด
 */
class EmailService {
    
    private static $mailer;
    
    /**
     * ตั้งค่าพื้นฐานของ PHPMailer
     */
    private static function initialize() {
        if (self::$mailer === null) {
            self::$mailer = new PHPMailer(true);

            // โหลดการตั้งค่าจาก Environment Variables
            self::$mailer->isSMTP();
            self::$mailer->Host = env('MAIL_HOST', 'smtp.office365.com'); 
            self::$mailer->SMTPAuth = true;
            self::$mailer->Username = env('MAIL_USERNAME', 'agri@nu.ac.th'); 
            self::$mailer->Password = env('MAIL_PASSWORD', '');
            
            $encryption = strtolower(env('MAIL_ENCRYPTION', 'starttls'));
            if ($encryption === 'ssl' || $encryption === 'smtps') {
                self::$mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($encryption === 'tls' || $encryption === 'starttls') {
                self::$mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                self::$mailer->SMTPSecure = false;
                self::$mailer->SMTPAutoTLS = false;
            }

            self::$mailer->Port = (int) env('MAIL_PORT', 587);
            self::$mailer->CharSet = 'UTF-8';

            // การตั้งค่าผู้ส่ง
            $fromAddress = env('MAIL_FROM_ADDRESS', env('MAIL_USERNAME', 'agri@nu.ac.th'));
            $fromName = env('MAIL_FROM_NAME', 'ระบบจัดการเอกสารนักวิจัย - คณะเกษตรศาตร์ฯ มหาวิทยาลัยนเรศวร');
            self::$mailer->Encoding = 'base64';
            self::$mailer->setFrom($fromAddress, $fromName);
            self::$mailer->addReplyTo($fromAddress, $fromName);
        }
    }

    /**
     * ฟังก์ชันส่งอีเมล
     */
    public static function send($to, $subject, $body, $attachment = null, $debug = false, $cc = null) {
        try {
            self::initialize();

            // ล้างค่าเก่าก่อนส่งทุกครั้ง (สำคัญมากเมื่อใช้ static instance)
            self::$mailer->clearAddresses();
            self::$mailer->clearCCs();
            self::$mailer->clearAttachments();

            if ($debug) {
                self::$mailer->SMTPDebug = SMTP::DEBUG_SERVER;
                self::$mailer->Debugoutput = function($str, $level) {
                    error_log("PHPMailer Debug: $str");
                };
            } else {
                self::$mailer->SMTPDebug = SMTP::DEBUG_OFF;
            }
            
            // ผู้รับ
            self::$mailer->addAddress($to);
            
            // เพิ่ม CC ถ้ามี
            if ($cc !== null) {
                if (is_string($cc)) {
                    self::$mailer->addCC($cc);
                } elseif (is_array($cc)) {
                    foreach ($cc as $ccEmail) {
                        self::$mailer->addCC($ccEmail);
                    }
                }
            }
            
            // เนื้อหาอีเมล
            self::$mailer->isHTML(true);
            self::$mailer->Subject = $subject;
            self::$mailer->Body = $body;
            
            // ไฟล์แนบ (ยังทำงานเหมือนเดิม)
            if ($attachment !== null) {
                if (is_string($attachment)) {
                    if (file_exists($attachment)) {
                        self::$mailer->addAttachment($attachment, basename($attachment));
                    }
                } elseif (is_array($attachment)) {
                    if (isset($attachment['content']) && isset($attachment['filename'])) {
                        $fileContent = base64_decode($attachment['content']);
                        if ($fileContent !== false) {
                            self::$mailer->addStringAttachment(
                                $fileContent,
                                $attachment['filename'],
                                'base64',
                                $attachment['mime_type'] ?? 'application/octet-stream'
                            );
                        }
                    } elseif (isset($attachment['path']) && file_exists($attachment['path'])) {
                        self::$mailer->addAttachment(
                            $attachment['path'],
                            $attachment['filename'] ?? basename($attachment['path'])
                        );
                    }
                }
            }
            
            $result = self::$mailer->send();
            
            return [
                'success' => $result,
                'message' => $result ? 'Email sent successfully' : 'Failed to send email: ' . self::$mailer->ErrorInfo
            ];
            
        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการส่งอีเมล: ' . $e->getMessage(),
                'error_details' => $debug ? $e->getTraceAsString() : null
            ];
        }
    }
}

/**
 * ฟังก์ชันส่ง JSON response (คงเดิม)
 */
function sendJsonResponse($success, $message, $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    $response = ['success' => $success, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// === ส่วนของ API Endpoint (แทบไม่เปลี่ยนแปลง) ===

$isHttpRequest = isset($_SERVER['REQUEST_METHOD']);

if ($isHttpRequest) {
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
    
    // อนุญาตให้สคริปต์ทำงานต่อแม้ client (cURL) จะตัดการเชื่อมต่อ (timeout) ไปแล้ว
    ignore_user_abort(true);
    set_time_limit(0);
    
    ob_start();
    
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
    
    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            sendJsonResponse(false, 'อนุญาตเฉพาะ POST method เท่านั้น', null, 405);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if ($input === null || json_last_error() !== JSON_ERROR_NONE) {
            $input = $_POST;
            if (!empty($_FILES)) {
                $input['files'] = $_FILES;
            }
        }
        
        $requiredFields = ['to', 'subject', 'body'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field]) || empty($input[$field])) {
                sendJsonResponse(false, "ข้อมูล {$field} จำเป็นต้องระบุ", null, 400);
            }
        }
        
        $to = filter_var($input['to'], FILTER_SANITIZE_EMAIL);
        $subject = htmlspecialchars($input['subject'], ENT_QUOTES, 'UTF-8');
        $body = $input['body'];
        $debug = isset($input['debug']) ? (bool)$input['debug'] : false;
        $cc = isset($input['cc']) ? $input['cc'] : env('MAIL_CC_DEFAULT', 'nungruthait@nu.ac.th');
        
        $attachment = null;
        if (isset($input['attachment']) && is_array($input['attachment'])) {
            $attachment = $input['attachment'];
        } elseif (isset($input['files']) && !empty($input['files'])) {
            foreach ($input['files'] as $fileField => $fileInfo) {
                if (is_uploaded_file($fileInfo['tmp_name'])) {
                    $attachment = [
                        'path' => $fileInfo['tmp_name'],
                        'filename' => $fileInfo['name'],
                        'mime_type' => $fileInfo['type']
                    ];
                    break;
                }
            }
        }
        
        // ** จุดที่เปลี่ยนแปลง: เรียกใช้ Class ใหม่แทนฟังก์ชันเก่า **
        $result = EmailService::send($to, $subject, $body, $attachment, $debug, $cc);
        
        if ($result['success']) {
            sendJsonResponse(true, $result['message'], $result);
        } else {
            sendJsonResponse(false, $result['message'], $result, 500);
        }
        
    } catch (Exception $e) {
        error_log("Email API Error: " . $e->getMessage());
        sendJsonResponse(false, 'เกิดข้อผิดพลาดใน API: ' . $e->getMessage(), null, 500);
    } catch (Error $e) {
        error_log("Email API Fatal Error: " . $e->getMessage());
        sendJsonResponse(false, 'เกิดข้อผิดพลาดร้ายแรง: ' . $e->getMessage(), null, 500);
    }
    
    if (ob_get_length()) {
        ob_end_clean();
    }
}
?>