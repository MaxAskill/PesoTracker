# Render Backend Deployment

This backend is set up for Render using Docker because receipt OCR needs Tesseract installed in the server image.

## 1. Prepare GitHub

Push the project repo to GitHub. Keep the current folder structure:

```text
Project/
  frontend/
  backend/
```

## 2. Create The Render Web Service

In Render:

1. Click **New > Web Service**.
2. Connect your GitHub repo.
3. Set **Root Directory** to `backend`.
4. Set **Runtime** to `Docker`.
5. Choose the free instance type if it is available.
6. Deploy.

Render's Docker docs say Docker services can build from a `Dockerfile`, and web services need to bind to `0.0.0.0` on the service port. This setup uses Apache and Render's `$PORT`.

## 3. Add Environment Variables

In the Render service, add these environment variables:

```env
APP_NAME=PesoTracker
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://your-render-service.onrender.com

LOG_CHANNEL=stderr
LOG_LEVEL=info

DB_CONNECTION=pgsql
DB_URL=postgresql://user:password@host:5432/database
DB_SSLMODE=require

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=public

RUN_MIGRATIONS=true
TESSERACT_PATH=/usr/bin/tesseract
```

Generate `APP_KEY` locally:

```bash
cd backend
php artisan key:generate --show
```

## 4. Database

Render may not offer a permanent free database for every account. If Render asks you to pay for a database, use a free external PostgreSQL provider such as Supabase or Neon, then paste its connection string into `DB_URL`.

Use PostgreSQL if possible:

```env
DB_CONNECTION=pgsql
DB_URL=postgresql://...
```

Do not paste the connection string into `DB_DATABASE`. If Laravel shows an error like this:

```text
Host: 127.0.0.1, Port: 5432, Database: your-postgres-connection-string
```

it means the connection string is in the wrong variable. Put the full connection string in `DB_URL`, then remove any incorrect `DB_DATABASE`, `DB_HOST`, `DB_PORT`, `DB_USERNAME`, or `DB_PASSWORD` values unless your database provider specifically tells you to use separate fields.

## 5. Update Cloudflare Pages

After Render gives you a backend URL, update the Cloudflare Pages frontend environment variable:

```env
VITE_API_BASE_URL=https://your-render-service.onrender.com/api
```

Redeploy the frontend after changing this value.

## 6. Health Check

After deployment, test these URLs:

```text
https://your-render-service.onrender.com/up
https://your-render-service.onrender.com/api/test
```

The API test should return:

```json
{"message":"PesoTracker API is working"}
```

## Notes

- Free Render web services spin down after inactivity, so the first request after a quiet period can be slow.
- Render's filesystem is ephemeral. Uploaded receipt images can disappear after redeploys/restarts. OCR will still work for extracting receipt text, but image history should use cloud storage later if you need persistence.
- If receipt scanning fails, check Render logs for Tesseract errors.
