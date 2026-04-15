# Setup

## Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Update your `.env` file with your database credentials:

```text
DB_DATABASE=timetrack_pro
DB_USERNAME=root
DB_PASSWORD=your_password
```

## Database Setup

```bash
php artisan migrate
```

## Seed Default Data

```bash
php artisan db:seed
```

This will create:

- **Default** organization unit
- Shift codes **AA-GG** (Saturday/Sunday: no time window,
  Monday-Friday: 08:00 - 17:00)

## Generate Setup Config

```bash
php artisan make:config
```

This creates `setup/config.json` with blank default shift codes
and default minimum overtime hours. Update the file with your
organization's shift codes:

```json
{
    "default_shift_codes": [],
    "minimum_overtime_hours": 1
}
```

## Mail Configuration

The application sends password reset emails. Update your `.env` with your mail credentials:

### Gmail

1. Enable **2-Step Verification** on your Google Account → Security
2. Go to **App passwords** → create one (select "Other")
3. Update your `.env`:

```text
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-16-char-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Outlook

1. Go to [Microsoft Security](https://account.live.com/proofs/manage) → **App passwords** → create one
2. Update your `.env`:

```text
MAIL_MAILER=smtp
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_USERNAME=your-email@outlook.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@outlook.com
MAIL_FROM_NAME="${APP_NAME}"
```

> **Note:** Using `MAIL_MAILER=log` will write emails to `storage/logs/laravel.log` instead of sending them. Useful for local development without configuring real mail credentials.

## Run the Application

```bash
npm start
```

This starts both `php artisan serve` and `vite` concurrently. Visit [http://127.0.0.1:8000](http://127.0.0.1:8000).

---

Back: [Installation](./INSTALLATION.md)
