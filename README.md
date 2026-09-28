# Hospital

سامانهٔ آموزشی پذیرش، ارائهٔ خدمت و ترخیص بیمارستان، بر اساس صورت پروژهٔ کارآموزی.

## وضعیت فعلی

اسکلت برنامه با Yii 2 نصب شده و صفحهٔ اولیهٔ آن اجرا می‌شود. قابلیت‌های بیمارستانی، اتصال واقعی به MySQL و اجرای Nginx هنوز پیاده‌سازی نشده‌اند.

## قابلیت‌های برنامه‌ریزی‌شده

- ثبت و فهرست بیماران
- ثبت پذیرش بیمار
- افزودن خدمات به پذیرش و محاسبهٔ هزینه
- ثبت ترخیص و نمایش خلاصهٔ پرونده

## پیش‌نیازهای نسخهٔ فعلی

- PHP 8.2 یا بالاتر
- Composer 2
- افزونهٔ `pdo_mysql` در PHP برای مرحلهٔ اتصال پایگاه داده

برنامه در پوشهٔ `app` قرار دارد. دستورهای زیر برای PowerShell ویندوز نوشته شده‌اند و از ریشهٔ مخزن اجرا می‌شوند.

## نصب و اجرای محلی

در یک نسخهٔ تازه از مخزن، وابستگی‌های ثبت‌شده در `composer.lock` را نصب کنید:

```powershell
composer --working-dir=app install
```

اگر `app\config\db.php` وجود ندارد، فایل نمونه را کپی کنید:

```powershell
Copy-Item .\app\config\db.example.php .\app\config\db.php
```

مقادیر اتصال را بعداً، هنگام راه‌اندازی MySQL، فقط در `app\config\db.php` تنظیم کنید. این فایل در Git ثبت نمی‌شود. اگر از قبل وجود دارد، آن را بازنویسی نکنید.

برای هر بار اجرای محلی، در همان ترمینالی که سرور را شروع می‌کنید یک کلید تازه بسازید:

```powershell
$keyBytes = New-Object byte[] 32
$rng = [System.Security.Cryptography.RandomNumberGenerator]::Create()
$rng.GetBytes($keyBytes)
$rng.Dispose()
$env:YII_COOKIE_VALIDATION_KEY = [Convert]::ToBase64String($keyBytes)
php .\app\yii serve --port=8081
```

صفحهٔ اولیه در `http://127.0.0.1:8081/` باز می‌شود. با `Ctrl+C` سرور را متوقف کنید. مقدار کلید را در کد یا Git ذخیره نکنید.

## بررسی فعلی

از ریشهٔ مخزن:

```powershell
php .\app\requirements.php
php -l .\app\config\web.php
php -l .\app\config\db.example.php
```

اجرای تست‌های قالب و دستورهای مربوط به پایگاه داده و Nginx پس از تنظیم آن بخش‌ها به این راهنما اضافه می‌شوند.