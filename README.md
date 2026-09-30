# سامانه پذیرش، خدمات و ترخیص بیمارستان

پروژهٔ آموزشی با Yii 2، PHP، MySQL و Nginx. همهٔ کاربران واردشده دسترسی عملیاتی یکسان دارند. گردش اصلی شامل ثبت بیمار، پذیرش، افزودن خدمات، محاسبه هزینه، ترخیص و مشاهده خلاصه پرونده است.

## نصب بستهٔ راهنمای تحویل در پروژهٔ فعلی

پوشه‌ها و فایل‌های این بسته را در ریشهٔ `hospital` کپی و ادغام کنید. این بسته شامل فایل‌های جدید و نسخهٔ جایگزین README است؛ پروژهٔ کامل نیست. فایل‌های ظاهر اصلاح‌شدهٔ مراحل قبلی باید در نسخهٔ فعلی پروژه باقی بمانند.

**روی سیستم فعلی SQL نصب را اجرا نکنید. دیتابیس فعلی شما آماده است.**

## پیش‌نیازها برای سیستم تازه

- PHP 8.2 یا بالاتر با افزونه‌های موردنیاز Yii؛ از جمله `pdo_mysql`، `mbstring` و `openssl`.
- Composer 2 و وابستگی‌های `composer.lock`.
- MySQL یا MariaDB با پشتیبانی InnoDB.
- Nginx ویندوز و فایل `php-cgi.exe`؛ در محیط فعلی PHP در `C:\xampp\php` و Nginx در `C:\nginx` است.

در PowerShell از پوشهٔ `hospital`:

```powershell
composer --working-dir=app install
& C:\xampp\php\php.exe .\app\requirements.php
```

پوشه‌های `app/runtime` و `app/web/assets` باید قابل نوشتن باشند. در صورت نبودن، آن‌ها را بسازید:

```powershell
New-Item -ItemType Directory -Force .\app\runtime, .\app\web\assets
```

برای این نسخهٔ آموزشی `composer install` معمولی استفاده می‌شود؛ پیکربندی توسعه ماژول‌های debug و gii را فعال می‌کند.

## ساخت دیتابیس در نصب تازه

۱. MySQL را روشن کنید.
۲. در phpMyAdmin یک دیتابیس **خالی** با نام انتخابی، مثلاً `hospital` و collation برابر `utf8mb4_unicode_ci` بسازید.
۳. همان دیتابیس را انتخاب و تنها `database/install.sql` را Import کنید.
۴. اگر دادهٔ نمونه برای انتخاب پزشک، بخش و خدمت می‌خواهید، `database/demo_reference_data.sql` را فقط یک بار Import کنید. این فایل هیچ بیمار یا پرونده‌ای ایجاد نمی‌کند.

فایل `install.sql` جایگزین روش نصب با SQLهای قدیمی `001_create_patients.sql` و `002_create_remaining_tables.sql` است. آن‌ها را همراه این فایل اجرا نکنید. نصب کامل جدول‌های `users`، `patients`، `doctors`، `wards`، `admissions`، `services`، `admission_services` و `discharges` را می‌سازد.

این SQL برای ارتقای دیتابیس موجود طراحی نشده است. `DROP TABLE` ندارد، اما اجرای آن روی جدول‌های موجود خطا می‌دهد.

## اتصال برنامه به دیتابیس

اگر فایل اتصال هنوز وجود ندارد:

```powershell
Copy-Item .\app\config\db.example.php .\app\config\db.php
```

نام دیتابیس، پورت، نام کاربری و رمز MySQL را در `app/config/db.php` مطابق سیستم مقصد تنظیم کنید. فایل موجود سیستم فعلی را بازنویسی نکنید. مثال محلی:

```php
<?php
return [
    'class' => \yii\db\Connection::class,
    'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=hospital',
    'username' => 'نام کاربری MySQL شما',
    'password' => 'رمز MySQL شما',
    'charset' => 'utf8mb4',
];
```

کلید کوکی در `app/config/web.php` ثابت تنظیم شده است. ساخت یا تنظیم متغیر `YII_COOKIE_VALIDATION_KEY` لازم نیست.

## ساخت حساب ورود در نصب تازه

پس از Import و تنظیم اتصال:

```powershell
& C:\xampp\php\php.exe .\app\yii setup/create-user operator
```

دستور نام کاربری و رمز تولیدشده را نمایش می‌دهد؛ آن‌ها را برای ورود نگه دارید. حساب موجود را تغییر نمی‌دهد. روی سیستم فعلی، اگر حساب دارید این مرحله لازم نیست.

مقدار `role` حساب تازه `operator` است؛ کنترلرهای صفحات اصلی براساس ورود کاربر عمل می‌کنند و بین نقش‌ها تفاوت دسترسی ندارند.

## تنظیم Nginx روی پورت 8080

در `deploy/nginx.conf` مقدار `root` را با مسیر `app/web` سیستم مقصد هماهنگ کنید. در تنظیم Nginx، جداکنندهٔ مسیر `/` است.

از فایل تنظیمات Nginx فعلی نسخهٔ پشتیبان بگیرید و سپس فایل نمونه را کپی کنید:

```powershell
Copy-Item C:\nginx\conf\nginx.conf C:\nginx\conf\nginx.conf.backup
Copy-Item .\deploy\nginx.conf C:\nginx\conf\nginx.conf
```

MySQL را روشن کنید. سپس، از ریشهٔ پروژه:

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File .\scripts\Start-Hospital.ps1
```

ExecutionPolicy در این دستور فقط برای همان فرایند PowerShell تنظیم می‌شود. اسکریپت ابتدا تنظیم Nginx را تست می‌کند، Nginx را اجرا یا reload می‌کند و سپس PHP FastCGI را در همین پنجره اجرا می‌کند. پنجره را باز نگه دارید.

اگر PHP از قبل روی 9000 اجراست، اسکریپت از اجرای نمونهٔ دوم جلوگیری می‌کند. همان PHP موجود را نگه دارید یا ابتدا در پنجرهٔ آن `Ctrl+C` بزنید.

مسیرهای متفاوت را می‌توانید به اسکریپت بدهید:

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File .\scripts\Start-Hospital.ps1 -NginxDirectory C:\nginx -PhpDirectory C:\xampp\php
```

آدرس برنامه:

[داشبورد](http://localhost:8080/index.php?r=site/index)

## اجرای دستی

در یک پنجره:

```powershell
& C:\xampp\php\php-cgi.exe -c C:\xampp\php\php.ini -d cgi.force_redirect=0 -d cgi.fix_pathinfo=0 -b 127.0.0.1:9000
```

در پنجرهٔ دیگر:

```powershell
cd C:\nginx
.\nginx.exe -t
```

اگر تست موفق بود و Nginx خاموش است:

```powershell
Start-Process -FilePath C:\nginx\nginx.exe -WorkingDirectory C:\nginx
```

اگر روشن است:

```powershell
.\nginx.exe -s reload
```

## توقف

در پنجرهٔ PHP، `Ctrl+C` بزنید. برای توقف Nginx:

```powershell
cd C:\nginx
.\nginx.exe -s quit
```

## رفع خطاهای رایج

- `502 Bad Gateway`: بررسی کنید PHP روی `127.0.0.1:9000` اجراست.
- خطای اتصال دیتابیس: MySQL، پورت و تنظیمات `db.php` را بررسی کنید.
- `ViewNotFoundException`: فایل‌های داخل بسته‌های اصلاحی را ادغام کنید؛ پوشهٔ کامل `views` یا زیرپوشه‌های آن را حذف نکنید.
- خطای bind روی 8080 یا 9000: برنامهٔ دیگری از پورت استفاده می‌کند؛ فرایند مربوط را شناسایی کنید، نمونهٔ دوم اجرا نکنید.

```powershell
Test-NetConnection 127.0.0.1 -Port 9000
Get-Content C:\nginx\logs\error.log -Tail 20
```

## وضعیت بررسی

گردش اصلی و ظاهر صفحات در گفتگو توسط کاربر روی سیستم فعلی تست شده‌اند. نصب روی دیتابیس خالی، دستور ساخت حساب و اسکریپت اجرای این بسته هنوز اجرا و تأیید نشده‌اند. PHP، MySQL و PowerShell در محیط آماده‌سازی بسته موجود نبودند؛ تطبیق ساختار فایل‌ها و مدل‌ها انجام شده است.

معیارهای بررسی نهایی در `DELIVERY_CHECKLIST.md` آمده‌اند.

## منابع فنی

- [Yii — نصب و تنظیم وب‌سرور](https://www.yiiframework.com/doc/guide/2.0/en/start-installation)
- [Yii — دستورات کنسول](https://www.yiiframework.com/doc/guide/2.0/en/tutorial-console)
- [Nginx در ویندوز](https://nginx.org/en/docs/windows.html)
