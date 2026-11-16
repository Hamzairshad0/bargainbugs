# 🐞 Bargain Bugs: bargainbugs.co.uk

The Bargain Bugs storefront: a Laravel 13 site that pulls products from the eBay store automatically and sends shoppers to eBay to buy. It includes an admin panel.

- **Storefront:** home, shop with search, filters and sorting, hot deals, new in, categories, product pages with gallery and share buttons, about/FAQ, contact, privacy, a `/links` "link in bio" page, and a sitemap.
- **Social-ready:** every page and product has an Open Graph/Twitter preview card (photo, title, price), plus WhatsApp, Facebook, X, Pinterest and copy-link share buttons. Put `https://bargainbugs.co.uk/links` in your Instagram and TikTok bio.
- **eBay sync:** new listings, price and stock changes, and sold/ended items sync automatically. Setup is in **[EBAY_SETUP.md](EBAY_SETUP.md)**.
- **Admin (`/admin`):** dashboard stats (views, eBay clicks, stock value), products (feature, hide, lock edits, bulk actions, manual products), categories, eBay connect and sync, contact messages, newsletter subscribers with CSV export, site settings (hero text, announcement bar, social links), and an account page for changing your password.

---

## Run it locally (XAMPP)

```bash
composer install
npm install && npm run build
cp .env.example .env        # then edit DB_* and ADMIN_* values
php artisan key:generate
php artisan migrate --seed  # creates tables + the admin login from ADMIN_EMAIL / ADMIN_PASSWORD
php artisan db:seed --class=DemoSeeder   # optional sample products to preview the design
php artisan storage:link
php artisan serve
```

Open http://127.0.0.1:8000 and http://127.0.0.1:8000/admin.

To delete the demo products later: Admin ▸ Products ▸ filter **Manual** ▸ tick all ▸ Delete.

Run tests: `php artisan test` (uses a MySQL database called `bargainbugs_test`).

---

## Deploy to Vercel

Vercel runs PHP through the community runtime `vercel-php@0.8.0` (PHP 8.4), configured in `vercel.json`. Every request goes to `api/index.php` → Laravel. Vercel has **no database and no permanent disk**, so you need an external MySQL database.

### 1. Create a free MySQL database
Any MySQL host works, for example:
- **TiDB Cloud Serverless** (free, MySQL compatible): https://tidbcloud.com
- **Aiven for MySQL** (free tier): https://aiven.io

Copy the host, port, database name, user and password. These hosts need SSL, so also set
`MYSQL_ATTR_SSL_CA=/etc/pki/tls/certs/ca-bundle.crt` on Vercel.

### 2. Create the tables in that database (from your PC, once)
In PowerShell, inside the project folder:
```powershell
$env:DB_HOST="your-host"; $env:DB_PORT="4000"; $env:DB_DATABASE="bargainbugs"; $env:DB_USERNAME="user"; $env:DB_PASSWORD="pass"; $env:MYSQL_ATTR_SSL_CA="C:\xampp\apache\bin\curl-ca-bundle.crt"
$env:ADMIN_EMAIL="you@bargainbugs.co.uk"; $env:ADMIN_PASSWORD="a-strong-password"
php artisan migrate --force --seed
```
(Real environment variables override `.env`. Close the PowerShell window afterwards to clear them.)

### 3. Push to GitHub and import into Vercel
1. Create a **private** GitHub repo and push this folder. `public/build` is committed on purpose.
2. In Vercel: **Add New ▸ Project ▸ Import** the repo. Leave Framework Preset as **Other**; `vercel.json` handles the rest.
3. Add **Environment Variables** before the first deploy:

```
APP_NAME="Bargain Bugs"
APP_KEY=base64:...            # run: php artisan key:generate --show
APP_URL=https://bargainbugs.co.uk
DB_CONNECTION=mysql
DB_HOST=...
DB_PORT=...
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
MYSQL_ATTR_SSL_CA=/etc/pki/tls/certs/ca-bundle.crt
ADMIN_EMAIL=you@bargainbugs.co.uk
CRON_SECRET=long-random-string
EBAY_ENV=production
EBAY_SITE_ID=3
EBAY_CLIENT_ID=...
EBAY_CLIENT_SECRET=...
EBAY_DEV_ID=...
EBAY_RUNAME=...
```
4. Click **Deploy**.

### 4. Point bargainbugs.co.uk at Vercel
Vercel ▸ Project ▸ **Settings ▸ Domains** ▸ add `bargainbugs.co.uk` and `www.bargainbugs.co.uk`. Then at your domain registrar set:

| Type | Name | Value |
|---|---|---|
| A | @ | `76.76.21.21` |
| CNAME | www | `cname.vercel-dns.com` |

(Vercel shows the exact values to use on the Domains page.) HTTPS is set up automatically.

### 5. Connect eBay
Follow **[EBAY_SETUP.md](EBAY_SETUP.md)**: create keys, add the RuName, click **Connect** in the admin, then set up cron-job.org for 15-minute syncing.

### Image uploads on Vercel
Products from eBay use eBay's own image links, so they need no storage. For **manual** products you can paste image URLs. To use the upload button on Vercel, add a Cloudflare R2 or S3 bucket:
```bash
composer require league/flysystem-aws-s3-v3 "^3.0"
```
Then set `UPLOAD_DISK=s3`, `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION=auto`, `AWS_BUCKET`, `AWS_ENDPOINT` (the R2 endpoint), `AWS_URL` (the public bucket URL) and `AWS_USE_PATH_STYLE_ENDPOINT=true`.

### Vercel notes
- The built-in cron in `vercel.json` runs once a day, the most the Hobby plan allows. Use cron-job.org for every-15-minute syncs.
- Sessions use cookies and the cache uses the database, so the read-only filesystem isn't a problem.
- If you ever prefer normal PHP hosting (cPanel, Hostinger, a VPS, Laravel Cloud), the same code works unchanged: point the web root at `public/` and add the `schedule:run` cron.

---

## Where things live

| What | File |
|---|---|
| eBay API client (OAuth + Trading API) | `app/Services/EbayClient.php` |
| Sync engine (full / incremental / single item) | `app/Services/EbaySync.php` |
| Description cleaner for eBay HTML | `app/Services/HtmlSanitizer.php` |
| Cron endpoint | `app/Http/Controllers/CronController.php` |
| Storefront controller | `app/Http/Controllers/StoreController.php` |
| Admin controllers | `app/Http/Controllers/Admin/*` |
| Default texts (editable in admin) | `app/Models/Setting.php` → `DEFAULTS` |
| Brand colours, fonts, buttons | `resources/css/app.css` |
| Pages | `resources/views/store/*`, `resources/views/admin/*` |
| Routes | `routes/web.php` |
