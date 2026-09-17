# ESPReL - ระบบจัดการเอกสารขอเข้าใช้ห้องปฏิบัติการวิจัย

ระบบจัดการใบรับรองและขอเข้าใช้ห้องปฏิบัติการ คณะเกษตรศาสตร์ ทรัพยากรธรรมชาติและสิ่งแวดล้อม มหาวิทยาลัยนเรศวร

---

## 🛠️ ข้อกำหนดเบื้องต้น (Requirements)

- **PHP**: เวอร์ชัน 7.4 ขึ้นไป (รองรับ PHP 8.x)
  - ส่วนขยายที่จำเป็น: `pdo_pgsql`, `mysqli`, `ldap`, `gd`, `openssl`, `mbstring`
  - สำหรับระบบนิสิต: `pdo_sqlsrv` หรือ `odbc` (MS SQL Server)
- **PostgreSQL Database** (สำหรับฐานข้อมูล ESPReL)
- **MySQL Database** (สำหรับฐานข้อมูลผู้ใช้ `db_user`)
- **Web Server**: Apache / Nginx

---

## 🚀 การติดตั้งและตั้งค่าโปรเจกต์ (Installation & Configuration)

1. **Clone repository**:
   ```bash
   git clone <repository_url>
   cd esprel
   ```

2. **ตั้งค่า Environment Variables**:
   คัดลอกไฟล์ `.env.example` ไปเป็น `.env`:
   ```bash
   cp .env.example .env
   ```

3. **แก้ไขไฟล์ `.env`**:
   กำหนดค่าการเชื่อมต่อฐานข้อมูล, LDAP, และระบบอีเมลให้ตรงกับเซิร์ฟเวอร์จริง:
   ```dotenv
   # PostgreSQL Database (ESPReL)
   DB_PG_HOST=localhost
   DB_PG_PORT=5432
   DB_PG_DATABASE=ESPReL
   DB_PG_USER=your_pg_user
   DB_PG_PASSWORD=your_pg_password

   # MySQL Database (db_user)
   DB_MYSQL_HOST=localhost
   DB_MYSQL_PORT=3306
   DB_MYSQL_DATABASE=db_user
   DB_MYSQL_USER=your_mysql_user
   DB_MYSQL_PASSWORD=your_mysql_password

   # LDAP Server
   LDAP_SERVER=ldap://10.10.10.71
   LDAP_DOMAIN=@nu.local

   # SMTP Service
   MAIL_HOST=smtp.office365.com
   MAIL_PORT=587
   MAIL_USERNAME=your_email@domain.com
   MAIL_PASSWORD=your_app_password
   ```

4. **ตรวจสอบสิทธิ์การเขียนโฟลเดอร์ (Directory Permissions)**:
   ตรวจสอบให้แน่ใจว่า Web Server มีสิทธิ์เขียนลงในโฟลเดอร์ต่อไปนี้:
   ```bash
   chmod -R 775 uploads export sign backup
   ```

---

## 🔒 ความปลอดภัย (Security Notes)

- ไฟล์ `.env` เก็บข้อมูลสำคัญและรหัสผ่านทั้งหมด **ห้ามนำไฟล์ `.env` ขึ้น Git เป็นอันขาด**
- โฟลเดอร์ `uploads/`, `export/`, `sign/`, และ `backup/` ถูกกำหนดไว้ใน `.gitignore` เพื่อป้องกันข้อมูลส่วนตัวของผู้ใช้รั่วไหล
