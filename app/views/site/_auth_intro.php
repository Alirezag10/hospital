<?php use yii\helpers\Html; ?>
<aside class="auth-intro">
    <span class="auth-symbol"><?= $this->render('/layouts/_icon', ['name' => 'doctor']) ?></span>
    <h2>هر نقش،<br>یک مسیر روشن.</h2>
    <p>ورود یکپارچه به ابزارهای پذیرش، درمان و ترخیص.</p>
    <dl class="auth-role-guide">
        <div><dt>مدیر سیستم</dt><dd>مدیریت کاربران و دسترسی به همه بخش‌ها</dd></div>
        <div><dt>منشی</dt><dd>ثبت بیمار و تنظیم پذیرش</dd></div>
        <div><dt>پزشک</dt><dd>نسخه، خدمات و اقلام درمانی</dd></div>
        <div><dt>ترخیص‌کار</dt><dd>بررسی هزینه و ثبت ترخیص</dd></div>
    </dl>
</aside>
