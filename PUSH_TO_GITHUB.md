# وارد کردن پروژه در مخزن Programing-web با حفظ تاریخچه کامیت‌ها

مخزن مقصد از قبل ساخته شده است: `https://github.com/daniyal3y/Programing-web`. چون API نوشتن اتصال GitHub در نشست فعلی با خطای 403 پاسخ می‌دهد، انتشار را با Git محلی انجام دهید. Git Bundle حاوی تاریخچه ۱۰ کامیت مستقل پروژه است. روش زیر کل پروژه را در پوشه `raffle-link-platform/` قرار می‌دهد، بدون آن‌که README یا فایل‌های قبلی ریشه مخزن جایگزین شوند.

## روش پیشنهادی (Windows PowerShell یا ترمینال Git)

۱. Git را نصب کنید و مخزن مقصد را clone کنید:

```powershell
git clone https://github.com/daniyal3y/Programing-web.git
cd Programing-web
```

۲. فایل `raffle-link-platform.bundle` را در دسترس قرار دهید. سپس مسیر واقعی فایل را در دستور زیر جایگزین کنید (برای مثال مسیر Downloads):

```powershell
git fetch "$HOME/Downloads/raffle-link-platform.bundle" main:refs/heads/raffle-source/main
```

۳. پروژه را به صورت subtree اضافه کنید. این دستور، تاریخچه اصلی ۱۰ کامیت را حفظ می‌کند و یک merge commit برای قرار گرفتن آن در زیرپوشه می‌سازد:

```powershell
git subtree add --prefix=raffle-link-platform raffle-source/main
```

۴. نتیجه را بررسی و push کنید:

```powershell
git log --oneline --graph -15
git status
git push origin main
```

پس از push، پروژه در این مسیر در دسترس است:

https://github.com/daniyal3y/Programing-web/tree/main/raffle-link-platform

## نکات مهم

- این روش به جای بازنویسی مخزن، یک پوشه مستقل اضافه می‌کند. فایل‌های قبلی ریشه حفظ می‌شوند.
- `git subtree` همراه Git for Windows عرضه می‌شود؛ در صورت در دسترس نبودن آن، Git for Windows را به‌روز کنید.
- فایل `.env`، کلیدها، توکن مدیریتی و secret واقعی را هرگز commit نکنید. فقط `.env.example` باید منتشر شود.
- کد دانشجویی را در `raffle-link-platform/SUBMISSION.md` وارد کنید و با یک کامیت مستقل ثبت کنید.
