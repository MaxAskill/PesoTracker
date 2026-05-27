# Cloudflare Pages Deployment

Use Cloudflare Pages for the Vue frontend only. The Laravel API should be deployed separately, then its public API URL should be added as a Pages environment variable.

## Build Settings

- Framework preset: `Vue`
- Root directory: `frontend`
- Build command: `npm run build`
- Build output directory: `dist`
- Node version: use the Cloudflare default unless the build complains

## Environment Variables

Add this in Cloudflare Pages under Settings > Environment variables:

```env
VITE_API_BASE_URL=https://pesotracker.onrender.com/api
```

Replace the value with the deployed Laravel API URL. Do not use `127.0.0.1`, `localhost`, or a LAN IP such as `192.168.x.x` for production because visitors' browsers cannot reach your local machine.

## Vue Router Fallback

`public/_redirects` is included so direct visits to routes like `/login`, `/register`, and `/dashboard` load the Vue app instead of returning a 404.

## Direct Upload Option

If you do not want to connect GitHub yet:

```bash
cd frontend
npm run build
npx wrangler pages deploy dist
```

For normal deployment, connecting the repository in the Cloudflare dashboard is cleaner because each push can trigger a new build.

## Backend Reminder

When the Laravel backend is deployed, update CORS if needed so it accepts requests from your Cloudflare Pages URL, for example:

```text
https://pesotracker.pages.dev
https://your-custom-domain.com
```
