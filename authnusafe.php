<?php
session_start();
?>
<!DOCTYPE html>
<html lang="th" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการใบรับรองนักวิจัยเพื่อใช้ประกอบในการขอทุน</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts - Prompt (Thai) -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- SweetAlert2 for better alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #ff7043; /* สีส้ม */
            --primary-light: #ffa270; /* สีส้มอ่อน */
            --primary-dark: #c63f17; /* สีส้มเข้ม */
            --accent-color: #ffab91; /* สีส้มเน้น */
            --text-color: #333333;
            --light-bg: #f5f5f5; /* พื้นหลังสีเทาอ่อน */
            --gray-color: #757575; /* สีเทากลาง */
            --dark-gray: #424242; /* สีเทาเข้ม */
            --card-shadow: 0 8px 30px rgba(0, 0, 0, 0.05);
        }
        
        body {
            font-family: 'Prompt', sans-serif;
            background-image: url('./vendor/image/nusafe1.jpg');
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            position: relative;
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 112, 67, 0.6) 0%, rgba(66, 66, 66, 0.8) 100%);
            z-index: -1;
        }
        
        .main-content {
            flex: 1 0 auto;
            display: flex;
            align-items: center;
            padding: 40px 0;
            position: relative;
            z-index: 1;
        }
        
        .login-card {
            background-color: white;
            border-radius: 12px;
            border: none;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(255, 112, 67, 0.15);
        }
        
        .login-header {
            position: relative;
            padding-bottom: 1rem;
        }
        
        .logo-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: white;
            box-shadow: 0 2px 15px rgba(255, 112, 67, 0.2);
            border: 3px solid rgba(255, 112, 67, 0.1);
            padding: 2px;
            margin: 0 auto 15px;
            transition: transform 0.3s ease;
        }
        
        .logo-circle:hover {
            transform: scale(1.05);
            border-color: var(--primary-color);
        }
        
        .logo-circle img {
            max-width: 85%;
            max-height: 85%;
            object-fit: contain;
        }
        
        .system-title {
            color: var(--primary-dark);
            font-weight: 600;
            font-size: 1.3rem;
            text-align: center;
            margin-bottom: 5px;
            line-height: 1.4;
        }
        
        .faculty-name {
            color: var(--gray-color);
            font-size: 0.95rem;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .login-divider {
            position: relative;
            height: 1px;
            background: linear-gradient(90deg, 
                rgba(255,255,255,0) 0%, 
                rgba(255, 112, 67, 0.3) 50%, 
                rgba(255,255,255,0) 100%);
            margin: 1.5rem 0;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 0.6rem 1rem;
            border: 1px solid #e0e0e0;
            background-color: #f9f9f9;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(255, 112, 67, 0.15);
            background-color: white;
        }
        
        .form-control::placeholder {
            color: #aaa;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 112, 67, 0.3);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        
        .loading-spinner {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .spinner-border {
            color: var(--primary-color);
        }
        
        .loading-text {
            margin-top: 1rem;
            color: var(--primary-color);
            font-weight: 500;
        }
        
        /* เพิ่ม AOS animations */
        .login-card {
            overflow: visible; /* ทำให้ animation ไม่ถูกตัด */
        }
        
        .pulse-effect {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 112, 67, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(255, 112, 67, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(255, 112, 67, 0);
            }
        }
        
        /* Responsive Adjustments */
        @media (max-width: 767.98px) {
            .main-content {
                padding: 20px 0;
            }
            
            .login-card {
                padding: 1.5rem;
            }
            
            .system-title {
                font-size: 1.1rem;
            }
            
            .logo-circle {
                width: 70px;
                height: 70px;
            }
        }
        
        @media (max-width: 575.98px) {
            .system-title {
                font-size: 1rem;
                padding: 0 10px;
            }
            
            .logo-circle {
                width: 60px;
                height: 60px;
            }
        }
        .footer {
            background-color: white;
            margin-top: auto;
            padding: 1.5rem 0;
            flex-shrink: 0;
        }
        
        .footer-card {
            border: none;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.03);
            border-radius: 12px;
        }
        
        .footer-title {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
        }
        
        .footer-subtitle {
            color: #6c757d;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }
        
        .footer-copyright {
            color: #aaa;
            font-size: 0.8rem;
        }
        

    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-spinner">
            <div class="spinner-border" role="status" style="width: 3rem; height: 3rem;"></div>
            <div class="loading-text">กำลังเข้าสู่ระบบ...</div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="login-card p-4 p-md-5" data-aos="fade-up" data-aos-duration="1000">
                        <div class="login-header" data-aos="fade-down" data-aos-delay="200">
                            <div class="logo-circle pulse-effect" data-aos="zoom-in" data-aos-delay="400">
                                <img src="./vendor/image/logo.jpg" alt="Logo" class="img-fluid">
                            </div>
                            <h1 class="system-title" data-aos="fade-up" data-aos-delay="600">ระบบจัดการใบรับรองนักวิจัยเพื่อใช้ประกอบในการขอทุนสนับสนุนการวิจัย<br>(สำหรับคณะกรรมการความปลอดภัย มหาวิทยาลัยนเรศวร)</h1>
                            <p class="faculty-name" data-aos="fade-up" data-aos-delay="700">คณะเกษตรศาสตร์ ทรัพยากรธรรมชาติและสิ่งแวดล้อม มหาวิทยาลัยนเรศวร</p>
                        </div>
                        
                        <div class="login-divider"></div>
                        
                        <form id="loginForm" method="post" data-aos="fade-up" data-aos-delay="800">
                            <div class="mb-3" data-aos="fade-right" data-aos-delay="900">
                                <label for="username" class="form-label">
                                    <i class="bi bi-person me-2"></i>ชื่อผู้ใช้
                                </label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="กรุณาใส่ชื่อผู้ใช้" autocomplete="username">
                            </div>
                            <div class="mb-4" data-aos="fade-right" data-aos-delay="1000">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock me-2"></i>รหัสผ่าน
                                </label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="กรุณาใส่รหัสผ่าน" autocomplete="current-password">
                            </div>
                            <div class="d-grid gap-2" data-aos="zoom-in" data-aos-delay="1100">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>เข้าสู่ระบบ
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer" data-aos="fade-up" data-aos-offset="100">
        <div class="container-fluid">
            <div class="card footer-card">
                <div class="card-body text-center p-3">
                    <!-- <a class="btn btn-primary btn-sm" href="#">ประเมินการใช้งาน</a> -->
                    <p class="footer-title mt-2">โครงการวิจัยสถาบัน การพัฒนาแพลตฟอร์มการออกใบรับรองนักวิจัยสำหรับใช้ในการขอทุนสนับสนุนงานวิจัย</p>
                    <p class="footer-subtitle">Development of a Researcher Certification Platform for Research Grant Applications.</p>
                    <p class="footer-copyright">&copy; <?php echo date('Y'); ?> คณะเกษตรศาสตร์ ทรัพยากรธรรมชาติและสิ่งแวดล้อม มหาวิทยาลัยนเรศวร</p>
             
                </div>
            </div>
        </div>
    </footer>
    

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Custom Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize AOS Animation
        AOS.init({
            once: true, // Animation only happens once
            mirror: false, // No mirror animation when scrolling back up
            duration: 800 // Animation duration
        });
        
        const loginForm = document.getElementById('loginForm');
        const loadingOverlay = document.getElementById('loadingOverlay');

        // ฟังก์ชันแสดง loading
        function showLoading() {
            loadingOverlay.style.visibility = 'visible';
            loadingOverlay.style.opacity = '1';
        }

        // ฟังก์ชันซ่อน loading
        function hideLoading() {
            loadingOverlay.style.opacity = '0';
            setTimeout(() => {
                loadingOverlay.style.visibility = 'hidden';
            }, 300);
        }

        async function submitForm(event) {
            event.preventDefault();
            
            // ดึงข้อมูลจากฟอร์ม
            const form = document.getElementById('loginForm');
            const formData = new FormData(form);
            
            // ปิดใช้งานปุ่ม submit
            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>กำลังเข้าสู่ระบบ...';
            
            // แสดง loading overlay
            showLoading();
            
            try {
                const response = await fetch('loginnusafe.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                // ซ่อน loading overlay
                hideLoading();
                
                // คืนค่าปุ่ม submit
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
                
                if (data.success) {
                    // แสดง SweetAlert2 สำเร็จ
                    Swal.fire({
                        icon: 'success',
                        title: 'เข้าสู่ระบบสำเร็จ',
                        text: 'กำลังนำท่านไปยังหน้าหลัก...',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // กำหนด URL ที่จะ redirect ไป
                        window.location.href = 'nusafe.php';
                    });
                } else {
                    // แสดง SweetAlert2 เกิดข้อผิดพลาด
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: data.message || 'ไม่สามารถเข้าสู่ระบบได้'
                    });
                }
            } catch (error) {
                // ซ่อน loading overlay
                hideLoading();
                
                // คืนค่าปุ่ม submit
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
                
                // แสดง SweetAlert2 เกิดข้อผิดพลาด
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์: ' + error.message
                });
                console.error('Error:', error);
            }
        }

        // ผูกฟังก์ชันกับเหตุการณ์ submit ของฟอร์ม
        loginForm.addEventListener('submit', submitForm);
    });

      // ฟังก์ชันสำหรับอัพเดทจำนวนผู้เข้าชมแบบ real-time (ไม่เพิ่มค่า)
      function getCounter() {
            fetch('counter.php')
                .then(response => response.json())
                .then(data => {
                    updateCounterDisplay(data);
                })
                .catch(error => {
                    console.error('เกิดข้อผิดพลาด:', error);
                });
        }
        
        // ฟังก์ชันสำหรับอัพเดทการแสดงผลบนหน้าเว็บ
        function updateCounterDisplay(data) {
            document.getElementById('visitor-counter').textContent = data.counter;
            document.getElementById('last-updated').textContent = data.formatted_time;
        }
        
        // เมื่อโหลดหน้าครั้งแรกให้เพิ่มจำนวนผู้เข้าชม
        document.addEventListener('DOMContentLoaded', incrementCounter);
        
        // อัพเดททุกๆ 30 วินาที โดยไม่เพิ่มค่า
        setInterval(getCounter, 30000);
        
    </script>
</body>
</html>