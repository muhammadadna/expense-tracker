# 🌐 Panduan Memasang Telegram Bot di Live Server (Vercel)

Panduan langkah demi langkah untuk mengaktifkan dan menghubungkan fitur **Telegram Bot** pada aplikasi **Expense Tracker** yang di-deploy di **Vercel**.

---

## 📋 Ikhtisar Langkah

```
[ 1. Siapkan Token BotFather ]
              ↓
[ 2. Set Environment Variables di Vercel Dashboard ]
              ↓
[ 3. Jalankan Migrasi Database Production ]
              ↓
[ 4. Deploy Perubahan Kode ke Vercel (Git Push) ]
              ↓
[ 5. Daftarkan URL Webhook ke Telegram ]
              ↓
[ 6. Verifikasi & Pairing Akun ]
```

---

## 1. Dapatkan Token Telegram Bot
Jika belum memiliki bot:
1. Buka aplikasi Telegram, cari akun resmi **`@BotFather`**.
2. Kirim pesan: `/newbot`.
3. Masukkan nama tampilan bot (contoh: `Expense Tracker Bot`).
4. Masukkan username bot berakhiran `bot` (contoh: `my_expense_track_bot`).
5. `@BotFather` akan memberikan **HTTP API Token** seperti:
   `7123456789:AAHkQWERTYUIOPasdfghjklzxcvbnm`
6. Simpan token ini untuk langkah berikutnya.

---

## 2. Konfigurasi Environment Variables di Vercel

Buka dashboard Vercel Anda di browser:
1. Buka project Expense Tracker di [vercel.com/dashboard](https://vercel.com/dashboard).
2. Masuk ke menu **Settings** $\rightarrow$ **Environment Variables**.
3. Tambahkan 3 variabel baru berikut:

| Key | Value | Contoh |
| :--- | :--- | :--- |
| `TELEGRAM_BOT_TOKEN` | Token dari `@BotFather` | `7123456789:AAHk...` |
| `TELEGRAM_BOT_USERNAME` | Username bot (tanpa tanda `@`) | `my_expense_track_bot` |
| `TELEGRAM_WEBHOOK_SECRET` | String acak rahasia untuk keamanan webhook | `rahasia_webhook_12345` |

> [!IMPORTANT]
> **Catatan Penting untuk Serverless (Vercel)**:
> Pastikan variabel `CACHE_STORE` di Vercel diatur ke `database` (bukan `array`), karena Vercel bersifat *stateless serverless*:
> ```env
> CACHE_STORE=database
> ```
> Dengan `CACHE_STORE=database`, data sesi pilihan tombol kategori dan pencegah duplikasi chat tersimpan aman di database SQL Anda.

---

## 3. Jalankan Migrasi Database di Server Production

Tabel `users` di database production Anda (misal Neon PostgreSQL atau Cloud MySQL) perlu diperbarui dengan kolom Telegram baru (`telegram_chat_id`, `telegram_connect_token`, `telegram_connect_token_expires_at`).

### Opsi A: Jalankan dari Komputer Lokal via Docker (Paling Praktis)
Jika Anda menghubungkan `.env` lokal ke database live:
```bash
docker exec -it expense-tracker-app php artisan migrate --force
```

### Opsi B: Jalankan SQL Manual di GUI Database (DBeaver / TablePlus / Neon Console)
Jika Anda tidak bisa menjalankan artisan ke database production, jalankan query SQL berikut langsung pada database production Anda:

**Untuk MySQL:**
```sql
ALTER TABLE users 
ADD COLUMN telegram_chat_id VARCHAR(255) NULL UNIQUE AFTER theme,
ADD COLUMN telegram_connect_token VARCHAR(32) NULL,
ADD COLUMN telegram_connect_token_expires_at TIMESTAMP NULL;

CREATE INDEX users_telegram_connect_token_index ON users (telegram_connect_token);
```

**Untuk PostgreSQL (NeonDB):**
```sql
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS telegram_chat_id VARCHAR(255) UNIQUE,
ADD COLUMN IF NOT EXISTS telegram_connect_token VARCHAR(32),
ADD COLUMN IF NOT EXISTS telegram_connect_token_expires_at TIMESTAMP NULL;

CREATE INDEX IF NOT EXISTS users_telegram_connect_token_idx ON users (telegram_connect_token);
```

---

## 4. Deploy Perubahan Kode ke Vercel

Commit dan push seluruh kode baru ke repository Git (GitHub / GitLab) yang terhubung ke Vercel:

```bash
git add .
git commit -m "feat: implement telegram bot expense tracking integration"
git push origin main
```

Vercel akan otomatis melakukan *trigger build* dan mendeploy versi terbaru aplikasi Anda.

---

## 5. Daftarkan Webhook Vercel ke Telegram

Setelah aplikasi di Vercel selesai di-deploy dan URL live Anda sudah aktif (contoh: `https://expense-tracker.vercel.app`), daftarkan endpoint webhook ke Telegram.

Ada **3 cara mudah** (pilih salah satu):

### Cara A: Cukup Buka Link di Browser (Paling Cepat ⚡)
Ganti `<TOKEN>`, `<DOMAIN_VERCEL>`, dan `<SECRET>` dengan data Anda, lalu paste ke address bar browser:

```text
https://api.telegram.org/bot<TOKEN>/setWebhook?url=https://<DOMAIN_VERCEL>/telegram/webhook&secret_token=<SECRET>
```

**Contoh nyata:**
```text
https://api.telegram.org/bot7123456789:AAHk.../setWebhook?url=https://expense-tracker.vercel.app/telegram/webhook&secret_token=rahasia_webhook_12345
```

Jika sukses, browser akan menampilkan response JSON:
```json
{"ok": true, "result": true, "description": "Webhook was set"}
```

---

### Cara B: Menggunakan Perintah Artisan Lokal
Jika file `.env` di lokal Anda sudah diisi `TELEGRAM_BOT_TOKEN`, jalankan:

```bash
docker exec -it expense-tracker-app php artisan telegram:set-webhook https://expense-tracker.vercel.app/telegram/webhook
```

---

### Cara C: Menggunakan cURL di Terminal Mac
```bash
curl -X POST "https://api.telegram.org/bot<TOKEN>/setWebhook" \
  -d "url=https://expense-tracker.vercel.app/telegram/webhook" \
  -d "secret_token=rahasia_webhook_12345"
```

---

## 6. Verifikasi & Menghubungkan Akun

1. Buka website Anda yang sudah live di Vercel: `https://expense-tracker.vercel.app`.
2. Login ke akun Anda $\rightarrow$ masuk ke menu **Profile**.
3. Di card **Telegram Bot Integration**, klik tombol **Hubungkan Telegram**.
4. Akan muncul kode pairing:
   ```text
   /connect EXP-XXXXXX
   ```
5. Klik tautan **"Buka langsung di Telegram"** atau buka bot Anda di Telegram dan ketik perintah `/connect EXP-XXXXXX`.
6. Bot akan membalas:
   > 🎉 **Selamat, Akun Berhasil Terhubung!**  
   > Halo [Nama Anda], akun Telegram Anda kini telah terhubung dengan Expense Tracker.
7. Coba catat transaksi pertama Anda via chat:
   ```text
   makan 25k soto ayam
   ```
8. Periksa database dan Google Sheets Anda—transaksi akan langsung tercatat secara instan!

---

## 🔍 Troubleshooting (Kendala Umum di Vercel)

### 1. Webhook Mengembalikan Error 403 (Unauthorized)
* **Penyebab**: `TELEGRAM_WEBHOOK_SECRET` di Vercel Environment Variables tidak sama dengan `secret_token` saat Anda memanggil `setWebhook`.
* **Solusi**: Samakan nilai string secret di keduanya, lalu panggil kembali URL `setWebhook`.

### 2. Tombol Kategori Interaktif Berputar / Sesi Kedaluwarsa
* **Penyebab**: Vercel menggunakan `CACHE_DRIVER=array`.
* **Solusi**: Pastikan di Environment Variables Vercel ditambahkan `CACHE_STORE=database`.

### 3. Cek Status Webhook Telegram Kapan Saja
Untuk memeriksa apakah Telegram berhasil mengirim webhook ke Vercel tanpa error:
Buka di browser:
```text
https://api.telegram.org/bot<TOKEN>/getWebhookInfo
```
Periksa baris `"last_error_message"`. Jika kosong dan `"pending_update_count": 0`, berarti webhook berjalan dengan sempurna.
