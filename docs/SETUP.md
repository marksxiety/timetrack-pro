# Setup

Follow each step in order. After completing all steps, run `php artisan serve` to launch the application.

---

## Step 1 — Environment Configuration

Copy the example environment file and generate an application key:

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and update these required values:

| Variable | Description | Example |
|---|---|---|
| `APP_URL` | URL where the app runs | `http://localhost` or `http://127.0.0.1:8000` |
| `APP_TIMEZONE` | Server timezone | `Asia/Manila` |
| `DB_DATABASE` | MySQL database name | `timetrack-pro` |
| `DB_USERNAME` | MySQL username | `root` |
| `DB_PASSWORD` | MySQL password | `your_password` |
| `ADMIN_NAME` | Admin user's name | `Admin` |
| `ADMIN_EMAIL` | Admin user's email | `admin@example.com` |
| `ADMIN_EMPLOYEEID` | Admin employee ID | `ADMIN001` |
| `ADMIN_PASSWORD` | Admin password | `changeme` |

> Make sure the database exists in MySQL before proceeding. Create it with: `CREATE DATABASE timetrack_pro;`

---

## Step 2 — Install Frontend Dependencies

```bash
npm install
```

---

## Step 3 — Database Migration

Run the migrations to create all required tables:

```bash
php artisan migrate
```

---

## Step 4 — Create Storage Link

```bash
php artisan storage:link
```

This creates a symlink from `public/storage` to `storage/app/public`, required for serving user avatar images and uploaded files.

---

## Step 5 — Seed Default Data

```bash
php artisan db:seed
```

This creates:

- **Default** organization unit
- Shift codes **AA–GG** (Saturday/Sunday: no time window, Monday–Friday: 08:00–17:00)
- **Default settings** (minimum overtime hours, overtime minute step, default shift codes)
- **Admin user** from `.env` variables (`ADMIN_NAME`, `ADMIN_EMAIL`, `ADMIN_EMPLOYEEID`, `ADMIN_PASSWORD`)

---

## Step 6 — AI Configuration

The application uses AI for reason enhancement and report analysis. Supports any OpenAI-compatible provider.

Add these variables to your `.env`:

```text
AI_API_KEY=your-api-key
AI_MODEL=gpt-4o-mini
```

For custom providers (Azure, Ollama, LM Studio, etc.), also add:

```text
AI_BASE_URL=https://your-provider-endpoint/v1
```

| Variable | Required | Description |
|---|---|---|
| `AI_API_KEY` | Yes | Your provider's API key |
| `AI_MODEL` | Yes | Model identifier (e.g., `gpt-4o-mini`, `llama3`) |
| `AI_BASE_URL` | No | Custom endpoint URL. Leave blank for OpenAI |

> Without AI configuration, the enhance and analyze features will be unavailable. All other features work normally.

---

## Step 7 — Mail Configuration

The application sends password reset emails. By default, `MAIL_MAILER=log` writes emails to `storage/logs/laravel.log` — no setup needed for local development.

To send real emails, update these variables in your `.env` with your SMTP provider's credentials:

```text
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

## Step 8 — Clear Cache

Remove the Vite hot file (if it exists) and clear all Laravel caches:

```bash
rm -f public/hot
php artisan optimize:clear
```

The `public/hot` file is created by `npm run dev` and causes `@vite` to look for a running Vite dev server instead of using the built assets. Remove it before building for production.

---

## Step 9 — Build the front-end

```bash
npm run build
```

---

## Step 10 — Run the Application

```bash
php artisan serve
```

Visit **http://127.0.0.1:8000** to access the application.

---

## Summary Checklist

- [ ] `.env` copied and configured (database credentials, admin credentials)
- [ ] Application key generated (`php artisan key:generate`)
- [ ] Frontend dependencies installed (`npm install`)
- [ ] Database migrated (`php artisan migrate`)
- [ ] Storage link created (`php artisan storage:link`)
- [ ] Database seeded (`php artisan db:seed` — creates org unit, settings, admin user)
- [ ] AI configured (`AI_API_KEY`, `AI_MODEL`)
- [ ] Mail configured
- [ ] Front-end built (`npm run build`)
- [ ] Hot file removed and cache cleared (`rm -f public/hot && php artisan optimize:clear`)
- [ ] Application running (`php artisan serve` → http://127.0.0.1:8000)

---

Back: [Installation](./INSTALLATION.md)
