# Personal Loan

โปรเจกต์นี้เป็นระบบ `Laravel` ที่รันผ่าน `Docker` โดยมี service หลักดังนี้

- `app` สำหรับเว็บแอป Laravel
- `mysql` สำหรับฐานข้อมูล MySQL
- `phpmyadmin` สำหรับจัดการฐานข้อมูลผ่านหน้าเว็บ

## สิ่งที่ต้องมี

ก่อนเปิดโปรเจกต์บนคอมเครื่องอื่น ให้เตรียมสิ่งเหล่านี้ก่อน

- ติดตั้ง `Docker Desktop`
- เปิด `Docker Desktop` ให้พร้อมใช้งาน
- มี `Git` หรือมีไฟล์โปรเจกต์อยู่ในเครื่องแล้ว
- แนะนำให้หยุด `Apache` และ `MySQL` จาก `XAMPP` ถ้ามี เพื่อกันพอร์ตชน

## ลำดับการเปิดโปรเจกต์บนคอมเครื่องอื่น

### 1. ดาวน์โหลดโปรเจกต์

ใช้วิธีใดวิธีหนึ่ง

- `git clone`
- ดาวน์โหลดไฟล์โปรเจกต์จาก GitHub เป็น zip
- copy โฟลเดอร์โปรเจกต์จากอีกเครื่อง

ตัวอย่าง:

```powershell
git clone https://github.com/phEt101/Personal-Loan.git
cd Personal-Loan
```

### 2. สร้างไฟล์ `.env`

ไฟล์ `.env` ไม่ถูกเก็บใน Git ดังนั้นหลัง clone โปรเจกต์ต้องสร้างเองจาก `.env.example`

```powershell
copy .env.example .env
```

### 3. ตรวจค่าใน `.env`

ค่าตั้งต้นของโปรเจกต์สำหรับ Docker local ควรเป็นประมาณนี้

```env
APP_NAME=PersonalLoan
APP_ENV=local
APP_KEY=
APP_DEBUG=false
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=personal_loan
DB_USERNAME=personal_loan_user
DB_PASSWORD=secret

SESSION_DRIVER=file
QUEUE_CONNECTION=sync
CACHE_STORE=file
```

หมายเหตุ:

- `APP_KEY` ปล่อยว่างไว้ก่อนได้ เดี๋ยวค่อย generate
- ถ้าเครื่องใหม่ต้องใช้ค่าพิเศษอื่น ให้แก้ในขั้นนี้ก่อน

### 4. เปิด Docker containers

รันคำสั่งนี้ที่รากโปรเจกต์

```powershell
docker compose up -d --build
```

### 5. เช็กว่า containers ขึ้นครบ

```powershell
docker compose ps
```

ควรเห็น service หลักประมาณนี้

- `personal-loan-app`
- `personal-loan-db`
- `personal-loan-phpmyadmin`

### 6. สร้าง `APP_KEY`

ถ้าไฟล์ `.env` ยังไม่มี `APP_KEY` ให้รัน

```powershell
docker compose exec app php artisan key:generate
```

### 7. สร้างตารางฐานข้อมูล

```powershell
docker compose exec app php artisan migrate
```

ถ้าต้องการข้อมูลตัวอย่างด้วย ให้รันเพิ่ม

```powershell
docker compose exec app php artisan db:seed
```

### 8. ล้าง cache ของ Laravel

```powershell
docker compose exec app php artisan optimize:clear
```

### 9. เปิดใช้งานหน้าเว็บ

เปิดในเบราว์เซอร์ที่ลิงก์เหล่านี้

- เว็บหลัก: [http://localhost:8080](http://localhost:8080)
- phpMyAdmin: [http://localhost:8081](http://localhost:8081)

## วิธีล็อกอิน phpMyAdmin

ใช้ค่าต่อไปนี้

- Server: `mysql`
- Username: `root`
- Password: `root`

หรือใช้ user ของโปรเจกต์

- Server: `mysql`
- Username: `personal_loan_user`
- Password: `secret`

## ถ้าต้องย้ายข้อมูลจากเครื่องเดิมมาด้วย

ถ้าไม่ได้ต้องการแค่โค้ด แต่ต้องการข้อมูลในฐานข้อมูลเดิมด้วย ให้ทำเพิ่มดังนี้

### 1. Export ฐานข้อมูลจากเครื่องเดิม

บันทึกออกมาเป็นไฟล์ `.sql`

### 2. เปิด MySQL container บนเครื่องใหม่ก่อน

```powershell
docker compose up -d mysql
```

### 3. Import ไฟล์ `.sql` เข้าไป

ตัวอย่าง:

```powershell
Get-Content .\backup.sql | docker compose exec -T mysql mysql -u root -proot personal_loan
```

จากนั้นค่อยเปิดเว็บและทดสอบข้อมูล

## คำสั่งที่ใช้บ่อย

### เปิดระบบ

```powershell
docker compose up -d
```

### หยุดระบบ

```powershell
docker compose down
```

### ดูสถานะ

```powershell
docker compose ps
```

### ดู log

```powershell
docker compose logs -f app
docker compose logs -f mysql
docker compose logs -f phpmyadmin
```

### เข้าไปรันคำสั่ง Laravel

```powershell
docker compose exec app php artisan migrate
docker compose exec app php artisan optimize:clear
```

## ปัญหาที่มักเจอ

### 1. เปิดเว็บไม่ได้

ให้เช็กก่อนว่า `app` container ขึ้นหรือยัง

```powershell
docker compose ps
```

### 2. เชื่อมฐานข้อมูลไม่ได้

ให้เช็กใน `.env` ว่าใช้ค่าเหล่านี้

- `DB_HOST=mysql`
- `DB_PORT=3306`
- `DB_DATABASE=personal_loan`
- `DB_USERNAME=personal_loan_user`
- `DB_PASSWORD=secret`

### 3. ขึ้น error เรื่อง key

ให้รัน

```powershell
docker compose exec app php artisan key:generate
```

### 4. ขึ้น error เรื่อง table ไม่มี

ให้รัน

```powershell
docker compose exec app php artisan migrate
```

### 5. พอร์ตชนกับโปรแกรมอื่น

โปรเจกต์นี้ใช้พอร์ตหลักดังนี้

- `8080` สำหรับเว็บ Laravel
- `3307` สำหรับ MySQL จากเครื่อง host
- `8081` สำหรับ phpMyAdmin

ถ้าเปิดไม่ได้ ให้เช็กว่ามีโปรแกรมอื่นใช้อยู่หรือไม่

## สรุปแบบสั้น

ถ้าจะเปิดบนคอมเครื่องอื่นแบบเร็วที่สุด ให้ทำตามนี้

```powershell
git clone https://github.com/phEt101/Personal-Loan.git
cd Personal-Loan
copy .env.example .env
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan optimize:clear
```

จากนั้นเปิด

- [http://localhost:8080](http://localhost:8080)
- [http://localhost:8081](http://localhost:8081)
