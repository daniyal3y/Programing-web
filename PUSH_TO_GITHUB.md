# انتشار پروژه روی GitHub با حفظ کامیت‌ها

در اتصال فعلی GitHub هیچ مخزن قابل‌دسترسی نمایش داده نشد و ابزار اتصال امکان ساخت Repository جدید را ندارد. برای حفظ تاریخچه هشت‌کامیتی، این پروژه به شکل Git Bundle نیز تحویل می‌شود.

۱. در حساب `daniyal3y` یک مخزن خالی با نام `raffle-link-platform` بسازید. در زمان ساخت، گزینه‌های افزودن README، `.gitignore` و License را خاموش بگذارید.

۲. فایل `raffle-link-platform.bundle` را دریافت کنید و در ترمینال اجرا کنید:

```bash
git clone raffle-link-platform.bundle raffle-link-platform
cd raffle-link-platform
git remote add origin https://github.com/daniyal3y/raffle-link-platform.git
git push -u origin main
```

۳. پس از push، لینک مخزن را باز کنید و مطمئن شوید تاریخچه کامیت‌ها نمایش داده می‌شود. سپس کد دانشجویی را در `SUBMISSION.md` قرار دهید و کامیت جداگانه‌ای برای آن ثبت کنید.

اگر Repository از قبل ایجاد شده است، به‌جای ساخت مخزن جدید فقط URL آن را به‌عنوان `origin` تنظیم کنید.
