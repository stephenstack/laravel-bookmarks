# Homepage v1.0.0

A modern, fast, and customizable self-hosted start page for developers and power users. Built with Laravel, Vue.js, and Inertia, this application serves as your central hub for bookmarks, resources, and quick stats.

> **Note**: This project was fully **vibe coded** in just a few hours.
> Inspiration drawn from [Square UI Bookmarks](https://square-ui-bookmarks.vercel.app/) & [ln-dev7/square-ui](https://github.com/ln-dev7/square-ui).

## 🚀 Features

- **Bookmark Management**: Organize your links into **Collections** and **Tags**.
- **Smart Dashboard**: Customizable stats cards including Weather, Clock, and Stats.
- **Link Interrogation**: Automatically scrapes titles, descriptions, and high-quality images/favicons when adding a link.
- **Search**: Fast global search (Ctrl+K).
- **Dark/Light Mode**: Fully themable UI with support for custom background images and opacity.
- **Company Resources**: Admins can set global links for all users (e.g., Company Wiki, HR Portal).
- **System Views**: Dedicated views for Favorites, Archive, and Trash.

## 💿 Installation

### 1. Clone & Install

```bash
git clone https://github.com/stephenstack/laravel-bookmarks
cd laravel-bookmarks
composer install
npm install
```

### 2. Environment & Key

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database

```bash
touch database/database.sqlite
php artisan migrate --seed
```

### 4. Build & Run

```bash
npm run build
php artisan serve
```

## 👤 Admin Setup

To create your first admin user, use the built-in command:

```bash
php artisan make:admin
```

This will prompt you for your name, email, and password.

## 📧 Email Setup

For password resets and system notifications, configure your SMTP settings in the `.env` file:

```env
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

You can test your email configuration in **Admin > Site Settings > Email Tab** using the "Send Test Email" button.

## 🔗 Web Link Interrogation

When you add a new bookmark, the system "interrogates" the URL. It uses a multi-layered approach to fetch the best metadata:

1. **Microlink API**: Attempts to get high-quality screenshots and structured meta.
2. **Thum.io**: Fallback for clean site screenshots if no Open Graph image is found.
3. **Google Favicon Service**: Ensures a high-resolution favicon is always present.

## 🤝 Contribution Guide

We love contributions!

1. Fork the repo.
2. Create a feature branch (`git checkout -b feature/amazing-feature`).
3. **Write Tests**: Ensure your feature is covered by automated tests.
4. **Run Tests**: Verify everything is passing before submitting.

    ```bash
    # Run all tests
    php artisan test

    # Run specific test file
    php artisan test tests/Feature/Bookmarks/BookmarkControllerTest.php
    ```

5. Commit your changes (`git commit -m 'Add amazing feature'`).
6. Push to the branch (`git push origin feature/amazing-feature`).
7. Open a Pull Request.

## 📄 License

MIT License.
