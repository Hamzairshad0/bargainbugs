# Connecting the website to the Bargain Bugs eBay store

After this one-time setup, every product you list on eBay appears on the website automatically. Price changes, stock updates and sold items sync too.

**How it works:** the site uses eBay's official **Trading API** (`GetSellerList`, `GetSellerEvents` and `GetItem`) to read *your own* listings. Seller Hub listings can only be read through the Trading API, which is why the site uses it. You keep listing on eBay exactly as you do now.

---

## Step 1: Create an eBay developer account (free)

1. Go to **https://developer.ebay.com** and click **Register**. Any eBay login works, but using the `bargainbugs` account keeps everything in one place.
2. Confirm your email and sign in.

## Step 2: Get your Production API keys

1. Open **Hi [name] ▸ Application Keysets**: https://developer.ebay.com/my/keys
2. Enter an application title, e.g. `BargainBugsWebsite`.
3. Under **Production**, click **Create a keyset**.
4. eBay asks about **Marketplace Account Deletion notifications**. Choose the option saying you are **not persisting eBay user data / exempted**. The site stores only *your own listings*, never buyer data. Give a short reason such as "Website only displays our own listings; no eBay user data is stored."
5. You now have three values. Copy them:

| eBay calls it | Put it in `.env` / Vercel as |
|---|---|
| App ID (Client ID) | `EBAY_CLIENT_ID` |
| Cert ID (Client Secret) | `EBAY_CLIENT_SECRET` |
| Dev ID | `EBAY_DEV_ID` |

> Make sure you copy the **Production** keys, not Sandbox. Sandbox is a fake test eBay with none of your real listings.

## Step 3: Create the redirect (RuName)

The RuName is how eBay sends you back to your website after you approve access.

1. On the keys page, next to your Production keyset, click **User Tokens**.
2. Select **"Get a Token from eBay via Your Application"** and click **Add eBay Redirect URL**.
3. Fill in:
   - **Display title:** `Bargain Bugs`
   - **Privacy policy URL:** `https://bargainbugs.co.uk/privacy`
   - **Auth accepted URL:** `https://bargainbugs.co.uk/ebay/callback`
   - **Auth declined URL:** `https://bargainbugs.co.uk/admin/ebay`
   - **OAuth** must be enabled (the default).
4. Save. eBay shows an **RuName** that looks like `Bargain_Bugs-BargainB-Bargai-abcdef`. Copy that whole value into **`EBAY_RUNAME`**.

> Testing on your PC first? eBay only redirects to **https** URLs, so local `http://127.0.0.1` won't work for the connect step. Do the connection on the live site.

## Step 4: Add the keys to the website

In **Vercel ▸ Project ▸ Settings ▸ Environment Variables** (or your local `.env`), add:

```
EBAY_ENV=production
EBAY_SITE_ID=3
EBAY_CLIENT_ID=xxxxxxxx-xxxx-PRD-xxxxxxxxx-xxxxxxxx
EBAY_CLIENT_SECRET=PRD-xxxxxxxxxxxx-xxxx-xxxx-xxxx-xxxx
EBAY_DEV_ID=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
EBAY_RUNAME=Bargain_Bugs-BargainB-Bargai-abcdef
CRON_SECRET=make-up-a-long-random-string
```

Then redeploy (Vercel ▸ Deployments ▸ ⋯ ▸ Redeploy) so the new variables are picked up.

`EBAY_SITE_ID=3` is eBay UK.

## Step 5: Connect and import

1. Go to **https://bargainbugs.co.uk/admin** and log in.
2. Open **eBay Sync** and click **Connect eBay account**.
3. Sign in as **bargainbugs** on eBay and click **Agree**.
4. You're sent back to the admin, which should show **● Connected as bargainbugs**.
5. Click **🔄 Sync now (full)**. Your listings, photos, prices, stock and categories are imported.
   - With a lot of listings, each click handles about 50 seconds of work and says "click again to continue". The automatic cron also finishes it on its own.

The connection lasts about **18 months**. The admin shows the expiry date, and when it's close you just click **Reconnect**.

## Step 6: Keep it in sync automatically

The sync URL is shown in **Admin ▸ eBay Sync ▸ Automatic sync**:

```
https://bargainbugs.co.uk/cron/ebay-sync?key=YOUR_CRON_SECRET
```

Each call:
- adds new eBay listings,
- updates price, stock and photos on changed listings,
- marks items **sold** or **ended** so they disappear from the shop,
- runs a full re-check once every 24 hours.

**On Vercel:** `vercel.json` already calls it once a day (the Hobby plan limit). To sync **every 15 minutes for free**:

1. Create a free account at **https://cron-job.org**.
2. **Create cronjob** ▸ URL = the sync URL above ▸ schedule **every 15 minutes** ▸ Save.

**On normal PHP hosting or a VPS**, add one server cron line instead:
```
* * * * * cd /path/to/site && php artisan schedule:run >> /dev/null 2>&1
```

Command-line options (local or SSH):
```
php artisan ebay:sync                 # automatic (full once a day, otherwise changes only)
php artisan ebay:sync --full          # re-check every listing now
php artisan ebay:sync --item=123456789012   # import or refresh one item
```

---

## Managing products after sync

- **⭐ Featured** puts a product on the home page. Toggle it in Admin ▸ Products.
- **🙈 Hide** keeps a product off the website (it stays on eBay). Hidden products stay hidden after syncs.
- **🔒 Lock my edits:** if you rewrite a title or description for the website, tick this so the sync doesn't overwrite it. Price and stock still follow eBay.
- **Categories** are created from the top-level eBay category (e.g. "Toys & Games"). Rename them, give them an emoji, reorder or hide them in Admin ▸ Categories.
- Prefer **Hide** over **Delete** for live eBay items: a deleted product that is still live on eBay comes back on the next sync.

## eBay rules to be aware of

- Your **eBay listings** must not tell buyers to buy from your website instead of eBay. That counts as fee avoidance and is against eBay policy. A website that *sends* people to eBay (which this site does) is fine.
- Purchases happen on eBay, so buyers keep eBay's Money Back Guarantee and you keep your feedback and seller ratings.

## Troubleshooting

| Problem | Fix |
|---|---|
| "API keys missing" in admin | The `EBAY_*` variables aren't set, or you didn't redeploy after adding them. |
| eBay says `invalid_scope` | Your keyset is missing a scope. Set `EBAY_SCOPES=https://api.ebay.com/oauth/api_scope` and reconnect. |
| eBay error page after clicking Agree | The RuName's "Auth accepted URL" must be exactly `https://bargainbugs.co.uk/ebay/callback`, and `EBAY_RUNAME` must be the RuName value, not the URL. |
| "Security check failed" | Clicking Connect and arriving back must happen in the same browser. Just try again. |
| Sync failed: "IAF token supplied is invalid" | The connection expired or was revoked. Click **Reconnect**. |
| Products not updating | Check **Sync history** in Admin ▸ eBay Sync, and that your cron-job.org job is running and returns HTTP 200. |
