# Instalasi CPMB Laravel App dengan Docker

Dokumen ini menjelaskan langkah pemasangan aplikasi CPMB Laravel di VPS menggunakan Docker Compose. Jalankan perintah dari root project `laravel-app`.

## 1. Kebutuhan Server

- VPS Ubuntu 22.04/24.04 atau distro Linux lain yang mendukung Docker.
- Domain/subdomain sudah diarahkan ke IP VPS jika aplikasi akan dipakai publik.
- Port aplikasi terbuka. Default project ini memakai `HTTP_PORT=8080`.
- Git, Docker Engine, dan Docker Compose plugin.

## 2. Install Docker di Ubuntu

Hapus paket Docker lama jika ada:

```bash
for pkg in docker.io docker-doc docker-compose docker-compose-v2 podman-docker containerd runc; do sudo apt-get remove -y $pkg; done
```

Install dependency repository:

```bash
sudo apt-get update
sudo apt-get install -y ca-certificates curl
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc
```

Tambahkan repository Docker:

```bash
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt-get update
```

Install Docker:

```bash
sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
```

Opsional agar user saat ini bisa menjalankan Docker tanpa `sudo`:

```bash
sudo usermod -aG docker $USER
newgrp docker
```

Validasi:

```bash
docker --version
docker compose version
```

## 3. Clone Project

```bash
git clone https://github.com/Gusma-crypto/cpmb.git
cd cpmb/laravel-app
```

Jika repository langsung berisi file Laravel tanpa folder `laravel-app`, cukup masuk ke folder hasil clone tersebut.

## 4. Siapkan Script

```bash
chmod +x build.sh deploy.sh docker/php/entrypoint.sh
```

Jika file script pernah diedit di Windows dan muncul error `bad interpreter`, jalankan:

```bash
sed -i 's/\r$//' build.sh deploy.sh docker/php/entrypoint.sh
```

## 5. Build Image Aplikasi

```bash
./build.sh v1.0.0
```

Perintah ini membuat image default:

```text
cpmb-php:v1.0.0
cpmb-php:latest
```

Jika ingin push ke registry:

```bash
REGISTRY=ghcr.io/gusma-crypto PUSH_IMAGE=true ./build.sh v1.0.0
```

## 6. Konfigurasi Environment

Buat file `.env.docker`:

```bash
cp .env.docker.example .env.docker
```

Edit `.env.docker`:

```bash
nano .env.docker
```

Minimal sesuaikan nilai berikut:

```env
COMPOSE_PROJECT_NAME=cpmb
PROJECT_NAME=cpmb
HTTP_PORT=8080

APP_NAME=CPMB
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://domain-anda.com

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=cpmb
DB_USERNAME=cpmb
DB_PASSWORD=ganti-password-kuat
DB_ROOT_PASSWORD=ganti-root-password-kuat

REDIS_HOST=redis
QUEUE_CONNECTION=database
RUN_MIGRATIONS=true
```

Generate `APP_KEY`:

```bash
docker run --rm cpmb-php:v1.0.0 php artisan key:generate --show
```

Salin output perintah tersebut ke `APP_KEY` di `.env.docker`.

## 7. Deploy

```bash
./deploy.sh v1.0.0
```

Saat deploy, Docker Compose akan menjalankan service berikut:

- `app`: Laravel PHP-FPM.
- `web`: Nginx, expose ke `HTTP_PORT`.
- `db`: MySQL 8.4.
- `redis`: Redis 7.
- `queue`: Laravel queue worker.

Migration otomatis dijalankan saat container start jika `RUN_MIGRATIONS=true`.

## 8. Cek Hasil Deploy

Cek status container:

```bash
docker compose --env-file .env.docker ps
```

Cek log:

```bash
docker compose --env-file .env.docker logs -f
```

Akses aplikasi:

```text
http://IP-SERVER:8080
```

atau domain sesuai `APP_URL` dan konfigurasi reverse proxy.

## 9. Seed Data Awal

Jika membutuhkan role, permission, user awal, tahun akademik, program, dan data contoh:

```bash
docker compose --env-file .env.docker exec app php artisan db:seed --force
```

Seeder membuat akun contoh seperti `admin@example.com`, `staff@example.com`, dan `test@example.com` dengan password default dari seeder.

## 10. Perintah Maintenance

Jalankan Artisan:

```bash
docker compose --env-file .env.docker exec app php artisan optimize:clear
docker compose --env-file .env.docker exec app php artisan optimize
docker compose --env-file .env.docker exec app php artisan queue:restart
```

Restart container:

```bash
docker compose --env-file .env.docker restart
```

Stop container:

```bash
docker compose --env-file .env.docker down
```

Stop container dan hapus volume database/redis:

```bash
docker compose --env-file .env.docker down -v
```

Gunakan `down -v` hanya jika data database memang boleh dihapus.

## 11. Update Versi

```bash
git pull
./build.sh v1.0.1
./deploy.sh v1.0.1
```

Jika memakai registry:

```bash
REGISTRY=ghcr.io/gusma-crypto PUSH_IMAGE=true ./build.sh v1.0.1
REGISTRY=ghcr.io/gusma-crypto ./deploy.sh v1.0.1
```

## 12. Backup Database

```bash
docker compose --env-file .env.docker exec db sh -c 'mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' > db_backup.sql
```

Jika `DB_USERNAME` atau `DB_DATABASE` diganti, sesuaikan perintah backup.

## 13. Restore Database

```bash
docker compose --env-file .env.docker exec -T db sh -c 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' < db_backup.sql
```

## 14. Catatan Reverse Proxy

Jika VPS memakai Nginx/Caddy/Traefik di luar Docker, arahkan reverse proxy ke:

```text
http://127.0.0.1:8080
```

Samakan `APP_URL` di `.env.docker` dengan domain publik, misalnya:

```env
APP_URL=https://pmb.domain-anda.com
```
