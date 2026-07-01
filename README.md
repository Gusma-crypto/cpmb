
# CPMB Laravel App

Aplikasi CPMB berbasis Laravel 12, Inertia, Vue, PostgreSQL, Redis, dan queue worker. Repository ini sudah dilengkapi konfigurasi Docker untuk deploy ke VPS.

## Stack

- PHP 8.3 FPM
- Laravel 12
- Nginx 1.27
- PostgreSQL 16
- Redis 7
- Node.js 22 untuk build asset Vite
- Docker Compose

## Struktur Docker

- `docker/php/Dockerfile`: build image aplikasi Laravel, install dependency Composer, build asset Vite, dan menyiapkan runtime PHP-FPM.
- `docker/php/entrypoint.sh`: menyiapkan permission storage/cache, publish asset public ke volume Nginx, cache config/routing/view, dan menjalankan migration jika `RUN_MIGRATIONS=true`.
- `docker/nginx/default.conf`: konfigurasi Nginx untuk Laravel.
- `docker-compose.yaml`: menjalankan service `app`, `web`, `db`, `redis`, dan `queue`.
- `.env.docker.example`: template environment production Docker.
- `build.sh`: build image aplikasi.
- `deploy.sh`: membuat direktori storage/cache, memastikan `.env.docker` tersedia, lalu menjalankan Docker Compose.

## Quick Start Docker

```bash
cd laravel-app
chmod +x build.sh deploy.sh docker/php/entrypoint.sh
./build.sh v1.0.0
cp .env.docker.example .env.docker
```

Edit `.env.docker`, terutama:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com
APP_KEY=
DB_PASSWORD=ganti-password-kuat
HTTP_PORT=8080
RUN_MIGRATIONS=true
```

Generate `APP_KEY` dari image yang sudah dibuild:

```bash
docker run --rm cpmb-php:v1.0.0 php artisan key:generate --show
```

Masukkan hasilnya ke `APP_KEY`, lalu deploy:

```bash
./deploy.sh v1.0.0
```

Aplikasi akan berjalan di port `HTTP_PORT`, default `http://localhost:8080`.

## Perintah Operasional

Cek container:

```bash
docker compose --env-file .env.docker ps
```

Lihat log:

```bash
docker compose --env-file .env.docker logs -f
```

Jalankan Artisan:

```bash
docker compose --env-file .env.docker exec app php artisan migrate --force
docker compose --env-file .env.docker exec app php artisan db:seed --force
docker compose --env-file .env.docker exec app php artisan optimize:clear
```

Masuk shell container aplikasi:

```bash
docker compose --env-file .env.docker exec app bash
```

## Update Aplikasi

```bash
git pull
./build.sh v1.0.1
./deploy.sh v1.0.1
```

Gunakan tag versi baru setiap deploy agar rollback lebih mudah.

## Backup Database

```bash
docker compose --env-file .env.docker exec db pg_dump -U cpmb -d cpmb -x -O > db_backup.sql
```

## Restore Database

```bash
docker compose --env-file .env.docker exec -T db psql -U cpmb cpmb < db_backup.sql
```

## Dokumentasi Instalasi

Panduan instalasi VPS yang lebih lengkap tersedia di [`instalasi.md`](instalasi.md).

