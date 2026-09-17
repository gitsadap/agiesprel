<?php
// ตั้งค่า header
header('Content-Type: text/html; charset=utf-8');

// ดึง user_id จาก GET parameter
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

// ตรวจสอบว่ามี user_id หรือไม่
if (!$user_id) {
    die('ไม่พบ user_id');
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบประกาศ</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            font-family: sans-serif;
        }
        .pdf-container {
            width: 100%;
            height: 100vh;
        }
        .image-container {
            display: block;
            max-width: 100%;
            margin: 0 auto;
        }
        #content {
            opacity: 0;
            transition: opacity 0.5s ease;
        }
        #loading-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #f5f5f5;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            transition: opacity 0.3s ease;
        }
        .loading-text {
            margin-top: 20px;
            font-size: 18px;
            color: #333;
        }
        .progress-container {
            width: 50%;
            max-width: 300px;
            background-color: #e0e0e0;
            border-radius: 10px;
            margin: 10px 0;
            overflow: hidden;
        }
        .progress-bar {
            height: 10px;
            background-color: #4caf50;
            width: 0%;
            border-radius: 10px;
            transition: width 0.3s ease;
        }
        .error {
            color: #f44336;
            text-align: center;
            margin: 20px;
            padding: 20px;
            border: 1px solid #f44336;
            border-radius: 5px;
            background-color: #ffebee;
        }
    </style>
</head>
<body>
    <div id="loading-container">
        <div class="progress-container">
            <div id="progress-bar" class="progress-bar"></div>
        </div>
        <div id="loading-text" class="loading-text">กำลังโหลดข้อมูล...</div>
    </div>
    
    <div id="content"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const contentDiv = document.getElementById('content');
            const loadingContainer = document.getElementById('loading-container');
            const progressBar = document.getElementById('progress-bar');
            const loadingText = document.getElementById('loading-text');
            
            // สร้างฟังก์ชันสำหรับอัพเดต loading progress
            function updateProgress(percent, text) {
                progressBar.style.width = percent + '%';
                if (text) {
                    loadingText.textContent = text;
                }
            }
            
            // เริ่มต้นโหลด
            updateProgress(10, 'กำลังเชื่อมต่อกับเซิร์ฟเวอร์...');
            
            // ดึงข้อมูลใบประกาศจาก API
            fetch(`get_certificates.php?user_id=<?php echo $user_id; ?>`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    updateProgress(30, 'กำลังประมวลผลข้อมูล...');
                    return response.json();
                })
                .then(data => {
                    updateProgress(50, 'กำลังเตรียมไฟล์...');
                    
                    // เช็คประเภทของข้อมูลที่ได้รับ
                    if (data.certype === 'elearning') {
                        // กรณี elearning (มี 1 ไฟล์)
                        if (data.pdf) {
                            // กรณีเป็นไฟล์ PDF
                            updateProgress(70, 'กำลังโหลด PDF...');
                            const iframe = document.createElement('iframe');
                            iframe.src = data.pdf;
                            iframe.className = 'pdf-container';
                            // รอให้ไฟล์โหลดเสร็จ
                            iframe.onload = function() {
                                updateProgress(100, 'เสร็จสมบูรณ์');
                                setTimeout(hideLoading, 500);
                            };
                            contentDiv.appendChild(iframe);
                        } else if (data.image) {
                            // กรณีเป็นรูปภาพ
                            updateProgress(70, 'กำลังโหลดรูปภาพ...');
                            const img = document.createElement('img');
                            img.src = data.image;
                            img.className = 'image-container';
                            // รอให้ไฟล์โหลดเสร็จ
                            img.onload = function() {
                                updateProgress(100, 'เสร็จสมบูรณ์');
                                setTimeout(hideLoading, 500);
                            };
                            contentDiv.appendChild(img);
                        } else {
                            contentDiv.innerHTML = '<div class="error">ไม่พบไฟล์ใบประกาศ</div>';
                            hideLoading();
                        }
                    } else if (data.certype === 'nu' || !data.certype) {
                        // กรณี nu (มี 7 ไฟล์) หรือไม่ระบุประเภท
                        let found = false;
                        let totalFiles = 0;
                        let loadedFiles = 0;
                        
                        // นับจำนวนไฟล์ทั้งหมด
                        for (let i = 1; i <= 7; i++) {
                            if (data[i] && data[i].path) {
                                totalFiles++;
                            }
                        }
                        
                        updateProgress(60, `กำลังโหลดไฟล์... (0/${totalFiles})`);
                        
                        // ถ้าไม่มีไฟล์เลย
                        if (totalFiles === 0) {
                            contentDiv.innerHTML = '<div class="error">ไม่พบไฟล์ใบประกาศ</div>';
                            hideLoading();
                            return;
                        }
                        
                        // สร้างฟังก์ชันสำหรับติดตามการโหลดไฟล์
                        function fileLoaded() {
                            loadedFiles++;
                            let percent = 60 + Math.floor((loadedFiles / totalFiles) * 40);
                            updateProgress(percent, `กำลังโหลดไฟล์... (${loadedFiles}/${totalFiles})`);
                            
                            if (loadedFiles === totalFiles) {
                                updateProgress(100, 'เสร็จสมบูรณ์');
                                setTimeout(hideLoading, 500);
                            }
                        }
                        
                        // ตรวจสอบและแสดงแต่ละไฟล์
                        for (let i = 1; i <= 7; i++) {
                            if (data[i] && data[i].path) {
                                found = true;
                                
                                // สร้าง container สำหรับแต่ละไฟล์
                                const fileContainer = document.createElement('div');
                                fileContainer.style.marginBottom = '20px';
                                
                                if (data[i].type === 'pdf' || data[i].path.toLowerCase().endsWith('.pdf')) {
                                    // กรณีเป็นไฟล์ PDF
                                    const iframe = document.createElement('iframe');
                                    iframe.src = data[i].path;
                                    iframe.style.width = '100%';
                                    iframe.style.height = '500px';
                                    iframe.style.border = 'none';
                                    iframe.onload = fileLoaded;
                                    fileContainer.appendChild(iframe);
                                } else {
                                    // กรณีเป็นรูปภาพหรืออื่นๆ
                                    const img = document.createElement('img');
                                    img.src = data[i].path;
                                    img.style.maxWidth = '100%';
                                    img.style.margin = '0 auto';
                                    img.style.display = 'block';
                                    img.onload = fileLoaded;
                                    fileContainer.appendChild(img);
                                }
                                
                                contentDiv.appendChild(fileContainer);
                            }
                        }
                    } else {
                        contentDiv.innerHTML = '<div class="error">รูปแบบข้อมูลไม่ถูกต้อง</div>';
                        hideLoading();
                    }
                })
                .catch(error => {
                    contentDiv.innerHTML = `<div class="error">เกิดข้อผิดพลาด: ${error.message}</div>`;
                    console.error('Error fetching data:', error);
                    hideLoading();
                });
                
            // ฟังก์ชันซ่อน loading และแสดงเนื้อหา
            function hideLoading() {
                loadingContainer.style.opacity = '0';
                contentDiv.style.opacity = '1';
                setTimeout(() => {
                    loadingContainer.style.display = 'none';
                }, 300);
            }
        });
    </script>
</body>
</html>