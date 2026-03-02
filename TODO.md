# TODO: Replace Placeholder / Configure Production Values

Use this checklist before going live.

## 1) Replace required placeholder media files

These files currently exist as text placeholders and **must** be replaced with real binary assets:

- [ ] `assets/logo/muddy-river-leather.png`
  - Final transparent brand logo PNG.
  - Recommended width: 1200px+.

- [ ] `assets/images/hero.jpg`
  - Homepage hero image (premium leather/wood lifestyle shot).
  - Recommended size: ~2000x1200, compressed for web.

- [ ] `assets/images/textures/subtle-grain.png`
  - Lightweight tileable texture (optional but recommended).

- [ ] `assets/icons/favicon.ico`
  - Real ICO with common sizes (16x16, 32x32, 48x48).

- [ ] `assets/icons/apple-touch-icon.png`
  - Real Apple touch icon (180x180).

## 2) Configure environment variables

Create `.env` from `.env.example` and set real values:

- [ ] `SITE_URL=https://mrlgoods.com`
- [ ] `STRIPE_SECRET_KEY=sk_live_...`
- [ ] (Optional) `STRIPE_PUBLISHABLE_KEY=pk_live_...`

## 3) Install backend dependency

- [ ] Run `composer install --no-dev --optimize-autoloader` to install `stripe/stripe-php`.

## 4) Verify server/runtime setup

- [ ] Set Caddy root to repo path (example in `Caddyfile.example`).
- [ ] Ensure `php-fpm` socket matches `php_fastcgi` config.
- [ ] Ensure web user can write to `data/` for contact form logs.

## 5) Final smoke test

- [ ] `/` loads with real logo and hero image.
- [ ] `/products/` loads Stripe products.
- [ ] “Buy” button redirects to Stripe Checkout.
- [ ] `/contact-us/` successfully submits and logs data.
- [ ] Mobile nav opens/closes and keyboard navigation works.
