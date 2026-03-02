# Muddy River Leather Website (mrlgoods.com)

Production-ready static-first website + PHP endpoints for Stripe product listing/checkout and contact form handling.

## Features

- Clean folder-per-page architecture:
  - `/` → `index.html`
  - `/products/` → `products/index.html`
  - `/about-us/` → `about-us/index.html`
  - `/contact-us/` → `contact-us/index.html`
- Global CSS and JS loaded on every page.
- Page-specific script folders for Products, About, and Contact.
- Stripe integration (server-side only secret key):
  - `GET /api/stripe/products.php` returns Products + Prices JSON.
  - `POST /api/stripe/create-checkout-session.php` creates Checkout Sessions.
- Contact form endpoint with validation + anti-spam (honeypot + minimum submit time) and logging to `data/contact-submissions.log`.
- SEO + social metadata, canonical URLs, `robots.txt`, and `sitemap.xml`.
- Accessibility support: semantic HTML, skip link, keyboard focus states, ARIA labels, reduced motion support.

## Project Structure

```text
.
├── index.html
├── assets/
├── products/
├── about-us/
├── contact-us/
├── styles/main.css
├── global-scripts/
├── scripts/
├── api/
├── data/
├── .env.example
├── .gitignore
├── composer.json
├── Caddyfile.example
├── robots.txt
├── sitemap.xml
└── README.md
```

## Logo & Image Placeholders

- Expected logo path: `assets/logo/muddy-river-leather.png`.
- If your official logo file is not present, replace the placeholder file in that path with your final brand asset.
- Placeholder asset files are intentionally text-based (not binary) in this repo to support binary-restricted review environments. Replace each with real image/icon files before production:
  - `assets/images/hero.jpg`
  - `assets/icons/favicon.ico`
  - `assets/icons/apple-touch-icon.png`
  - `assets/images/textures/subtle-grain.png`

## Environment Variables

Copy `.env.example` to `.env` and update values:

```bash
cp .env.example .env
```

Required:

- `SITE_URL` (e.g., `https://mrlgoods.com`)
- `STRIPE_SECRET_KEY` (`sk_live_...` in production)

Optional:

- `STRIPE_PUBLISHABLE_KEY` (not required for current redirect-by-session-url flow)

> Never expose `STRIPE_SECRET_KEY` in browser scripts.

## FreeBSD 15 Setup (Caddy + PHP-FPM + Composer)

### 1) Install packages

```sh
sudo pkg update
sudo pkg install -y caddy php84 php84-fpm php84-curl php84-mbstring php84-json php84-openssl php84-phar php84-tokenizer composer
```

### 2) Deploy site files

```sh
sudo mkdir -p /usr/local/www/mrlgoods.com
sudo rsync -av ./ /usr/local/www/mrlgoods.com/
cd /usr/local/www/mrlgoods.com
cp .env.example .env
# edit .env with real values
```

### 3) Install Stripe SDK

```sh
composer install --no-dev --optimize-autoloader
```

### 4) Configure and start PHP-FPM

- Ensure `/usr/local/etc/php-fpm.d/www.conf` has a listening socket, e.g.:
  - `listen = /var/run/php-fpm.sock`
  - Socket owner/group readable by Caddy user.

```sh
sudo service php_fpm enable
sudo service php_fpm start
```

### 5) Configure Caddy

- Copy `Caddyfile.example` contents into `/usr/local/etc/caddy/Caddyfile`.
- Adjust domain/root/socket as needed.

```sh
sudo service caddy enable
sudo service caddy restart
```

### 6) Verify

```sh
fetch -qo- http://localhost/
fetch -qo- http://localhost/api/stripe/products.php
```

## Stripe Setup

1. In Stripe Dashboard, create Products and one-time Prices.
2. Add your secret key to `.env` (`STRIPE_SECRET_KEY`).
3. Visit `/products/` and confirm products render.
4. Click **Buy** to create a checkout session and redirect to Stripe Checkout.

### Security Notes

- `price_id` is validated with regex and quantity bounds (1–10).
- Checkout and product listing run server-side with Stripe SDK.
- Recommend adding per-IP rate limiting in Caddy or a WAF for abuse prevention.

## Contact Form Behavior

- Frontend posts to `/contact-us/scripts/contact-submit.php` (wrapper to `/api/contact.php`).
- Backend validates `name`, `email`, `message`.
- Anti-spam:
  - Honeypot field `company` must remain empty.
  - Minimum 3-second time-to-submit required.
- Messages append to `data/contact-submissions.log`.

## Local Testing

For static UI only:

```sh
python3 -m http.server 8080
```

For PHP endpoints, use a PHP-capable web server (Caddy + php-fpm preferred) or PHP built-in server for quick checks:

```sh
php -S 127.0.0.1:8080
```

## Deployment Notes

- Keep file and folder names URL-safe (no spaces).
- Use absolute paths (`/styles/main.css`, `/global-scripts/global.js`) for shared assets.
- Ensure `data/` is writable by web server user for contact logging.
- Rotate/monitor logs and back them up as needed.
