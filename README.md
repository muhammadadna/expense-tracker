# 💰 FamilyBudget - Family Expense Tracker

A modern, collaborative expense tracking application built with Laravel. Track expenses together with your family members in real-time.

![Laravel](https://img.shields.io/badge/Laravel-12.x-red?logo=laravel)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-blue?logo=tailwindcss)
![License](https://img.shields.io/badge/License-MIT-green)

---

## ✨ Features

- 👨‍👩‍👧‍👦 **Family Groups** - Create or join a family group with invite codes
- 💳 **Expense Tracking** - Log daily expenses with categories and notes
- 📊 **Reports & Analytics** - Visualize spending with charts and trends
- 🌙 **Dark Mode** - Beautiful dark/light theme support
- 📱 **Responsive Design** - Works on desktop, tablet, and mobile
- 📤 **Google Sheets Backup** - Automatically sync transactions to Google Sheets
- 🔐 **Secure Authentication** - User registration with email verification

---

## 🛠️ Tech Stack

- **Backend:** Laravel 12, PHP 8.2+
- **Frontend:** Blade, TailwindCSS, Alpine.js
- **Database:** SQLite (default) / MySQL / PostgreSQL
- **Charts:** Chart.js
- **Icons:** Material Symbols

---

## 📦 Installation

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- SQLite / MySQL / PostgreSQL

### Step 1: Clone the Repository

```bash
git clone https://github.com/yourusername/expense-tracker.git
cd expense-tracker
```

### Step 2: Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### Step 3: Environment Setup

```bash
# Copy the example environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 4: Database Setup

```bash
# Create SQLite database (default)
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed default categories
php artisan db:seed
```

### Step 5: Build Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### Step 6: Start the Server

**Using Local PHP:**
```bash
php artisan serve
```

**Using Docker (PHP 8.5):**
```bash
docker compose up -d
```
*Lihat panduan lengkap Docker di [DOCKER_GUIDE.md](DOCKER_GUIDE.md).*

Visit `http://localhost:8000` in your browser.

---

## ⚙️ Configuration

### Environment Variables

Edit `.env` file to configure:

```env
APP_NAME="FamilyBudget"
APP_URL=http://localhost:8000

# Database (SQLite is default)
DB_CONNECTION=sqlite

# For MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=expense_tracker
# DB_USERNAME=root
# DB_PASSWORD=

# Google Sheets Integration (Optional)
GOOGLE_SHEET_ID=your_spreadsheet_id_here
```

### Theme Customization

Edit `tailwind.config.js` to change colors:

```javascript
colors: {
    "primary": "#13ec80",           // Change primary color
    "background-light": "#f6f8f7",  // Light mode background
    "background-dark": "#102219",   // Dark mode background
}
```

After changes, run: `npm run build`

---

## 📤 Google Sheets Integration (Optional)

To enable automatic transaction backup to Google Sheets:

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project and enable **Google Sheets API**.
3. Create a **Service Account** and download the JSON key.
4. **Google Sheets Setup**
   - **Local Dev**: Save the downloaded JSON key file as `storage/app/google-credentials.json`.
   - **Production (e.g., Vercel)**: Copy the content of the JSON key file, remove newlines (minify it), and set it as the `GOOGLE_CREDENTIALS` environment variable in your hosting provider.
5. Create a Google Spreadsheet with headers:
   ```
   ID | Date | Category | Amount | Note | User | Family | Created At
   ```
6. Share the spreadsheet with the service account email (Editor access).
7. Add the Spreadsheet ID to your `.env` file:
   ```
   GOOGLE_SHEET_ID=your_spreadsheet_id
   ```
8. Test the connection:
   ```bash
   php artisan sheets:test
   ```

---

## 🧪 Running Tests

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage
```

---

## 📁 Project Structure

```
├── app/
│   ├── Http/Controllers/     # Application controllers
│   ├── Models/               # Eloquent models
│   ├── Events/               # Event classes
│   ├── Listeners/            # Event listeners
│   └── Services/             # Service classes (GoogleSheetsService)
├── resources/
│   ├── views/                # Blade templates
│   │   ├── layouts/          # App layout & navigation
│   │   ├── dashboard.blade.php
│   │   ├── reports/          # Reports views
│   │   ├── family/           # Family management views
│   │   └── profile/          # Profile settings
│   └── css/                  # Stylesheets
├── routes/
│   └── web.php               # Web routes
├── database/
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
└── tailwind.config.js        # Tailwind CSS configuration
```

---

## 🚀 Deployment

### Deploy to Vercel

1. Install Vercel CLI: `npm i -g vercel`
2. Create `api/index.php` for serverless functions
3. Configure `vercel.json` for Laravel routing
4. Run: `vercel --prod`

### Deploy to Traditional Hosting

1. Upload files to server
2. Set document root to `/public`
3. Configure `.env` for production:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```
4. Run:
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   npm run build
   ```

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Commit your changes: `git commit -m 'Add amazing feature'`
4. Push to the branch: `git push origin feature/amazing-feature`
5. Open a Pull Request

---

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

---

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP Framework
- [TailwindCSS](https://tailwindcss.com) - Utility-first CSS
- [Alpine.js](https://alpinejs.dev) - Lightweight JavaScript
- [Chart.js](https://www.chartjs.org) - JavaScript charting
- [Material Symbols](https://fonts.google.com/icons) - Google Icons

---

Made with ❤️ for families who want to track their finances together.
