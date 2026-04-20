# Assetry — Deployment Notes

## Folder layout on the server

Copy the contents of this `assetry/` folder into your web volume so it looks like:

```
/web/                  (or whatever your container mounts as the web root)
├── public/            ← Apache DocumentRoot points HERE
│   ├── index.php
│   ├── .htaccess
│   └── assets/
├── src/               ← must stay OUTSIDE public/
└── data/              ← created automatically; holds SQLite + uploads
```

## Apache vhost (must allow .htaccess overrides)

```apache
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    ServerName localhost

    DocumentRoot /var/www/public

    <Directory /var/www/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

Make sure `mod_rewrite` is enabled: `a2enmod rewrite && service apache2 restart`.

## Required PHP extensions

`pdo_sqlite` is mandatory. On a minimal Ubuntu/Debian PHP image:

```bash
apt-get update && apt-get install -y php-sqlite3
service apache2 restart
```

If the install does not survive container rebuilds, bake a tiny image:

```dockerfile
FROM <your-base-image>
RUN apt-get update && apt-get install -y php-sqlite3 && rm -rf /var/lib/apt/lists/*
```

## Permissions

The web server user needs to write to `data/`:

```bash
chown -R www-data:www-data /var/www/data
chmod -R 775 /var/www/data
```

## Environment variables

- `SESSION_SECRET` — set to a long random string for production.

## First login

- URL: `http://<server-ip>/`
- Username: `admin`
- Password: `admin`

**Change the admin password immediately** from the user menu.

## Backups

Everything you need to back up lives in the `data/` folder:
- `data/assetry.sqlite` — the database
- `data/uploads/` — uploaded images

Copy that single folder to back up or migrate the entire install.
