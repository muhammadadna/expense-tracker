# 🐳 Panduan Menjalankan Expense Tracker dengan Docker (PHP 8.5)

Dokumen ini berisi rangkuman perintah untuk menjalankan dan mengelola aplikasi **Expense Tracker** menggunakan Docker dengan PHP 8.5 dan terhubung ke database MySQL lokal di komputer host.

---

## 📌 Ringkasan Arsitektur
- **Aplikasi (PHP 8.5 + Apache)**: Berjalan di dalam container Docker (`webdevops/php-apache-dev:8.5-alpine`).
- **Database (MySQL)**: Berjalan langsung di komputer Mac Anda (port `3306`), diakses oleh container via `host.docker.internal`.
- **URL Akses**: [http://localhost:8000](http://localhost:8000)

---

## 🚀 1. Menjalankan & Menghentikan Container

| Perintah | Fungsi |
| :--- | :--- |
| `docker compose up -d` | Menyalakan container di background |
| `docker compose down` | Menghentikan dan menghapus container |
| `docker compose restart` | Merestart container |
| `docker compose ps` | Memeriksa status container yang sedang berjalan |
| `docker compose logs -f` | Melihat log output server Apache & PHP secara real-time |

---

## ⚙️ 2. Menjalankan Perintah Laravel Artisan & Composer

Karena PHP lokal tidak ada, jalankan perintah melalui container `expense-tracker-app`:

### Perintah Database & Migrasi
```bash
# Menjalankan migrasi database
docker exec -it expense-tracker-app php artisan migrate

# Rollback migrasi
docker exec -it expense-tracker-app php artisan migrate:rollback

# Menjalankan seeder
docker exec -it expense-tracker-app php artisan db:seed
```

### Pengujian (Testing)
```bash
# Menjalankan seluruh automated test suite
docker exec -it expense-tracker-app php artisan test

# Menjalankan test spesifik (contoh: TransactionTest)
docker exec -it expense-tracker-app php artisan test --filter TransactionTest
```

### Google Sheets Integration
```bash
# Menguji koneksi dan backup ke Google Sheets
docker exec -it expense-tracker-app php artisan sheets:test
```

### Cache & Optimization
```bash
# Membersihkan seluruh cache (config, route, view)
docker exec -it expense-tracker-app php artisan optimize:clear

# Menjalankan tinker
docker exec -it expense-tracker-app php artisan tinker
```

### Composer
```bash
# Menjalankan composer install di dalam container
docker exec -it expense-tracker-app composer install

# Menjalankan composer dump-autoload
docker exec -it expense-tracker-app composer dump-autoload
```

---

## 💻 3. Masuk ke Dalam Terminal Container (Interactive Shell)

Jika ingin bekerja langsung di dalam container tanpa mengetik `docker exec` berulang kali:

```bash
docker exec -it expense-tracker-app sh
```
Setelah masuk, Anda bisa langsung menjalankan perintah seperti biasa:
```sh
php artisan migrate
php artisan test
exit
```

---

## 🎨 4. Frontend Assets (Vite & Tailwind)

Jalankan perintah ini di terminal komputer lokal Anda (di root proyek):

```bash
# Mode development (live reload)
npm run dev

# Mode production build
npm run build
```

---

## 💡 5. Tips Shortcut (Alias Zsh / Bash)

Agar tidak perlu mengetik `docker exec -it expense-tracker-app ...` yang panjang, Anda bisa menambahkan alias di file `~/.zshrc`:

```bash
# Buka ~/.zshrc di editor
nano ~/.zshrc

# Tambahkan baris ini di paling bawah:
alias art="docker exec -it expense-tracker-app php artisan"
alias dcomposer="docker exec -it expense-tracker-app composer"
```

Setelah itu reload konfigurasi:
```bash
source ~/.zshrc
```

Sekarang Anda bisa menjalankan perintah dengan sangat singkat:
```bash
art migrate
art test
art sheets:test
dcomposer install
```

---

## 🛠️ 6. Troubleshooting Cepat

1. **Database Connection Refused (`host.docker.internal`):**
   - Pastikan MySQL di Mac lokal aktif: periksa via DBeaver atau command line MySQL.
   - Periksa file `.env` pastikan `DB_HOST=host.docker.internal`.
2. **Port 8000 Bentrok:**
   - Jika port 8000 dipakai aplikasi lain, ubah baris `ports: - "8000:80"` di `docker-compose.yml` menjadi port lain (misal `"8082:80"`).
3. **Izin File (Permission Denied):**
   - `docker-compose.yml` sudah diset menggunakan `APPLICATION_UID=1000` dan `APPLICATION_GID=1000` sehingga file yang dibuat di container kompatibel dengan user Mac Anda.
