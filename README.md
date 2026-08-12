# Floating Social Media Icons

A lightweight WordPress plugin that displays a floating bar of social media icons, fully configurable from the admin — no external icon fonts or CDN dependencies.

## Features

- Choose which side of the screen the bar floats on (left/right) and its distance from the top
- Admin screen (Settings > Floating Social Icons) to add/remove social networks and their link URLs
- Bundled inline-SVG icons: Facebook, Instagram, X (Twitter), YouTube, LinkedIn, Pinterest, TikTok, WhatsApp, Email, RSS
- Icon size, shape (circle/square/none), spacing, and color controls (icon, background, hover)
- Optional "hide on mobile" toggle with a configurable breakpoint

## Installation

1. Copy (or zip and upload) this folder into `wp-content/plugins/` on your WordPress site.
2. Activate **Floating Social Media Icons** from the Plugins screen.
3. Go to **Settings > Floating Social Icons** to add your links and adjust appearance.

## Development

Plugin code lives in:

- `floating-social-icons.php` — plugin bootstrap, defaults, settings accessor
- `includes/class-fsi-icons.php` — bundled SVG icon library
- `includes/class-fsi-settings.php` — admin settings page
- `includes/class-fsi-frontend.php` — front-end rendering
- `assets/` — admin and front-end CSS/JS

See `readme.txt` for the WordPress.org-style plugin readme.
