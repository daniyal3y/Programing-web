# سامانه لینک اختصاصی قرعه‌کشی | Raffle Link Platform

پیاده‌سازی تمرین Laravel برای تولید لینک کوتاه و یکتا، رد کردن لینک دست‌کاری‌شده در Nginx پیش از رسیدن به Laravel و جلوگیری قطعی از ثبت‌نام تکراری در شرایط درخواست هم‌زمان.

## قابلیت‌ها

- تولید توکن تصادفی ۱۲۸ بیتی با طول ۲۲ کاراکتر Base64URL.
- امضای HMAC-SHA-256 با خروجی ۱۲۸ بیتی (۳۲ کاراکتر hex)؛ قالب URL: `/r/{token}.{signature}`.
- اعتبارسنجی مستقل امضا در Nginx با `ngx_http_js_module` و njs در فاز `js_access`؛ URL نامعتبر با 404 محلی خاتمه می‌یابد و به PHP-FPM/Laravel ارسال نمی‌شود.
- اعتبارسنجی دوباره در Laravel و جست‌وجوی رکورد از طریق ایندکس یکتای token.
- جلوگیری اتمیک از ثبت‌نام تکراری با unique index روی `(raffle_id, phone)`؛ بنابراین دو درخواست هم‌زمان نمی‌توانند یک شماره را دوبار برای یک قرعه‌کشی ثبت کنند.
- تولید انبوه لینک در batchهای ۵۰۰تایی، Redis برای cache و rate limit توزیع‌شده، و محدودسازی نرخ درخواست در Nginx.
- API مدیریتی محافظت‌شده با Bearer Token، اعتبارسنجی ورودی‌ها و تست‌های واحد/ویژگی.

## پیش‌نیازها

- PHP 8.3 یا بالاتر و Composer 2
- Laravel 13
- MySQL 8+ یا MariaDB سازگار
- Redis و PHP Redis extension در محیط production
- Nginx با `ngx_http_js_module` و njs نسخه 0.9.1 یا بالاتر (با پشتیبانی QuickJS و `crypto.createHmac`)

Laravel 13 حداقل PHP 8.3 را نیاز دارد. در توسعه می‌توان از SQLite و cache آرایه‌ای استفاده کرد؛ در production از MySQL و Redis استفاده شود.

## نصب و اجرای محلی

```bash
git clone https://github.com/daniyal3y/Programing-web.git
cd Programing-web/raffle-link-platform
composer install
cp .env.example .env
php artisan key:generate
```

یک secret تصادفی مشترک برای Laravel و Nginx تولید کنید:

```bash
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
```

مقدار خروجی را در `.env` برای `RAFFLE_LINK_SECRET` قرار دهید. برای `RAFFLE_ADMIN_TOKEN` نیز یک مقدار تصادفی مستقل با حداقل ۳۲ بایت انتخاب کنید. این دو مقدار را هرگز commit نکنید.

برای توسعه با SQLite، فایل `database/database.sqlite` را بسازید و در `.env` تنظیم کنید:

```dotenv
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/project/database/database.sqlite
CACHE_STORE=array
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
```

سپس:

```bash
php artisan migrate --seed
php artisan serve
```

## API مدیریتی

تمام مسیرهای مدیریتی به هدر `Authorization: Bearer <RAFFLE_ADMIN_TOKEN>` نیاز دارند.

### ایجاد قرعه‌کشی

```http
POST /api/admin/raffles
Content-Type: application/json
Authorization: Bearer YOUR_ADMIN_TOKEN

{
  "name": "قرعه‌کشی پاییز",
  "is_active": true,
  "starts_at": null,
  "ends_at": null
}
```

### تولید لینک

```http
POST /api/admin/raffles/1/links
Content-Type: application/json
Authorization: Bearer YOUR_ADMIN_TOKEN

{
  "quantity": 1000,
  "expires_at": null
}
```

پاسخ شامل `issued` و آرایه لینک‌های امضاشده است. حداکثر تعداد در هر فراخوانی ۱۰٬۰۰۰ لینک است.

## معماری امنیت لینک در Nginx

فایل `nginx/njs/raffle_guard.js` قبل از اجرای PHP، مسیر و امضای لینک را بررسی می‌کند. این بررسی به دیتابیس یا Laravel درخواست نمی‌زند.

۱. مسیر باید دقیقاً یکی از این دو الگو باشد:

- `/r/{22-char-token}.{32-char-signature}`
- `/r/{22-char-token}.{32-char-signature}/entries`

۲. Nginx با secret مشترک، HMAC-SHA-256 توکن را محاسبه و ۱۲۸ بیت اول آن را به hex تبدیل می‌کند.

۳. امضا با مقایسه زمان‌ثابت بررسی می‌شود. در صورت تفاوت حتی یک نویسه در token یا signature، یا تغییر delimiter/path، پاسخ 404 در خود Nginx تولید می‌شود.

۴. فقط درخواست معتبر با `try_files` به front controller لاراول می‌رسد. Laravel مجدداً امضا را تأیید و رکورد لینک/وضعیت قرعه‌کشی را بررسی می‌کند.

**نکته دقیق درباره تضمین:** این تضمین برای بخش مسیر تحت کنترل برنامه (`/r/...`) است. تغییر scheme، hostname یا DNS ممکن است درخواست را به سرور دیگری ببرد و طبیعتاً توسط Nginx این virtual host قابل ارزیابی نیست. همچنین HMAC تضمین رمزنگارانه با احتمال جعل ناچیز (حدود `2^-128`) می‌دهد، نه گزاره ریاضی با احتمال صفر.

### نصب Nginx

فایل `nginx/nginx.conf.example` نمونه تنظیم کامل است. پیش از استفاده:

- njs را نصب و فعال کنید؛ نسخه باید QuickJS و crypto/HMAC را پشتیبانی کند.
- فایل `nginx/njs/raffle_guard.js` را در مسیر import شده قرار دهید.
- `RAFFLE_LINK_SECRET` را به محیط سرویس Nginx بدهید و directive مربوط به `env` را در context اصلی حفظ کنید. این مقدار باید دقیقاً با `.env` لاراول برابر باشد.
- مسیر پروژه، دامنه و socket مربوط به PHP-FPM را مطابق سرور تغییر دهید.
- دسترسی عمومی به `.env`, `vendor`, `storage` و فایل‌های مخفی را مسدود نگه دارید.

پس از تنظیم:

```bash
sudo nginx -t
sudo systemctl reload nginx
```

**مهم:** در صورت خالی یا نامعتبر بودن secret، njs به‌صورت fail-closed همه لینک‌ها را 404 می‌کند. این رفتار عمدی است.

## جلوگیری از ثبت تکراری و هم‌زمانی

بررسی تکراری بودن صرفاً با `SELECT` قبل از `INSERT` انجام نمی‌شود، زیرا این الگو در رقابت هم‌زمانی آسیب‌پذیر است. جدول `raffle_entries` یک قید یکتای مرکب روی `(raffle_id, phone)` دارد و درج با `insertOrIgnore` انجام می‌شود. دیتابیس تنها مرجع نهایی یکتایی است؛ بنابراین حتی اگر دو درخواست در یک لحظه برسند، حداکثر یک ردیف ساخته می‌شود.

شماره همراه باید به شکل canonical و بین‌المللی E.164 ارسال شود؛ برای مثال `+989121234567`. به این ترتیب یک شماره با فاصله، خط تیره یا قالب‌های محلی متفاوت، چند هویت متفاوت ایجاد نمی‌کند.

## بهینه‌سازی برای ترافیک بالا

- token یکتا و lookup با ایندکس `UNIQUE`؛
- cache کوتاه‌مدت اطلاعات لینک در Redis (TTL پیش‌فرض ۳۰ ثانیه)؛
- rate limit در Nginx پیش از PHP و rate limit توزیع‌شده در Laravel؛
- batch insert برای تولید لینک‌های گروهی؛
- عدم اجرای query برای URLهایی که Nginx امضای آن‌ها را رد می‌کند؛
- پاسخ `no-store` برای صفحه لینک خصوصی و `no-referrer` برای کاهش نشت URL.

در محیط چندسروری باید secret مشترک، Redis مشترک و دیتابیس primary سازگار در تمام nodeها تنظیم شود. در صورت نیاز به revocation تقریباً فوری، TTL cache را کاهش دهید یا هنگام غیرفعال‌کردن لینک کلید cache مربوطه را پاک کنید.

## تست

```bash
php artisan test
node --experimental-default-type=module tests/nginx/raffle_guard.node-test.mjs
```

تست‌ها قالب و امضای لینک، تغییر توکن/امضا، دسترسی API مدیریتی، ایجاد لینک و قید یکتایی دیتابیس را پوشش می‌دهند. تست end-to-end مربوط به Nginx باید روی محیطی اجرا شود که ماژول njs واقعاً نصب شده باشد.

## ساختار اصلی

```text
app/
  Http/Controllers/Api/Admin/  # مدیریت قرعه‌کشی و تولید لینک
  Http/Controllers/             # صفحه قرعه‌کشی و ثبت شرکت
  Http/Middleware/              # Bearer token مدیریتی
  Http/Requests/                # اعتبارسنجی ورودی
  Models/                       # Raffle, RaffleLink, RaffleEntry
  Services/                     # صدور، امضا و resolve لینک
  Providers/
config/                         # تنظیمات برنامه و raffle
 database/migrations/           # schema و قیدهای یکتا
 nginx/njs/                     # اعتبارسنجی مستقل HMAC قبل از Laravel
 nginx/nginx.conf.example       # نمونه پیکربندی Nginx
 resources/views/               # رابط فارسی و راست‌چین
 tests/                         # Unit و Feature tests
```

## اطلاعات تحویل دانشگاه

- مخزن GitHub: `daniyal3y/Programing-web`
- مسیر پروژه: `raffle-link-platform/`
- حساب GitHub: `daniyal3y`
- لینک پروژه: https://github.com/daniyal3y/Programing-web/tree/main/raffle-link-platform
- کد دانشجویی: **پیش از ارسال در گروه، در `SUBMISSION.md` وارد شود.**
