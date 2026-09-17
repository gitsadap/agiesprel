<?php
require_once 'function.php';
  if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION['user_id']);
    header("location: index.php");
  }

  if (!isset($_SESSION['user_id'])) {
    header("location: index.php");
  }

$loggedInUserId = $_SESSION['user_id'] ; // ดึง user_id จาก session หรือ null ถ้าไม่มี
//echo $loggedInUserId;


if ($loggedInUserId !== null) {
    $uploadInfo = getLatestUploadInfo($conn, $loggedInUserId);
    $uploadId = $uploadInfo['upload_id'] ?? 0;
    $certtype = $uploadInfo['upload_type'] ?? '';



    if (isset($certtype)) {
    $cert = getUploadPathsByType($conn, $uploadId, $certtype);
    } 
    else {
        $cert = []; // กำหนดค่าเริ่มต้น
    }
} 

    $approvalStatus = getApprovalStatus($conn, $loggedInUserId);
   // echo $approvalStatus;
   if($approvalStatus == '0'){
    $approvalStatus ='';
   }

   $sign = getSignStatus($conn, $loggedInUserId);
   $nu02 = getCertStatus($conn, $loggedInUserId);
   $stage = $nu02['stage']?? '';
   $nu02_path = $nu02['path']?? '';
   $labSelected = $nu02['lab_id'] ?? '';
   if($labSelected){
        $contact = getContactLabs($conn, $labSelected);
   }else{
    $contact = NULL;
   }
   $nu01_path = getnu01($conn, $loggedInUserId);
   //echo $contact['id_lab'] ;
    $POS2 = $POS ?? '';
   
    if($dep_id < 2 || isset($dep_id)){
        $dr = '';
    }
    if($dep_id>1 && $dep_id <5){
        $dr = 'ดร.';
    }
   
    
?>


<!DOCTYPE html>
<html lang="th">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการใบรับรองนักวิจัยเพื่อใช้ประกอบในการขอทุน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        :root {
            --primary-color: #2E7D32;
            --primary-light: #4CAF50;
            --primary-lighter: #A5D6A7;
            --primary-dark: #1B5E20;
            --accent-color: #8BC34A;
            --text-light: #F1F8E9;
            --text-dark: #263238;
            --bg-light: #F9FBE7;
            --bg-gray: #F5F5F5;
            --bg-white: #FFFFFF;
            --error-color: #D32F2F;
            --warning-color: #FFA000;
            --info-color: #1976D2;
            --success-color: #388E3C;
            --border-radius: 12px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #F8FDF8;
            color: var(--text-dark);
            line-height: 1.6;
            padding-bottom: 40px;
        }
        
        /* Navbar styling */
        .navbar-custom {
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
            padding: 12px 0;
        }
        
        .logo-circle {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
            border: 2px solid var(--primary-lighter);
            padding: 2px;
            transition: var(--transition);
        }
        
        .logo-circle:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .logo-circle img {
            max-width: 80%;
            max-height: 80%;
            object-fit: contain;
        }
        
        .system-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 2px;
        }
        
        .system-subtitle {
            font-size: 0.85rem;
            color: var(--text-dark);
            opacity: 0.8;
        }
        
        /* Main container */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        /* Card styling */
        .dashboard-card {
            background-color: var(--bg-white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: none;
            overflow: hidden;
            transition: var(--transition);
            margin-bottom: 25px;
            padding: 0;
        }
        
        /* Researcher profile card */
        .researcher-profile {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 24px;
            margin-bottom: 24px;
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .profile-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--primary-lighter);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            color: var(--primary-dark);
            font-size: 24px;
            overflow: hidden;
            position: relative;
        }
        
        .profile-icon img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .profile-details {
            flex: 1;
        }
        
        .profile-name {
            font-weight: 600;
            font-size: 1.2rem;
            color: var(--primary-dark);
            margin-bottom: 4px;
        }
        
        .profile-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 16px;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }
        
        .meta-item i {
            margin-right: 8px;
            color: var(--primary-color);
        }
        
        .lab-info {
            background-color: #F1F8E9;
            border-radius: 8px;
            padding: 16px;
            margin-top: 16px;
            border-left: 4px solid var(--primary-color);
        }
        
        .lab-info h6 {
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 8px;
        }
        
        .status-indicators {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-top: 20px;
        }
        
        .status-item {
            display: flex;
            align-items: center;
            padding: 10px 16px;
            background-color: #F5F5F5;
            border-radius: 8px;
            width: 100%;
            max-width: 320px;
        }
        
        .status-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 16px;
        }
        
        .status-icon.success {
            background-color: #E8F5E9;
            color: var(--success-color);
        }
        
        .status-icon.warning {
            background-color: #FFF8E1;
            color: var(--warning-color);
        }
        
        .status-icon.error {
            background-color: #FFEBEE;
            color: var(--error-color);
        }
        
        .status-text {
            font-size: 0.9rem;
        }
        
        /* Progress tracker */
        .progress-tracker {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 24px;
            margin-bottom: 24px;
        }
        
        .progress-tracker h5 {
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .progress-tracker h5 i {
            margin-right: 8px;
            color: var(--primary-color);
        }
        
        .progress-steps {
            position: relative;
            display: flex;
            justify-content: space-between;
            padding-top: 24px;
        }
        
        .progress-line {
            position: absolute;
            top: 36px;
            left: 0;
            width: 100%;
            height: 4px;
            background-color: #E0E0E0;
            z-index: 1;
        }
        
        .progress-line-active {
            position: absolute;
            top: 36px;
            left: 0;
            height: 4px;
            background-color: var(--primary-color);
            z-index: 2;
            transition: width 0.5s ease;
        }
        
        .progress-step {
            position: relative;
            z-index: 3;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 140px;
        }
        
        .step-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #E0E0E0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            color: white;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .step-active .step-icon {
            background-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.2);
        }
        
        .step-completed .step-icon {
            background-color: var(--primary-color);
        }
        
        .step-text {
            font-size: 0.85rem;
            text-align: center;
            color: #757575;
            transition: all 0.3s ease;
        }
        
        .step-active .step-text {
            color: var(--primary-dark);
            font-weight: 500;
        }
        
        .step-completed .step-text {
            color: var(--primary-dark);
        }
        
        .step-error .step-icon {
            background-color: var(--error-color);
            box-shadow: 0 0 0 4px rgba(211, 47, 47, 0.2);
        }
        
        .step-error .step-text {
            color: var(--error-color);
        }
        
        /* Action buttons */
        .action-buttons {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 24px;
            margin-bottom: 24px;
        }
        
        .action-buttons h5 {
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .action-buttons h5 i {
            margin-right: 8px;
            color: var(--primary-color);
        }
        
        .button-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 16px;
        }
        
        .action-button {
            display: flex;
            align-items: center;
            padding: 16px;
            background-color: #F5F5F5;
            border-radius: 10px;
            border: none;
            transition: all 0.3s ease;
            text-align: left;
            cursor: pointer;
        }
        
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .action-button.primary {
            background-color: #E8F5E9;
        }
        
        .action-button.secondary {
            background-color: #F5F5F5;
        }
        
        .action-button.warning {
            background-color: #FFF8E1;
        }
        
        .action-button.danger {
            background-color: #FFEBEE;
        }
        
        .action-button.disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .action-button.disabled:hover {
            transform: none;
            box-shadow: none;
        }
        
        .button-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 18px;
            flex-shrink: 0;
        }
        
        .button-icon.primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .button-icon.secondary {
            background-color: #9E9E9E;
            color: white;
        }
        
        .button-icon.warning {
            background-color: var(--warning-color);
            color: white;
        }
        
        .button-icon.danger {
            background-color: var(--error-color);
            color: white;
        }
        
        .button-text {
            flex: 1;
        }
        
        .button-title {
            font-weight: 500;
            font-size: 0.95rem;
            margin-bottom: 4px;
            color: var(--text-dark);
        }
        
        .button-subtitle {
            font-size: 0.8rem;
            color: #757575;
        }
        
        /* Forms */
        .form-section {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 24px;
            margin-bottom: 24px;
        }
        
        .form-section h5 {
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .form-section h5 i {
            margin-right: 8px;
            color: var(--primary-color);
        }
        
        .form-guide {
            background-color: #E3F2FD;
            border-left: 4px solid var(--info-color);
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 16px;
        }
        
        .form-control, .form-select {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #E0E0E0;
            font-size: 0.95rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }
        
        .form-hint {
            font-size: 0.8rem;
            color: #757575;
            margin-top: 4px;
        }
        
        .form-footer {
            margin-top: 24px;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
        
        /* Progress custom */
        .progress-custom {
            height: 10px;
            border-radius: 50px;
            background-color: #E8F5E9;
            margin-top: 8px;
            overflow: hidden;
        }
        
        .progress-bar-custom {
            background-color: var(--primary-color);
            height: 10px;
            border-radius: 50px;
            transition: width 0.5s ease;
        }
        
        /* Document viewer */
        .document-viewer {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 24px;
            margin-bottom: 24px;
        }
        
        .document-viewer h5 {
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .document-viewer h5 i {
            margin-right: 8px;
            color: var(--primary-color);
        }
        
        /* Buttons */
        .btn-modern {
            padding: 10px 16px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .btn-modern.primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-modern.primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-modern.secondary {
            background-color: #F5F5F5;
            color: var(--text-dark);
        }
        
        .btn-modern.secondary:hover {
            background-color: #E0E0E0;
        }
        
        .btn-modern.danger {
            background-color: var(--error-color);
            color: white;
        }
        
        .btn-modern.danger:hover {
            background-color: #C62828;
        }
        
        .btn-modern.warning {
            background-color: var(--warning-color);
            color: white;
        }
        
        .btn-modern.warning:hover {
            background-color: #FF8F00;
        }
        
        .btn-modern.success {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-modern.success:hover {
            background-color: #2E7D32;
        }
        
        .btn-modern.outline-primary {
            background-color: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-modern.outline-primary:hover {
            background-color: #E8F5E9;
        }
        
        .btn-modern.disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .btn-modern.disabled:hover {
            transform: none;
            box-shadow: none;
        }
        
        /* Badge styling */
        .badge-status {
            padding: 6px 10px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
        }
        
        .badge-status i {
            margin-right: 4px;
            font-size: 0.9rem;
        }
        
        .badge-waiting {
            background-color: #FFF8E1;
            color: #FF8F00;
        }
        
        .badge-approved {
            background-color: #E8F5E9;
            color: #2E7D32;
        }
        
        .badge-rejected {
            background-color: #FFEBEE;
            color: #C62828;
        }
        
        .badge-processing {
            background-color: #E3F2FD;
            color: #1565C0;
        }
        
        /* Loading animation */
        .loading-spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid rgba(76, 175, 80, 0.1);
            border-left-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            flex-direction: column;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        
        .loading-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .loading-text {
            margin-top: 12px;
            font-weight: 500;
            color: var(--primary-dark);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .profile-icon {
                margin-bottom: 12px;
            }
            
            .progress-steps {
                overflow-x: auto;
                padding-bottom: 12px;
            }
            
            .action-button {
                width: 100%;
            }
            
            .button-grid {
                grid-template-columns: 1fr;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-footer {
                flex-direction: column;
            }
            
            .form-footer button {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top mb-4">
        <div class="container">
            <div class="d-flex align-items-center">
                <div class="logo-circle me-3">
                    <img src="./vendor/image/logo.jpg" alt="Logo">
                </div>
                <div>
                    <h1 class="system-title mb-0">ระบบจัดการใบรับรองนักวิจัยเพื่อใช้ประกอบการขอทุน คณะเกษตรศาสตร์ฯ </h1>
                    <p class="system-subtitle mb-0">สำหรับนักวิจัย</p>
                </div>
            </div>
            
            <div class="d-flex align-items-center">
                <div class="text-end me-3">
                    <span class="d-block text-success fw-bold"><?php echo $POS2.' '.$dr.htmlspecialchars($row['fname']).' '.htmlspecialchars($row['lname']); ?></span>
                </div>
                <a href="./researcher.php?logout" class="btn btn-outline-danger btn-sm">
                    <i class='bx bx-log-out me-1'></i>ออกจากระบบ
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Researcher Profile -->
        <div class="researcher-profile">
        <div class="profile-header">
                <div class="profile-icon">
                    <img src="https://ww2.agi.nu.ac.th/personnel/upload/<?php echo $row['profile_image'] ?>" alt="Profile Image">
                </div>
                <div class="profile-details">
                    <h4 class="profile-name"><?php echo $POS2.' '.$dr.htmlspecialchars($row['fname']).' '.htmlspecialchars($row['lname']); ?></h4>
                    <div class="profile-meta">
                        <div class="meta-item">
                            <i class="fa-solid fa-building-columns"></i>
                            <span>สังกัดภาควิชา: <?php echo $DEP ;?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fa-solid fa-envelope"></i>
                            <span>อีเมล: <?php echo htmlspecialchars($row['email']);?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if($stage>0 && $stage<5) { ?>
            <div class="lab-info">
                <h6><i class="fa-solid fa-flask"></i> ข้อมูลห้องปฏิบัติการ</h6>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2">ห้องปฎิบัติการในการทำงานวิจัย: <strong><?php echo $contact['name']?></strong></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2">ติดต่อห้องปฎิบัติการ: <strong><?php echo $contact['sci'].' ('.$contact['sci_tel'].') | '. $contact['admin'].' ('.$contact['admin_tel'].')' ?></strong></p>
                    </div>
                </div>
            </div>
            <?php } ?>
            
            <div class="status-indicators">
                <div class="status-item">
                    <div class="status-icon <?php echo ($uploadId == 0) ? 'error' : 'success'; ?>">
                        <i class="fa-solid <?php echo ($uploadId == 0) ? 'fa-xmark' : 'fa-check'; ?>"></i>
                    </div>
                    <div class="status-text">
                        <?php
                            echo ($uploadId == 0) ? 'ยังไม่ได้อัปโหลดใบประกาศ' : 'อัปโหลดใบประกาศแล้ว: ' . htmlspecialchars($approvalStatus);
                        ?>
                    </div>
                </div>
                
                <div class="status-item">
                    <div class="status-icon <?php echo ($sign == 0) ? 'error' : 'success'; ?>">
                        <i class="fa-solid <?php echo ($sign == 0) ? 'fa-xmark' : 'fa-check'; ?>"></i>
                    </div>
                    <div class="status-text">
                        <?php
                            echo ($sign == 0) ? 'ยังไม่ได้อัปโหลดลายเซ็น' : 'อัปโหลดลายเซ็นแล้ว';
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Progress Tracker -->
        <?php if($stage>0) { ?>
        <div class="progress-tracker">
            <h5><i class="fa-solid fa-chart-line"></i> ความคืบหน้าการดำเนินการ</h5>
            
            <?php if($stage == 1) { ?>
            <div class="progress-steps">
                <div class="progress-line"></div>
                <div class="progress-line-active" style="width: 25%"></div>
                
                <div class="progress-step step-active">
                    <div class="step-icon">1</div>
                    <div class="step-text">ยื่นคำขอ</div>
                </div>
                
                <div class="progress-step">
                    <div class="step-icon">2</div>
                    <div class="step-text">ห้องปฏิบัติการอนุมัติ</div>
                </div>
                
                <div class="progress-step">
                    <div class="step-icon">3</div>
                    <div class="step-text">ผู้บริหารอนุมัติ</div>
                </div>
                
                <div class="progress-step">
                    <div class="step-icon">4</div>
                    <div class="step-text">ออกใบรับรอง</div>
                </div>
            </div>
            <?php } ?>
            
            <?php if($stage == 3) { ?>
            <div class="progress-steps">
                <div class="progress-line"></div>
                <div class="progress-line-active" style="width: 75%"></div>
                
                <div class="progress-step step-completed">
                    <div class="step-icon"><i class="fa-solid fa-check"></i></div>
                    <div class="step-text">ยื่นคำขอ</div>
                </div>
                
                <div class="progress-step step-completed">
                    <div class="step-icon"><i class="fa-solid fa-check"></i></div>
                    <div class="step-text">ห้องปฏิบัติการอนุมัติ</div>
                </div>
                
                <div class="progress-step step-active">
                    <div class="step-icon">3</div>
                    <div class="step-text">ผู้บริหารอนุมัติ</div>
                </div>
                
                <div class="progress-step">
                    <div class="step-icon">4</div>
                    <div class="step-text">ออกใบรับรอง</div>
                </div>
            </div>
            <?php } ?>
            
            <?php if($stage == 4) { ?>
            <div class="progress-steps">
                <div class="progress-line"></div>
                <div class="progress-line-active" style="width: 25%"></div>
                
                <div class="progress-step step-completed">
                    <div class="step-icon"><i class="fa-solid fa-check"></i></div>
                    <div class="step-text">ยื่นคำขอ</div>
                </div>
                
                <div class="progress-step step-error">
                    <div class="step-icon"><i class="fa-solid fa-xmark"></i></div>
                    <div class="step-text">ห้องปฏิบัติการไม่อนุมัติ</div>
                </div>
                
                <div class="progress-step">
                    <div class="step-icon">3</div>
                    <div class="step-text">ผู้บริหารอนุมัติ</div>
                </div>
                
                <div class="progress-step">
                    <div class="step-icon">4</div>
                    <div class="step-text">ออกใบรับรอง</div>
                </div>
            </div>
            <?php } ?>
            
            <?php if($stage == 5) { ?>
            <div class="progress-steps">
                <div class="progress-line"></div>
                <div class="progress-line-active" style="width: 50%"></div>
                
                <div class="progress-step step-completed">
                    <div class="step-icon"><i class="fa-solid fa-check"></i></div>
                    <div class="step-text">ยื่นคำขอ</div>
                </div>
                
                <div class="progress-step step-completed">
                    <div class="step-icon"><i class="fa-solid fa-check"></i></div>
                    <div class="step-text">ห้องปฏิบัติการอนุมัติ</div>
                </div>
                
                <div class="progress-step step-error">
                    <div class="step-icon"><i class="fa-solid fa-xmark"></i></div>
                    <div class="step-text">ผู้บริหารไม่อนุมัติ</div>
                </div>
                
                <div class="progress-step">
                    <div class="step-icon">4</div>
                    <div class="step-text">ออกใบรับรอง</div>
                </div>
            </div>
            <?php } ?>
            
            <?php if($stage == 6) { ?>
            <div class="progress-steps">
                <div class="progress-line"></div>
                <div class="progress-line-active" style="width: 100%"></div>
                
                <div class="progress-step step-completed">
                    <div class="step-icon"><i class="fa-solid fa-check"></i></div>
                    <div class="step-text">ยื่นคำขอ</div>
                </div>
                
                <div class="progress-step step-completed">
                    <div class="step-icon"><i class="fa-solid fa-check"></i></div>
                    <div class="step-text">ห้องปฏิบัติการอนุมัติ</div>
                </div>
                
                <div class="progress-step step-completed">
                    <div class="step-icon"><i class="fa-solid fa-check"></i></div>
                    <div class="step-text">ผู้บริหารอนุมัติ</div>
                </div>
                
                <div class="progress-step step-completed">
                    <div class="step-icon"><i class="fa-solid fa-check"></i></div>
                    <div class="step-text">ออกใบรับรอง</div>
                </div>
            </div>
            <?php } ?>
            
            <div class="mt-4">
                <?php if($stage>0 && $stage<5) { ?>
                <div class="d-flex justify-content-between align-items-center">
                    <button id="nuView" class="btn btn-modern outline-primary">
                        <i class="fa-solid fa-file-lines me-2"></i>ดูเอกสาร NU-LAB-02 ของท่าน
                    </button>
                    <button id="deny" class="btn btn-modern danger">
                        <i class="fa-solid fa-ban me-2"></i>ยกเลิกเอกสาร
                    </button>
                </div>
                <?php } ?>
                
                <?php if($stage>5) { ?>
                <div class="d-flex justify-content-between align-items-center">
                    <button id="View01" class="btn btn-modern outline-primary">
                        <i class="fa-solid fa-file-lines me-2"></i>เอกสาร NU-LAB-01 ของท่าน
                    </button>
                    <button id="deny" class="btn btn-modern danger">
                        <i class="fa-solid fa-ban me-2"></i>ยกเลิกเอกสาร
                    </button>
                </div>
                <?php } ?>
                
                <?php if($stage == 3) { ?>
                <div class="mt-3">
                    <button id="viewFileBtn" data-user-id="<?php echo $loggedInUserId; ?>" class="btn btn-modern success">
                        <i class="fa-solid fa-file-pdf me-2"></i>ดูเอกสาร NU-LAB-01 ของท่าน
                    </button>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
        
        <!-- Action Buttons -->
        <div class="action-buttons">
            <h5><i class="fa-solid fa-circle-check"></i> ดำเนินการ</h5>
            
            <div class="button-grid">
                <?php if($uploadId>0){ ?>
                <button id="CertView" class="action-button secondary">
                    <div class="button-icon secondary">
                        <i class="fa-solid fa-file-certificate"></i>
                    </div>
                    <div class="button-text">
                        <div class="button-title">ดูไฟล์ประกาศนียบัตร</div>
                        <div class="button-subtitle">ตรวจสอบไฟล์ที่อัปโหลดไว้แล้ว</div>
                    </div>
                </button>
                
                <button id="updateCert" class="action-button primary">
                    <div class="button-icon primary">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <div class="button-text">
                        <div class="button-title">แก้ไขไฟล์ประกาศนียบัตร</div>
                        <div class="button-subtitle">อัปโหลดไฟล์ใหม่เพื่อแทนที่</div>
                    </div>
                </button>
                <?php } else{ ?>
                <button id="updateCert" class="action-button warning">
                    <div class="button-icon warning">
                        <i class="fa-solid fa-upload"></i>
                    </div>
                    <div class="button-text">
                        <div class="button-title">อัพโหลดไฟล์ประกาศนียบัตร</div>
                        <div class="button-subtitle">อัปโหลดใบประกาศเพื่อดำเนินการต่อ</div>
                    </div>
                </button>
                <?php } ?>
                
                <?php if($uploadId == 0) { ?>
                <button id="signAdd" class="action-button secondary disabled">
                    <div class="button-icon secondary">
                        <i class="fa-solid fa-signature"></i>
                    </div>
                    <div class="button-text">
                        <div class="button-title">ลายเซ็นอิเล็กทรอนิกส์</div>
                        <div class="button-subtitle">กรุณาอัพโหลดไฟล์ประกาศนียบัตรก่อน</div>
                    </div>
                </button>
                <?php } else { ?>
                <button id="signAdd" class="action-button primary">
                    <div class="button-icon primary">
                        <i class="fa-solid fa-signature"></i>
                    </div>
                    <div class="button-text">
                        <div class="button-title">ลายเซ็นอิเล็กทรอนิกส์</div>
                        <div class="button-subtitle">อัปโหลดลายเซ็นของท่าน</div>
                    </div>
                </button>
                <?php } ?>
                
                <?php if($sign == 0) { ?>
                <button id="labCert" class="action-button secondary disabled">
                    <div class="button-icon secondary">
                        <i class="fa-solid fa-file-contract"></i>
                    </div>
                    <div class="button-text">
                        <div class="button-title">ขอใบรับรองนักวิจัย</div>
                        <div class="button-subtitle">กรุณาอัพโหลดลายเซ็นต์ของท่านก่อน</div>
                    </div>
                </button>
                <?php } else if($stage>0) { ?>
                <button id="labCert" class="action-button secondary disabled">
                    <div class="button-icon secondary">
                        <i class="fa-solid fa-file-contract"></i>
                    </div>
                    <div class="button-text">
                        <div class="button-title">ขอใบรับรองนักวิจัย</div>
                        <div class="button-subtitle">อยู่ระหว่างกระบวนการ</div>
                    </div>
                </button>
                <?php } else { ?>
                <button id="labCert" class="action-button primary">
                    <div class="button-icon primary">
                        <i class="fa-solid fa-file-contract"></i>
                    </div>
                    <div class="button-text">
                        <div class="button-title">ขอใบรับรองนักวิจัย</div>
                        <div class="button-subtitle">เริ่มขั้นตอนการขอใบรับรอง</div>
                    </div>
                </button>
                <?php } ?>
                
                <?php if($sign != 0) { ?>
                <button id="signView" class="action-button secondary">
                    <div class="button-icon secondary">
                        <i class="fa-solid fa-eye"></i>
                    </div>
                    <div class="button-text">
                        <div class="button-title">ดูภาพลายเซ็น</div>
                        <div class="button-subtitle">ตรวจสอบลายเซ็นที่อัปโหลด</div>
                    </div>
                </button>
                <?php } ?>
            </div>
        </div>
        
        <!-- Forms -->
        <!-- Certificate Upload Form -->
        <div id="uploadForm" class="form-section" style="display: none;">
            <h5><i class="fa-solid fa-upload"></i> อัปโหลดใบประกาศนียบัตร</h5>
            
            <div class="mb-4">
                <label for="uploadType" class="form-label">ประเภทการอัพโหลด:</label>
                <select class="form-select" id="uploadType" name="upload_type">
                    <option value="elearning" selected>การอบรมผ่าน E-learning จาก วช.</option>
                    <option value="nresuan">การอบรมโดยมหาวิทยาลัยนเรศวร 7 องค์ประกอบ</option>
                </select>
            </div>
            
            <!-- E-learning Form -->
            <form id="form1" action="upload1.php" method="POST" enctype="multipart/form-data">
                <div id="elearningSection" class="mb-4">
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    
                    <div class="form-guide mb-3">
                        <i class="fa-solid fa-info-circle me-2"></i>
                        <span>อัพโหลดไฟล์ประกาศนียบัตรที่ได้จากระบบ E-learning ของ วช เท่านั้น </span></br>
                        <a href="https://elearning-labsafety.nrct.go.th/">หากต้องการอบรม คลิกที่นี่</a>
                    </div>
                    
                    <div class="mb-3">
                        <label for="elearningFile" class="form-label">เลือกไฟล์ประกาศนียบัตร</label>
                        <input type="file" class="form-control" id="elearningFile" name="elearning_file">
                    </div>
                    
                    <div class="form-footer">
                        <button type="submit" class="btn btn-modern primary">
                            <i class="fa-solid fa-upload me-2"></i>อัพโหลด
                        </button>
                    </div>
                </div>
            </form>
            
            <!-- NU Form -->
            <form id="form2" action="upload2.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                
                <div id="nresuanSection" class="mb-4" style="display: none;">
                    <div class="form-guide mb-3">
                        <i class="fa-solid fa-info-circle me-2"></i>
                        <span>กรุณาอัปโหลดเอกสารรับรองการอบรมทั้ง 7 องค์ประกอบ</span>
                    </div>
                    
                    <div id="nresuanFiles">
                        <div class="mb-3">
                            <label for="formFile1" class="form-label">องค์ประกอบที่ 1 การบริหารระบบการจัดการด้านความปลอดภัย</label>
                            <input type="file" class="form-control" id="formFile1" name="nresuan_Files[]" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="formFile2" class="form-label">องค์ประกอบที่ 2 ระบบการจัดการสารเคมี</label>
                            <input type="file" class="form-control" id="formFile2" name="nresuan_Files[]" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="formFile3" class="form-label">องค์ประกอบที่ 3 ระบบการจัดการของเสีย</label>
                            <input type="file" class="form-control" id="formFile3" name="nresuan_Files[]" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="formFile4" class="form-label">องค์ประกอบที่ 4 ลักษณะทางกายภาพของห้องปฏิบัติการ อุปกรณ์และเครื่องมือ</label>
                            <input type="file" class="form-control" id="formFile4" name="nresuan_Files[]" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="formFile5" class="form-label">องค์ประกอบที่ 5 ระบบการป้องกันและแก้ไขภัยอันตราย</label>
                            <input type="file" class="form-control" id="formFile5" name="nresuan_Files[]" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="formFile6" class="form-label">องค์ประกอบที่ 6 การให้ความรู้พื้นฐานเกี่ยวกับความปลอดภัยในห้องปฏิบัติการ</label>
                            <input type="file" class="form-control" id="formFile6" name="nresuan_Files[]" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="formFile7" class="form-label">องค์ประกอบที่ 7 การจัดการข้อมูลและเอกสาร</label>
                            <input type="file" class="form-control" id="formFile7" name="nresuan_Files[]" required>
                        </div>
                    </div>
                    
                    <div class="form-footer">
                        <button type="submit" class="btn btn-modern primary">
                            <i class="fa-solid fa-upload me-2"></i>อัพโหลด
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Lab Certification Request Form -->
        <form id="labform" action="request.php" method="post" enctype="multipart/form-data" class="form-section" style="display: none;">
            <h5><i class="fa-solid fa-file-certificate"></i> ขอใบรับรองนักวิจัย</h5>
            
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
            <input type="hidden" name="dr" value="<?php echo $dr; ?>">
            
            <div class="form-guide mb-4">
                <i class="fa-solid fa-info-circle me-2"></i>
                <span>กรุณากรอกข้อมูลให้ครบถ้วนเพื่อดำเนินการขอใบรับรองนักวิจัย</span>
            </div>
            
            <div class="form-grid">
                <div class="mb-3">
                    <label for="labSelect" class="form-label">เลือกห้องปฏิบัติการ</label>
                    <select class="form-select" id="labSelect" name="labselect">
                        <option value="" selected disabled>กรุณาเลือก</option>
                        <?php
                        $query = "SELECT * FROM lab";
                        $stmt = $conn->prepare($query);
                        $stmt->execute();
                        $labs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($labs as $lab): ?>
                            <option value="<?php echo htmlspecialchars($lab['id']); ?>">
                                <?php echo htmlspecialchars($lab['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="purposeSelect" class="form-label">วัตถุประสงค์ของการขอใบรับรอง</label>
                    <select class="form-select" name="purpose" id="purposeSelect">
                        <option value="R">วิจัย</option>
                        <option value="T">การเรียนการสอน</option>
                        <option value="S">งานบริการวิชาการ</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="phone" class="form-label">เบอร์ติดต่อ</label>
                <input type="text" class="form-control" id="phone" name="phone" required>
            </div>
            
            <div class="mb-3">
                <label for="p12_password" class="form-label">รหัสผ่านในการยืนยันตัวตน Digital Signature</label>
                <input type="password" name="p12_password" class="form-control" required>
                <div class="form-hint">รหัสผ่านสำหรับใช้งานลายเซ็นดิจิทัลของท่าน</div>
            </div>
            
            <div class="form-footer">
                <button type="submit" class="btn btn-modern primary">
                    <i class="fa-solid fa-paper-plane me-2"></i>ส่งคำร้อง
                </button>
            </div>
        </form>
        
        <!-- Signature Upload Form -->
        <form id="signForm" action="signadd.php" method="post" enctype="multipart/form-data" class="form-section" style="display: none;">
            <h5><i class="fa-solid fa-signature"></i> อัปโหลดลายเซ็นอิเล็กทรอนิกส์</h5>
            
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
            
            <div class="form-guide mb-4">
                <i class="fa-solid fa-info-circle me-2"></i>
                <span>ลายเซ็นอิเล็กทรอนิกส์จะถูกใช้ในใบรับรองนักวิจัยของท่าน</span>
            </div>
            
            <div class="mb-4">
                <label for="signFileInput" class="form-label">อัพโหลดภาพลายเซ็น</label>
                <input type="file" class="form-control" id="signFileInput" name="sign" required>
                <div class="form-hint">รองรับไฟล์ภาพนามสกุล .jpg, .png หรือ .jpeg</div>
            </div>
            
            <div class="mb-4">
                <label for="p12FileInput" class="form-label">อัพโหลดไฟล์ Digital Signature</label>
                <input type="file" class="form-control" id="p12FileInput" name="p12_file" accept=".p12" required>
                <div class="form-hint">รองรับไฟล์นามสกุล .p12 เท่านั้น</div>
                <a href="https://cert.nu.ac.th" type="button" class="btn btn-modern danger mt-2">
                    <i class="fa-solid fa-external-link-alt me-2"></i>หากไม่มีไฟล์ Digital Signature คลิกที่นี่
                </a>
            </div>
            
            <div class="form-footer">
                <button type="submit" class="btn btn-modern primary">
                    <i class="fa-solid fa-upload me-2"></i>อัพโหลดลายเซ็น
                </button>
            </div>
        </form>
        
        <!-- Document Viewer -->
        <div id="Cert" class="document-viewer" style="display: none;">
            <h5><i class="fa-solid fa-file-certificate"></i> ใบประกาศ</h5>
            <div id="fileDisplay" class="mt-4"></div>
        </div>
        
        <!-- Loading Overlay -->
        <div id="loadingSpinner" class="loading-overlay">
            <div class="loading-spinner"></div>
            <div class="loading-text">กำลังดำเนินการ กรุณารอสักครู่...</div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true
    });
    document.addEventListener('DOMContentLoaded', function () {
    // จัดการการเปลี่ยนประเภท Upload
    const uploadTypeSelect = document.getElementById('uploadType');
    const elearningSectionDiv = document.getElementById('elearningSection');
    const nresuanSectionDiv = document.getElementById('nresuanSection');
    const updateCertButton = document.getElementById('updateCert');
    const labCertButton = document.getElementById('labCert');
    const signAddButton = document.getElementById('signAdd');
    const CertViewButton = document.getElementById('CertView');
    const uploadFormDiv = document.getElementById('uploadForm');
    const labFormDiv = document.getElementById('labform');
    const signFormDiv = document.getElementById('signForm');
    const CertDiv = document.getElementById('Cert');

    if (uploadTypeSelect) {
        uploadTypeSelect.addEventListener('change', function() {
            elearningSectionDiv.style.display = this.value === 'elearning' ? 'block' : 'none';
            nresuanSectionDiv.style.display = this.value === 'elearning' ? 'none' : 'block';
        });
    }

    const switchSection = (section) => {
        uploadFormDiv.style.display = section === 'upload' ? 'block' : 'none';
        labFormDiv.style.display = section === 'lab' ? 'block' : 'none';
        signFormDiv.style.display = section === 'sign' ? 'block' : 'none';
        CertDiv.style.display = section === 'cert' ? 'block' : 'none';
    };

    if (updateCertButton) updateCertButton.addEventListener('click', () => switchSection('upload'));
    if (labCertButton) labCertButton.addEventListener('click', () => switchSection('lab'));
    if (signAddButton) signAddButton.addEventListener('click', () => switchSection('sign'));
    if (CertViewButton) CertViewButton.addEventListener('click', () => switchSection('cert'));
});

$(document).ready(function() {
    // อัปโหลด form1
    $('#form1').submit(function(event) {
        event.preventDefault();
        ajaxSubmit(this, 'upload1.php');
    });

    // อัปโหลด form2
    $('#form2').submit(function(event) {
        event.preventDefault();
        ajaxSubmit(this, 'upload2.php');
    });

    // อัปโหลด signForm
    $('#signForm').submit(function(event) {
        event.preventDefault();
        ajaxSubmit(this, 'signadd.php');
    });

    // อัปโหลด labform
    $('#labform').submit(function(event) {
        event.preventDefault();
        ajaxSubmit(this, 'request.php');
    });

    // เพิ่ม CSS สำหรับ loading overlay
    const loadingOverlayCSS = `
        <style>
            .form-loading-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(255, 255, 255, 0.7);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 1000;
                border-radius: 4px;
            }
            .spinner-container {
                text-align: center;
                background-color: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }
            .loading-text {
                font-size: 14px;
                color: #333;
            }
        </style>
    `;
    
    // เพิ่ม CSS ไปยัง head
    if (!$('head').find('style:contains("form-loading-overlay")').length) {
        $('head').append(loadingOverlayCSS);
    }

    function ajaxSubmit(form, url) {
    // เก็บอ้างอิงไปยังฟอร์ม
    const $form = $(form);
    
    // เก็บอ้างอิงไปยังปุ่มส่งฟอร์ม
    const $submitButton = $form.find('button[type="submit"], input[type="submit"]');
    
    // เก็บข้อความเดิมของปุ่มไว้
    const originalButtonText = $submitButton.html();
    
    // สร้าง Spinner HTML
    const spinnerHtml = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>กำลังประมวลผล...';
    
    // ปิดการใช้งานปุ่มและเปลี่ยนข้อความ
    $submitButton.prop('disabled', true).html(spinnerHtml);
    
    // แสดง loading overlay บนฟอร์ม
    const $loadingOverlay = $('<div class="form-loading-overlay"><div class="spinner-container"><div class="spinner-border text-primary" role="status"></div><div class="loading-text mt-2">กำลังส่งข้อมูล กรุณารอสักครู่...</div></div></div>');
    $form.css('position', 'relative').append($loadingOverlay);
    
    // เริ่มการส่งข้อมูล
    var formData = new FormData(form);
    
    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        dataType: 'json',
        contentType: false,
        processData: false,
        success: function(response) {
            // คืนค่าปุ่มกลับไปเป็นปกติ
            $submitButton.prop('disabled', false).html(originalButtonText);
            
            // ลบ loading overlay
            $form.find('.form-loading-overlay').remove();
            
            // ตรวจสอบว่าเป็น request.php ที่มีการแสดงคะแนนความพึงพอใจหรือไม่
            if ((url.indexOf('request.php') !== -1) && response.showRating && (response.icon === 'success' || response.status === 'success')) {
                // แสดง SweetAlert สำหรับการให้คะแนนความพึงพอใจ
                Swal.fire({
                    title: response.title,
                    text: response.text,
                    icon: response.icon || response.status,
                    confirmButtonText: 'ประเมินความพึงพอใจ',
                    allowOutsideClick: false,
                    html: `
                        <p>${response.text}</p>
                        <p>กรุณาให้คะแนนความพึงพอใจในการใช้บริการ</p>
                        <div class="rating">
                            <i class="far fa-star star" data-rating="1"></i>
                            <i class="far fa-star star" data-rating="2"></i>
                            <i class="far fa-star star" data-rating="3"></i>
                            <i class="far fa-star star" data-rating="4"></i>
                            <i class="far fa-star star" data-rating="5"></i>
                        </div>
                        <p id="selected-rating">คะแนนที่เลือก: <span>0</span>/5</p>
                    `,
                    didOpen: () => {
                        // สร้าง Event listener สำหรับการคลิกดาว
                        const stars = document.querySelectorAll('.star');
                        const ratingText = document.querySelector('#selected-rating span');
                        let selectedRating = 0;
                        
                        stars.forEach(star => {
                            star.style.cursor = 'pointer';
                            star.style.fontSize = '24px';
                            star.style.margin = '0 5px';
                            
                            star.addEventListener('mouseenter', function() {
                                const rating = parseInt(this.getAttribute('data-rating'));
                                highlightStars(rating);
                            });
                            
                            star.addEventListener('mouseleave', function() {
                                highlightStars(selectedRating);
                            });
                            
                            star.addEventListener('click', function() {
                                selectedRating = parseInt(this.getAttribute('data-rating'));
                                ratingText.textContent = selectedRating;
                                highlightStars(selectedRating);
                                
                                // อัปเดตปุ่ม confirm ให้สามารถกดได้เมื่อมีการเลือกคะแนน
                                Swal.getConfirmButton().disabled = false;
                            });
                        });
                        
                        function highlightStars(rating) {
                            stars.forEach(star => {
                                const starRating = parseInt(star.getAttribute('data-rating'));
                                if (starRating <= rating) {
                                    star.classList.remove('far');
                                    star.classList.add('fas');
                                    star.style.color = '#FFD700'; // สีทอง
                                } else {
                                    star.classList.remove('fas');
                                    star.classList.add('far');
                                    star.style.color = '#000';
                                }
                            });
                        }
                        
                        // ปิดปุ่ม confirm จนกว่าจะมีการเลือกคะแนน
                        Swal.getConfirmButton().disabled = true;
                    },
                    preConfirm: () => {
                        const selectedRating = document.querySelector('#selected-rating span').textContent;
                        if (selectedRating === '0') {
                            Swal.showValidationMessage('กรุณาให้คะแนนความพึงพอใจ');
                            return false;
                        }
                        return selectedRating;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const rating = result.value;
                        // ส่งคะแนนไปบันทึกในฐานข้อมูล
                        saveRating(rating, response.user_id).then(() => {
                            // เมื่อบันทึกคะแนนเสร็จแล้ว ให้เปลี่ยนหน้าไปที่ researcher.php
                            window.location.href = 'researcher.php';
                        });
                    } else {
                        // กรณีกดปิด หรือยกเลิก ให้เปลี่ยนหน้าไปที่ researcher.php
                        window.location.href = 'researcher.php';
                    }
                });
            } else {
                // แสดง SweetAlert ปกติถ้าไม่ใช่ request.php หรือไม่ต้องการแสดงการให้คะแนน
                Swal.fire({
                    icon: response.status || response.icon,
                    title: response.title,
                    text: response.text
                }).then(() => {
                    if (response.status === 'success' || response.icon === 'success') {
                        window.location.href = 'researcher.php';
                    }
                });
            }
        },
        error: function(xhr, status, error) {
            // คืนค่าปุ่มกลับไปเป็นปกติ
            $submitButton.prop('disabled', false).html(originalButtonText);
            
            // ลบ loading overlay
            $form.find('.form-loading-overlay').remove();
            
            // If server returns HTML/PHP error page, show a useful snippet instead of generic "parsererror".
            const raw = (xhr && xhr.responseText) ? String(xhr.responseText) : '';
            const snippet = raw ? raw.replace(/<[^>]*>/g, '').trim().slice(0, 300) : '';

            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาดในการส่งข้อมูล!',
                text: snippet || error
            });
        },
        // เพิ่ม timeout เพื่อป้องกันการค้างเมื่อ server ไม่ตอบสนอง
        timeout: 60000, // 60 วินาที
        // กรณี timeout
        statusCode: {
            504: function() {
                // คืนค่าปุ่มกลับไปเป็นปกติ
                $submitButton.prop('disabled', false).html(originalButtonText);
                
                // ลบ loading overlay
                $form.find('.form-loading-overlay').remove();
                
                Swal.fire({
                    icon: 'error',
                    title: 'การเชื่อมต่อหมดเวลา',
                    text: 'ไม่สามารถส่งข้อมูลได้ในเวลาที่กำหนด กรุณาลองใหม่อีกครั้ง'
                });
            }
        }
    });
}

// ฟังก์ชันสำหรับส่งคะแนนไปบันทึกในฐานข้อมูล
function saveRating(score, user_id) {
    console.log("กำลังส่งข้อมูล:", { score, user_id });
    
    return new Promise((resolve, reject) => {
        // สร้าง FormData สำหรับส่งค่า
        const formData = new FormData();
        formData.append('score', score);
        formData.append('user_id', user_id);
        
        $.ajax({
            url: 'save_rating.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(data) {
                console.log("ได้รับข้อมูลตอบกลับ:", data);
                
                if (data.success) {
                    Swal.fire({
                        title: 'ขอบคุณ!',
                        text: 'ขอบคุณสำหรับการประเมินความพึงพอใจ ระบบจะจัดส่งเอกสารให้ท่านภายใน 1-3 วันทำการ',
                        icon: 'success',
                        confirmButtonText: 'ตกลง'
                    }).then(() => {
                        resolve();
                    });
                } else {
                    console.error('เกิดข้อผิดพลาดในการบันทึกคะแนน:', data.message);
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด',
                        text: 'ไม่สามารถบันทึกคะแนนความพึงพอใจได้: ' + (data.message || ''),
                        icon: 'error',
                        confirmButtonText: 'ตกลง'
                    }).then(() => {
                        resolve();
                    });
                }
            },
            error: function(xhr, status, error) {
                // จัดการกับข้อผิดพลาดที่ไม่ใช่ JSON
                console.error('เกิดข้อผิดพลาด XHR:', xhr.responseText);
                console.error('สถานะ:', status);
                console.error('ข้อผิดพลาด:', error);
                
                // พยายามอ่านข้อความผิดพลาด HTML (ถ้ามี)
                let errorMsg = error;
                if (xhr.responseText && xhr.responseText.indexOf('<br') !== -1) {
                    // มีข้อความ HTML error
                    errorMsg = "เกิดข้อผิดพลาดใน PHP - โปรดตรวจสอบ error log";
                }
                
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถบันทึกคะแนนความพึงพอใจได้: ' + errorMsg,
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    resolve();
                });
            }
        });
    });
}

    // แสดงลายเซ็นต์
    const signViewButton = document.getElementById('signView');
    const signPath = '<?php echo isset($sign) ? htmlspecialchars($sign) : ''; ?>';

    if (signViewButton) { // ตรวจสอบว่ามีปุ่มนี้อยู่ในหน้าหรือไม่
        if (signPath) {
            signViewButton.style.display = 'inline-block';
            signViewButton.addEventListener('click', function() {
                Swal.fire({
                    title: 'ภาพลายเซ็นต์',
                    html: `<img src="${signPath}" style="max-width: 100%; height: auto;">`,
                    showConfirmButton: false,
                    showCloseButton: true,
                    width: '80%',
                    padding: '20px'
                });
            });
        } else {
            signViewButton.addEventListener('click', function() {
                Swal.fire('ไม่พบภาพลายเซ็นต์', 'ยังไม่มีภาพลายเซ็นต์ที่อัปโหลด', 'warning');
            });
        }
    }

    // แสดงไฟล์ NU02
    const nuViewButton = document.getElementById('nuView');
    const nuPath = '<?php echo isset($nu02_path) ? htmlspecialchars($nu02_path) : ''; ?>';

    if (nuViewButton) { // ตรวจสอบว่ามีปุ่มนี้อยู่ในหน้าหรือไม่
        if (nuPath) {
            nuViewButton.style.display = 'inline-block';
            nuViewButton.addEventListener('click', function() {
                Swal.fire({
                    title: 'ไฟล์',
                    html: `<iframe src="${nuPath}" width="100%" height="600px" style="border:none;"></iframe>`,
                    showConfirmButton: false,
                    showCloseButton: true,
                    width: '90%',
                    padding: '20px'
                });
            });
        } else {
            nuViewButton.addEventListener('click', function() {
                Swal.fire('ไม่พบไฟล์', 'ยังไม่มีไฟล์ที่อัปโหลด', 'warning');
            });
        }
    }
});
const viewButton01 = document.getElementById('View01');
const filePath01 = '<?php echo isset($nu01_path) ? htmlspecialchars($nu01_path) : ''; ?>';

if (viewButton01) {
    if (filePath01) {
        viewButton01.style.display = 'inline-block';
        viewButton01.addEventListener('click', function() {
            // แสดง loading dialog พร้อม progress bar
            Swal.fire({
                title: 'กำลังโหลดไฟล์...',
                html: `
                    <div class="progress" style="height: 20px; margin-top: 20px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             id="file-progress-bar" 
                             role="progressbar" 
                             style="width: 0%;" 
                             aria-valuenow="0" 
                             aria-valuemin="0" 
                             aria-valuemax="100">0%</div>
                    </div>
                `,
                showConfirmButton: false,
                allowOutsideClick: false,
                didOpen: () => {
                    const progressBar = document.getElementById('file-progress-bar');
                    
                    // จำลองการโหลดไฟล์ (เพิ่มไปทีละ 10%)
                    let progress = 0;
                    const interval = setInterval(() => {
                        progress += 10;
                        progressBar.style.width = progress + '%';
                        progressBar.setAttribute('aria-valuenow', progress);
                        progressBar.textContent = progress + '%';
                        
                        if (progress >= 100) {
                            clearInterval(interval);
                            
                            // เมื่อโหลดเสร็จให้ปิด dialog และเปิดไฟล์ในแท็บใหม่
                            setTimeout(() => {
                                Swal.close();
                                // เปิดไฟล์ในแท็บใหม่
                                window.open(filePath01, '_blank');
                            }, 500);
                        }
                    }, 200);
                }
            });
        });
    } else {
        viewButton01.addEventListener('click', function() {
            Swal.fire('ไม่พบไฟล์', 'กรุณาติดต่อห้องปฏิบัติการ', 'warning');
        });
    }
}



// โหลดใบประกาศ (Cert) พร้อม Spinner
document.addEventListener('DOMContentLoaded', function() {
    var cert = <?php echo json_encode($cert); ?>;
    var certType = "<?php echo $certtype; ?>";

    var certDiv = document.getElementById('Cert');
    var fileDisplay = document.getElementById('fileDisplay');
    var loadingSpinner = document.getElementById('loadingSpinner');
    var certViewButton = document.getElementById('CertView');

    // ตัวแปรสำหรับเก็บสถานะการแสดงผล
    var isDisplayed = false;
    var filesLoaded = false;

    // ซ่อน certDiv ไว้ตั้งแต่เริ่มต้น
    if (certDiv) {
        certDiv.style.display = 'none';
    }

    if (!certDiv || !fileDisplay || !loadingSpinner || !certViewButton) {
        console.error('ไม่พบองค์ประกอบในหน้า HTML');
        return;
    }

    // เพิ่ม Event Listener สำหรับปุ่ม CertView
    certViewButton.addEventListener('click', function() {
        if (!isDisplayed) {
            // แสดงใบประกาศ
            certDiv.style.display = 'block';
            isDisplayed = true;
            
            // เปลี่ยนข้อความปุ่ม (ถ้าต้องการ)
            certViewButton.textContent = 'ซ่อนใบประกาศ';
            
            // โหลดไฟล์ครั้งแรกเท่านั้น
            if (!filesLoaded) {
                loadCertificate();
                filesLoaded = true;
            }
        } else {
            // ซ่อนใบประกาศ
            certDiv.style.display = 'none';
            isDisplayed = false;
            
            // เปลี่ยนข้อความปุ่มกลับ
            certViewButton.textContent = 'ดูใบประกาศ';
        }
    });

    // ฟังก์ชันสำหรับโหลดใบประกาศ
    function loadCertificate() {
        loadingSpinner.style.display = 'block';

        var filePath = null;
        var isPdf = false;
        var isImage = false;

        if (certType === "elearning") {
            if (cert.pdf) {
                filePath = cert.pdf;
                isPdf = true;
            } else if (cert.image) {
                filePath = cert.image;
                isImage = true;
            }
        } else if (certType === "nu") {
            // สำหรับ certType = "nu" จะแสดงไฟล์ทั้ง 7 องค์ประกอบ
            loadingSpinner.style.display = 'none';
            displayMultipleFiles();
            return;
        } else if (cert && cert.path) {
            filePath = cert.path;
            isPdf = filePath.toLowerCase().endsWith('.pdf');
            isImage = /\.(jpg|jpeg|png|gif|bmp|webp)$/i.test(filePath);
        }

        if (!filePath) {
            loadingSpinner.style.display = 'none';
            fileDisplay.innerHTML = "<p style='color: red;'>ไม่พบใบประกาศของคุณ</p>";
            return;
        }

        var xhr = new XMLHttpRequest();
        xhr.open('GET', filePath, true);
        xhr.responseType = 'blob';
        xhr.onload = function() {
            loadingSpinner.style.display = 'none';
            if (xhr.status === 200) {
                var fileURL = URL.createObjectURL(xhr.response);
                if (isPdf) {
                    fileDisplay.innerHTML = `<iframe src="${fileURL}" width="100%" height="600px" style="border:none; box-shadow: 0 2px 8px rgba(0,0,0,0.2);"></iframe>`;
                } else if (isImage) {
                    fileDisplay.innerHTML = `<img src="${fileURL}" style="max-width: 100%; height: auto; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">`;
                }
            } else {
                fileDisplay.innerHTML = "<p style='color: red;'>เกิดข้อผิดพลาดในการโหลดใบประกาศ</p>";
            }
        };
        xhr.onerror = function() {
            loadingSpinner.style.display = 'none';
            fileDisplay.innerHTML = "<p style='color: red;'>เกิดข้อผิดพลาดในการโหลดใบประกาศ</p>";
        };
        xhr.send();
    }

    // ฟังก์ชันสำหรับแสดงไฟล์หลายองค์ประกอบ (NU)
    function displayMultipleFiles() {
        var fileListHTML = '<div style="display: flex; flex-direction: column; gap: 20px;">';
        
        // วนลูปผ่านไฟล์ทั้ง 7 องค์ประกอบ
        for (var i = 1; i <= 7; i++) {
            if (cert[i] && cert[i].path) {
                var filePath = cert[i].path;
                var fileNumber = i;
                var isPdf = filePath.toLowerCase().endsWith('.pdf');
                var isImage = /\.(jpg|jpeg|png|gif|bmp|webp)$/i.test(filePath);
                
                fileListHTML += `
                    <div style="margin-bottom: 20px; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <div style="background-color: #f5f5f5; padding: 10px; font-weight: bold; border-bottom: 1px solid #ddd;">
                            องค์ประกอบที่ ${fileNumber}
                        </div>
                        <div style="padding: 15px;">
                            <div id="fileContainer${fileNumber}" style="min-height: 100px; display: flex; align-items: center; justify-content: center;">
                                <div style="text-align: center;">
                                    <div style="margin-bottom: 10px;">
                                        <span style="font-size: 14px; color: #666;">กำลังโหลด...</span>
                                    </div>
                                    <div style="width: 30px; height: 30px; border: 3px solid #f3f3f3; border-top: 3px solid #3498db; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
        }
        
        fileListHTML += '</div>';
        
        // เพิ่ม CSS สำหรับ loading animation
        fileListHTML += `
            <style>
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            </style>
        `;
        
        fileDisplay.innerHTML = fileListHTML;
        
        // โหลดไฟล์แต่ละองค์ประกอบ
        for (var i = 1; i <= 7; i++) {
            if (cert[i] && cert[i].path) {
                loadFileForContainer(cert[i].path, i);
            }
        }
    }

    // ฟังก์ชันสำหรับโหลดไฟล์แต่ละองค์ประกอบ
    function loadFileForContainer(filePath, fileNumber) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', filePath, true);
        xhr.responseType = 'blob';
        
        xhr.onload = function() {
            var container = document.getElementById('fileContainer' + fileNumber);
            if (xhr.status === 200) {
                var fileURL = URL.createObjectURL(xhr.response);
                var isPdf = filePath.toLowerCase().endsWith('.pdf');
                var isImage = /\.(jpg|jpeg|png|gif|bmp|webp)$/i.test(filePath);
                
                if (isPdf) {
                    container.innerHTML = `<iframe src="${fileURL}" width="100%" height="400px" style="border:none; border-radius: 4px;"></iframe>`;
                } else if (isImage) {
                    container.innerHTML = `<img src="${fileURL}" style="max-width: 100%; height: auto; border-radius: 4px;">`;
                } else {
                    container.innerHTML = `<p style='color: #666;'>ไฟล์: ${filePath.split('/').pop()}</p>`;
                }
            } else {
                container.innerHTML = `<p style='color: red;'>เกิดข้อผิดพลาดในการโหลดองค์ประกอบที่ ${fileNumber}</p>`;
            }
        };
        
        xhr.onerror = function() {
            var container = document.getElementById('fileContainer' + fileNumber);
            container.innerHTML = `<p style='color: red;'>เกิดข้อผิดพลาดในการโหลดองค์ประกอบที่ ${fileNumber}</p>`;
        };
        
        xhr.send();
    }
});

function checkAndClearSession() {
    fetch('clear_session.php')
        .then(response => response.json())
        .then(data => {
            if (data.cleared) {
                Swal.fire({
                    title: 'Session หมดอายุ!',
                    text: data.message,
                    icon: 'warning',
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    // ทำการ Redirect ไปยังหน้า Login หรือ Reload หน้าเว็บ
                    window.location.href = 'login.php'; // เปลี่ยนเป็น URL หน้า Login ของคุณ
                    // หรือ window.location.reload();
                });
            }
        })
        .catch(error => {
            console.error('เกิดข้อผิดพลาดในการตรวจสอบ Session:', error);
        });
}

document.addEventListener('DOMContentLoaded', function() {
    const denyButton = document.getElementById('deny');

    if (denyButton) {
        denyButton.addEventListener('click', function() {
            Swal.fire({
                title: 'ยืนยันการยกเลิกเอกสาร',
                text: 'คุณต้องการยกเลิกเอกสารนี้ใช่หรือไม่?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ใช่, ยกเลิก!',
                cancelButtonText: 'ไม่, ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    // ส่งข้อมูลแบบ POST ไปยัง PHP endpoint สำหรับลบข้อมูล
                    fetch('delete_request.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        // ไม่ต้องส่ง user_id ใน Body แล้ว
                        body: '',
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            Swal.fire(
                                'ยกเลิก!',
                                data.message,
                                'success'
                            ).then(() => {
                                // ทำการ Redirect หรือ Reload หน้าเพื่ออัปเดตข้อมูล
                                window.location.href = 'researcher.php'; // หรือ window.location.href = 'your_page.php';
                            });
                        } else {
                            Swal.fire(
                                'เกิดข้อผิดพลาด!',
                                data.message,
                                'error'
                            );
                        }
                    })
                    .catch(error => {
                        console.error('เกิดข้อผิดพลาดในการลบ:', error);
                        Swal.fire(
                            'เกิดข้อผิดพลาด!',
                            'ไม่สามารถลบเอกสารได้: ' + error.message,
                            'error'
                        );
                    });
                }
            });
        });
    }
});




document.getElementById('viewFileBtn').addEventListener('click', function() {
    const userId = this.getAttribute('data-user-id');

    fetch('nu01view.php?user_id=' + encodeURIComponent(userId))
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    title: 'ดูไฟล์ PDF',
                    html: `<iframe src="${data.path}" width="100%" height="500px"></iframe>`,
                    width: 800,
                    showCloseButton: true,
                    showConfirmButton: false,
                    focusConfirm: false
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => {
            Swal.fire('Error', error.message, 'error');
        });
});

function handleResponse(response) {
    if (response.success) {
        if (response.showRating) {
            // แสดง SweetAlert สำหรับการให้คะแนนความพึงพอใจ
            Swal.fire({
                title: response.title,
                text: response.text,
                icon: response.icon,
                confirmButtonText: 'ประเมินความพึงพอใจ',
                allowOutsideClick: false,
                html: `
                    <p>${response.text}</p>
                    <p>กรุณาให้คะแนนความพึงพอใจในการใช้งานระบบ</p>
                    <div class="rating">
                        <i class="far fa-star star" data-rating="1"></i>
                        <i class="far fa-star star" data-rating="2"></i>
                        <i class="far fa-star star" data-rating="3"></i>
                        <i class="far fa-star star" data-rating="4"></i>
                        <i class="far fa-star star" data-rating="5"></i>
                    </div>
                    <p id="selected-rating">คะแนนที่เลือก: <span>0</span>/5</p>
                `,
                didOpen: () => {
                    // สร้าง Event listener สำหรับการคลิกดาว
                    const stars = document.querySelectorAll('.star');
                    const ratingText = document.querySelector('#selected-rating span');
                    let selectedRating = 0;
                    
                    stars.forEach(star => {
                        star.style.cursor = 'pointer';
                        star.style.fontSize = '24px';
                        star.style.margin = '0 5px';
                        
                        star.addEventListener('mouseenter', function() {
                            const rating = parseInt(this.getAttribute('data-rating'));
                            highlightStars(rating);
                        });
                        
                        star.addEventListener('mouseleave', function() {
                            highlightStars(selectedRating);
                        });
                        
                        star.addEventListener('click', function() {
                            selectedRating = parseInt(this.getAttribute('data-rating'));
                            ratingText.textContent = selectedRating;
                            highlightStars(selectedRating);
                            
                            // อัปเดตปุ่ม confirm ให้สามารถกดได้เมื่อมีการเลือกคะแนน
                            Swal.getConfirmButton().disabled = false;
                        });
                    });
                    
                    function highlightStars(rating) {
                        stars.forEach(star => {
                            const starRating = parseInt(star.getAttribute('data-rating'));
                            if (starRating <= rating) {
                                star.classList.remove('far');
                                star.classList.add('fas');
                                star.style.color = '#FFD700'; // สีทอง
                            } else {
                                star.classList.remove('fas');
                                star.classList.add('far');
                                star.style.color = '#000';
                            }
                        });
                    }
                    
                    // ปิดปุ่ม confirm จนกว่าจะมีการเลือกคะแนน
                    Swal.getConfirmButton().disabled = true;
                },
                preConfirm: () => {
                    const selectedRating = document.querySelector('#selected-rating span').textContent;
                    if (selectedRating === '0') {
                        Swal.showValidationMessage('กรุณาให้คะแนนความพึงพอใจ');
                        return false;
                    }
                    return selectedRating;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const rating = result.value;
                    // ส่งคะแนนไปบันทึกในฐานข้อมูล
                    saveRating(rating, response.user_id);
                }
            });
        } else {
            // แสดง SweetAlert ปกติถ้าไม่ต้องการแสดงการให้คะแนน
            Swal.fire({
                title: response.title,
                text: response.text,
                icon: response.icon,
                confirmButtonText: 'ตกลง'
            });
        }
    } else {
        // แสดง SweetAlert กรณีเกิดข้อผิดพลาด
        Swal.fire({
            title: response.title,
            text: response.text,
            icon: response.icon,
            confirmButtonText: 'ตกลง'
        });
    }
}

// ฟังก์ชันสำหรับส่งคะแนนไปบันทึกในฐานข้อมูล
function saveRating(score, user_id) {
    console.log("กำลังส่งข้อมูล:", { score, user_id }); // เพิ่มบรรทัดนี้
    
    return new Promise((resolve, reject) => {
        $.ajax({
            url: 'save_rating.php',
            type: 'POST',
            data: JSON.stringify({
                score: score,
                user_id: user_id
            }),
            dataType: 'json',
            contentType: 'application/json',
            success: function(data) {
                console.log("ได้รับข้อมูลตอบกลับ:", data); // เพิ่มบรรทัดนี้
                
                if (data.success) {
                    // ...
                } else {
                    console.error('เกิดข้อผิดพลาดในการบันทึกคะแนน:', data.message); // เพิ่ม data.message
                    // ...
                }
            },
            error: function(xhr, status, error) {
                console.error('เกิดข้อผิดพลาด XHR:', xhr.responseText); // เพิ่มบรรทัดนี้
                console.error('สถานะ:', status);
                console.error('ข้อผิดพลาด:', error);
                // ...
            }
        });
    });
}



// ตั้งเวลาให้ตรวจสอบ Session ทุกนาที (60000 milliseconds)
setInterval(checkAndClearSession, 600000);

// เรียกฟังก์ชันตรวจสอบ Session ครั้งแรกเมื่อหน้าเว็บโหลด
checkAndClearSession();


    </script>
   
</body>
</html>
