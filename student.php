<?php

  if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION['user_id']);
    header("location: index.php");
  }



require_once 'function.php';

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
    json_encode($cert, JSON_UNESCAPED_SLASHES);
    json_encode($certtype);  // ส่งข้อมูล JSON กลับไปยัง client
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
   $nu01_path = getnu01($conn, $loggedInUserId);

   $studentId = $_SESSION["user_id"];
    $fullName = $_SESSION["std_prefix"] . $_SESSION["std_firstname"] . " " . $_SESSION["std_lastname"];
    $program = $_SESSION["std_program"];
    $faculty = $_SESSION["std_faculty"];
    $department = $_SESSION["std_department"];
    $email = $_SESSION["STDMAIL"];
    $level = $_SESSION["level"];


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


    
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background-image: url('./vendor/image/background.jpg');
        }
        .logo-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: 2px solid #f0f0f0;
            padding: 5px;
        }
        
        /* สไตล์สำหรับรูปภาพโลโก้ */
        .logo-circle img {
            max-width: 80%;
            max-height: 80%;
            object-fit: contain;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0069d9;
            border-color: #0062cc;
        }
        .file-preview {
            width: 100%; /* ปรับความกว้างให้เต็ม container */
            height: auto; /* ปรับความสูงตามสัดส่วนภาพ */
            border: 1px solid #ccc;
            margin-bottom: 10px;
            display: block; /* สำคัญ: ทำให้เป็น block element เพื่อให้ width 100% ทำงาน */
        }

        .file-preview.pdf {
            height: 500px; /* กำหนดความสูงเริ่มต้นสำหรับ PDF (สามารถปรับได้ตามต้องการ) */
        }

        .table-container {
            margin-top: 20px;
            width: 100%; /* ปรับความกว้างตารางให้เต็ม container */
            overflow-x: auto; /* เพิ่ม scrollbar ในแนวนอนถ้าเนื้อหาเกิน */
        }

        .table {
            width: 100%; /* ปรับความกว้างของตารางภายใน */
        }

        .file-link {
            cursor: pointer;
            color: blue;
            text-decoration: underline;
            display: block; /* ทำให้เป็น block เพื่อให้คลิกง่ายขึ้นถ้าอยู่ใน cell ที่มี padding */
            width: 100%; /* ขยายให้เต็ม cell */
            text-align: center; /* จัดข้อความกลาง */
        }

        /* สำหรับรูปภาพใน SweetAlert */
        .swal2-html-container img {
            max-width: 100%;
            height: auto;
        }

        /* สำหรับ iframe (PDF) ใน SweetAlert */
        .swal2-html-container iframe {
            width: 100%;
            height: 500px; /* ปรับความสูง iframe ใน popup ได้ตามต้องการ */
        }
            .spinner-container {
            position: absolute; /* หรือ fixed ขึ้นอยู่กับ layout */
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* พื้นหลังกึ่งโปร่งใส */
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000; /* ให้ spinner อยู่เหนือเนื้อหาอื่นๆ */
            display: none; /* ซ่อนไว้ตอนเริ่มต้น */
            }

            .spinner {
            border: 4px solid rgba(0, 0, 0, 0.3);
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
            }

            @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
            }
                    

    </style>
</head>



<body class="bg-light">

    <div class="container-fluid py-2 ml-5 mr-5">

    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <!-- Desktop Layout (แสดงแถวเดียว) -->
            <div class="d-none d-md-flex justify-content-between align-items-center">
                <!-- โลโก้และชื่อระบบ (ด้านซ้าย) -->
                <div class="d-flex align-items-center">
                    <div class="logo-circle me-3">
                        <img src="./vendor/image/logo.jpg" alt="Logo">
                    </div>
                    <div>
                        <h5 class="mb-1">ระบบออกใบรับรองนักวิจัยเพื่อใช้ประกอบการขอทุนสนับสนุนการวิจัย คณะเกษตรศาสตร์ฯ </h5>
                        <p class="text-muted mb-0">เฉพาะนิสิตคณะเกษตรศาสตร์ฯ</p>
                    </div>
                </div>
                
                <!-- ข้อมูลผู้ใช้และปุ่มออกจากระบบ (ด้านขวา) -->
                <div class="d-flex align-items-center">
                    <div class="text-end me-3">
                        <span class="d-block text-primary fw-bold"><?php echo htmlspecialchars($fullName); ?></span>
                    </div>
                    <a href="./student.php?logout" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-box-arrow-right me-1"></i>ออกจากระบบ
                    </a>
                </div>
            </div>
            
            <!-- Mobile Layout (แสดงแบบซ้อนกัน) -->
            <div class="d-md-none">
                <!-- ส่วนบน: โลโก้และชื่อระบบ -->
                <div class="d-flex align-items-center mb-2">
                    <div class="logo-circle me-2">
                        <img src="./vendor/image/logo.jpg" alt="Logo">
                    </div>
                    <div>
                        <h6 class="mb-1">ระบบออกใบรับรองนักวิจัย</h6>
                        <p class="text-muted small mb-0">คณะเกษตรศาสตร์ฯ (ส่วนของนักวิจัย)</p>
                    </div>
                </div>
                
                <!-- ส่วนล่าง: ข้อมูลผู้ใช้และปุ่มออกจากระบบ -->
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <div>
                        <span class="small text-primary fw-bold"><?php echo htmlspecialchars($fullName); ?></span>
                    </div>
                    <a href="./student.php?logout" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-box-arrow-right"></i> ออก
                    </a>
                </div>
            </div>
        </div>
    </div>


        <div class="row justify-content-center">

            <div class="col-md-12">
                <div class="card p-4">
                    
                    <!-- <p class="text-center mb-4">ผู้ใช้งาน: </p> -->
                    <hr>
                    <div class="alert alert-primary" role="alert">
                        <p>รหัสนิสิต: <?php echo $studentId ;?></p>
                        <p>ระดับการศึกษา: <?php echo $level; ?> | สาขา: <?php echo  $program ;?></p>
                        <p>อีเมล์ที่ใช้ในการรับเอกสาร: <?php echo $email.'@nu.ac.th';?></p>
                       <hr>
                       <p>
                            <?php
                                echo ($uploadId == 0) ? '<span style="color: red;">ยังไม่ได้อัปโหลดใบประกาศ</span>' : '<span style="color: green;">' . htmlspecialchars($approvalStatus) . '</span>';
                            ?>
                        </p>
                        <p>
                            <?php
                                echo ($sign == 0) ? '<span style="color: red;">ยังไม่ได้อัปโหลดลายเซ็น</span>' : '<button id="signView" class="btn btn-info mb-3" style="display: none;">ดูภาพลายเซ็นต์</button>';
                            ?>
                        </p>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                            <p>
                                <?php
                                    echo ($stage>0 && $stage<5) ? '<button id="nuView" class="btn btn-success mb-3 ml-3" >ดูเอกสาร NU-LAB-02 ของท่าน</button>' : '';
                                ?>
                            </p>
                            <p>
                                <?php
                                    if($stage>0 && $stage<5) {
                                        echo  '<button id="deny" class="btn btn-danger mb-3 ml-3" >ยกเลิกเอกสาร</button>' ;
                                    }
                                ?>
                            </p>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                            <p>
                                <?php
                                    echo ($stage>5) ? '<button id="View01" class="btn btn-success mb-3 ml-3" >เอกสาร NU-LAB-01 ของท่าน</button>' : '';
                                ?>
                            </p>
                            <p>
                                <?php
                                    if($stage>5) {
                                        echo  '<button id="deny" class="btn btn-danger mb-3 ml-3" >ยกเลิกเอกสาร</button>' ;
                                    }
                                ?>
                            </p>
                        </div>


                     
                          
                        <?php 
                        echo ($stage>0) ? '<p>ความคืบหน้า: </p>' :'';
                        if($stage == 1){
                            echo '
                            <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar bg-info" style="width: 25%">รอห้องปฎิบัติการอนุมัติ</div>
                          </div>';
                        }
                        if($stage == 2){
                            echo '<div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar bg-info" style="width: 50%">รอผู้บริหารอนุมัติ</div>
                          </div>';
                        }
                        if($stage == 3){
                            echo '<div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar bg-info" style="width: 75%">รอมหาลัย/กองวิจัยอนุมัติ</div>
                          </div>';
                          echo '<button id="viewFileBtn" data-user-id="' . $loggedInUserId . '" class="btn btn-success mt-2 mb-3 ml-3">ดูเอกสาร NU-LAB-01 ของท่าน</button>';

                        }
                        if($stage == 4){
                            echo '<div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar bg-danger" style="width: 100%">ห้องปฎิบัติการไม่อนุมัติ</div>
                          </div>';
                        }
                        if($stage == 5){
                            echo '<div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar bg-danger" style="width: 100%">ผู้บริหารไม่อนุมัติ</div>
                          </div>';
                        }
                        if($stage == 6){
                            echo '<div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar bg-success" style="width: 100%">เอกสารรับรองนักวิจัย NU-Lab-01 อนุมัติเเล้ว</div>
                          </div>';
                        }
                         ?>
                         
                       
                    </div>

                    <div class="d-flex justify-content-start gap-3">
                        <?php
                        if($uploadId>0){ ?>
                            <button id="CertView" class="btn btn-success mb-3">ดูไฟล์ประกาศนียบัตร</button>
                            <button id="updateCert" class="btn btn-warning mb-3">แก้ไขไฟล์ประกาศนียบัตร</button>
                        <?php } else{ ?>
                            <button id="updateCert" class="btn btn-primary mb-3">อัพโหลดไฟล์ประกาศนียบัตร</button>
                        <?php } ?>

                        <?php
                                echo ($uploadId == 0) ? '<button id="signAdd" class="btn btn-primary mb-3" disabled>ลายเซ็นอิเล็กทรอนิกส์ (กรุณาอัพโหลดไฟล์ประกาศนียบัตรก่อน)</button>' : ' <button id="signAdd" class="btn btn-primary mb-3">ลายเซ็นอิเล็กทรอนิกส์</button>';
                            ?>
               
                        <?php
                                if($sign == 0) {
                                    echo '<button id="labCert" class="btn btn-primary mb-3" disabled>ขอใบรับรองนักวิจัย (กรุณาอัพโหลดลายเซ็นต์ของท่านก่อน)</button>' ;
                                }
                                else{
                                    if($stage>0){
                                        echo '<button id="labCert" class="btn btn-primary mb-3" disabled>ขอใบรับรองนักวิจัย (อยู่ระหว่างกระบวนการ)</button>';
                                    }
                                    else{
                                        echo '<button id="labCert" class="btn btn-primary mb-3">ขอใบรับรองนักวิจัย</button>';
                                    }
                                }
            
                            ?>

                        
                    </div>

                    <div id="uploadForm" style="display: none;">
                        <div class="mb-3">
                            <label for="upload_type" class="form-label">ประเภทการอัพโหลด:</label>
                            <select class="form-select" id="uploadType" name="upload_type">
                                <option value="elearning" selected>การอบรมผ่าน E-learning จาก วช.</option>
                                <option value="nresuan">การอบรมโดยมหาวิทยาลัยนเรศวร 7 องค์ประกอบ</option>
                            </select>
                        </div>

                        <form id="form1" action="upload1.php" method="POST" enctype="multipart/form-data">
                            <div id="elearningSection" class="mb-3">
                                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                <div class="alert alert-primary" role="alert">
                                    <label for="elearningFile" class="form-label">อัพโหลดไฟล์ประกาศนียบัตรที่ได้จากระบบ E-learning ของ วช เท่านั้น</label></div>
                                <input type="file" class="form-control" id="elearningFile" name="elearning_file">
                                <div class="spinner-container">
                                    <div class="spinner"></div>
                                </div>
                                <button type="submit" class="btn btn-primary">อัพโหลด</button>
                            </div>
                        </form>

                        <form id="form2" action="upload2.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                            <div id="nresuanSection" class="mb-3" style="display: none;">
                                <div id="nresuanFiles">
                                    <div class="row mb-2">
                                        <label for="formFile1" class="col-md-6 col-form-label">องค์ประกอบที่ 1 การบริหารระบบการจัดการด้านความปลอดภัย</label>
                                        <div class="col-md-6">
                                            <input type="file" class="form-control" id="formFile1" name="nresuan_Files[]" required>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <label for="formFile2" class="col-md-6 col-form-label">องค์ประกอบที่ 2 ระบบการจัดการสารเคมี</label>
                                        <div class="col-md-6">
                                            <input type="file" class="form-control" id="formFile2" name="nresuan_Files[]"required>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <label for="formFile3" class="col-md-6 col-form-label">องค์ประกอบที่ 3 ระบบการจัดการของเสีย</label>
                                        <div class="col-md-6">
                                            <input type="file" class="form-control" id="formFile3" name="nresuan_Files[]"required>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <label for="formFile4" class="col-md-6 col-form-label">องค์ประกอบที่ 4 ลักษณะทางกายภาพของห้องปฏิบัติการ อุปกรณ์และเครื่องมือ</label>
                                        <div class="col-md-6">
                                            <input type="file" class="form-control" id="formFile4" name="nresuan_Files[]"required>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <label for="formFile5" class="col-md-6 col-form-label">องค์ประกอบที่ 5 ระบบการป้องกันและแก้ไขภัยอันตราย</label>
                                        <div class="col-md-6">
                                            <input type="file" class="form-control" id="formFile5" name="nresuan_Files[]"required>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <label for="formFile6" class="col-md-6 col-form-label">องค์ประกอบที่ 6 การให้ความรู้พื้นฐานเกี่ยวกับความปลอดภัยในห้องปฏิบัติการ</label>
                                        <div class="col-md-6">
                                            <input type="file" class="form-control" id="formFile6" name="nresuan_Files[]"required>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <label for="formFile7" class="col-md-6 col-form-label">องค์ประกอบที่ 7 การจัดการข้อมูลและเอกสาร </label>
                                        <div class="col-md-6">
                                            <input type="file" class="form-control" id="formFile7" name="nresuan_Files[]"required>
                                        </div>
                                    </div>
                                </div>
                                <div class="spinner-container">
                                    <div class="spinner"></div>
                                </div>
                                <button type="submit" class="btn btn-primary">อัพโหลด</button>
                            </div>
                        </form>
                    </div>

                    <form id="labform" action="request.php" method="post" enctype="multipart/form-data" style="display: none;">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
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

                        <div class="mb-3">
                            <label for="phone" class="form-label">เบอร์ติดต่อ</label>
                            <input type="text" class="form-control" id="phone" name="phone" required>
                        </div>

                        <div class="mb-3">
                            <label for="p12_password"class="form-label">รหัสผ่านในการยืนยันตัวตน Digital Signature</label>
                            <input type="password" name="p12_password" class="form-control" required>

                        </div>
                        
                        <div class="spinner-container">
                            <div class="spinner"></div>
                        </div>
                            <button type="submit" class="btn btn-primary">ส่งคำร้อง</button>
                            
                        

                    </form>

                    



                    <form id="signForm" action="signadd.php" method="post" enctype="multipart/form-data" style="display: none;">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

                        <div class="mb-3">
                            <label for="signFileInput" class="form-label">อัพโหลดภาพลายเซนต์</label>
                            <input type="file" class="form-control" id="signFileInput" name="sign" required>
                        </div>

                        <div class="mb-3">
                            <label for="signFileInput" class="form-label">อัพโหลดไฟล์ Digital Signature</label>
                            <input type="file" class="form-control" id="p12FileInput" name="p12_file" accept=".p12"  required>
                            <a href="https://cert.nu.ac.th" type="button" class="btn btn-danger btn-sm mt-2">หากไม่มีไฟล์ Digital Signature คลิกที่นี่</a>
                        </div>
                        <div class="spinner-container">
                            <div class="spinner"></div>
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">อัพโหลดลายเซนต์</button>
                        </div>
                    </form>

                    <div id="loadingSpinner" style="display: none; text-align: center; margin-top: 20px;">
                        <i class="fa fa-spinner fa-spin" style="font-size:24px;"></i> กำลังโหลด...
                    </div>


                    <!-- ส่วนใบประกาศ -->
                        <div id="Cert" style="display: none; margin-top: 20px;">
                            <h4>ใบประกาศ</h4>
                        <div id="fileDisplay" style="margin-top: 20px;"></div>
                        </div>

                </div>
            </div>
        </div>
    </div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>

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
                
                Swal.fire({
                    icon: response.status || response.icon,
                    title: response.title,
                    text: response.text
                }).then(() => {
                    if (response.status === 'success' || response.icon === 'success') {
                        window.location.href = 'student.php';
                    }
                });
            },
            error: function(xhr, status, error) {
                // คืนค่าปุ่มกลับไปเป็นปกติ
                $submitButton.prop('disabled', false).html(originalButtonText);
                
                // ลบ loading overlay
                $form.find('.form-loading-overlay').remove();
                
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาดในการส่งข้อมูล!',
                    text: error
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
                                window.location.href = 'student.php'; // หรือ window.location.href = 'your_page.php';
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


// ตั้งเวลาให้ตรวจสอบ Session ทุกนาที (60000 milliseconds)
setInterval(checkAndClearSession, 600000);

// เรียกฟังก์ชันตรวจสอบ Session ครั้งแรกเมื่อหน้าเว็บโหลด
checkAndClearSession();


    </script>
   
</body>
</html>

