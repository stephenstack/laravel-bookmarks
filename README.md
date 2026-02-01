# 🏠 Homepage - Your Self-Hosted Start Page

> **Built in a weekend, polished for production.** A lightning-fast, beautifully designed bookmark manager and personal dashboard that actually feels good to use.

[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?logo=vue.js)](https://vuejs.org)
[![Inertia](https://img.shields.io/badge/Inertia.js-2-9553E9)](https://inertiajs.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**[Live Demo](#) • [Screenshots](#screenshots) • [Documentation](#) • [Report Bug](https://github.com/stephenstack/laravel-bookmarks/issues)**

---

> 💰 **Would you pay for a hosted version?** Skip the setup and get instant access with automatic updates, backups, and support. If this interests you, head to the [**Issues tab**](https://github.com/stephenstack/laravel-bookmarks/issues) and 👍 or comment on the pinned issue to show your interest!

---

## ✨ Why Another Bookmark Manager?

Because your browser's bookmark bar is chaos. Because you deserve a start page that's **fast**, **beautiful**, and **actually yours**.

This project was **vibe coded** in a few hours as a passion project—inspired by [Square UI Bookmarks](https://square-ui-bookmarks.vercel.app/)—but has evolved into a production-ready tool with real-world features:

- 🎨 **Gorgeous UI** - Dark/light themes, custom backgrounds, smooth animations
- ⚡ **Stupid Fast** - Inertia.js SPA experience with zero compromises
- 🔍 **Smart Search** - Global search (Ctrl+K) finds anything instantly
- 📦 **Self-Hosted** - Your data, your server, your rules
- 🏢 **Team Ready** - Admin can share company resources with all users
- 🎯 **Zero Config** - Works out of the box with SQLite

## 🎬 Screenshots

<table>
  <tr>
    <td><img src="docs/screenshots/dashboard.png" alt="Dashboard" /></td>
    <td><img src="docs/screenshots/bookmarks.png" alt="Bookmarks" /></td>
  </tr>
  <tr>
    <td align="center"><b>Dashboard View</b></td>
    <td align="center"><b>Bookmark Collections</b></td>
  </tr>
</table>

> 💡 **Tip**: Add your screenshots to `docs/screenshots/` before going public

---

## 🚀 Features

### Core Functionality

- **📑 Collections & Tags** - Organize bookmarks your way with nested collections and color-coded tags
- **🤖 Auto-Fetch Metadata** - Just paste a URL—title, description, favicon, and screenshot are scraped automatically
- **⭐ Smart Views** - Pre-built views for Favorites, Archive, Trash, and All Bookmarks
- **🔗 Link Intelligence** - Multi-source metadata fetching (Microlink API → Thum.io → Google Favicon)

### User Experience

- **⌨️ Keyboard Shortcuts** - Ctrl+K for search, hotkeys for everything
- **🎨 Theming** - Light/dark mode with custom backgrounds and opacity controls
- **📊 Dashboard Widgets** - Weather, Clock, Stats, and customizable cards
- **🏢 Company Resources** - Admins can create global bookmarks visible to all users

### Developer Features

- **🔐 2FA Support** - Built-in two-factor authentication
- **✉️ Email System** - Password resets, notifications, test emails from admin panel
- **🧪 Test Coverage** - Comprehensive test suite included
- **🔧 Admin Panel** - Full system settings, user management, and site configuration

---

## 🛠️ Tech Stack

- **Backend**: Laravel 11 (PHP 8.4+)
- **Frontend**: Vue 3 + Inertia.js (SPA architecture)
- **Styling**: Tailwind CSS with custom design system
- **Database**: SQLite (or MySQL/PostgreSQL)
- **Build**: Vite
- **Icons**: Lucide icons

---

## 💿 Quick Start

### Prerequisites

- PHP 8.1+ with required extensions
- Composer
- Node.js 18+
- SQLite (or MySQL/PostgreSQL)

### Installation (5 minutes)

```bash
# 1. Clone and install dependencies
git clone https://github.com/stephenstack/laravel-bookmarks.git
cd laravel-bookmarks
composer install
npm install

# 2. Environment setup
cp .env.example .env
php artisan key:generate

# 3. Database
touch database/database.sqlite
php artisan migrate --seed

# 4. Storage link
php artisan storage:link

# 5. Build and run
npm run build
php artisan serve
```

**That's it!** Open `http://localhost:8000` in your browser.

### Create Your Admin User

```bash
php artisan make:admin
# Follow the prompts to create your first admin account
```

---

## ⚙️ Configuration

### Email (Optional)

Configure SMTP in `.env` for password resets:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="hello@yourdomain.com"
```

Test emails from **Admin → Settings → Email Tab**.

### Weather Widget

Get a free API key from [OpenWeather](https://openweathermap.org/api):

```env
OPENWEATHER_API_KEY=your_api_key_here
```

---

## 🎯 Usage

### Adding Bookmarks

1. Click **"Add Bookmark"** or press `Ctrl+N`
2. Paste any URL—metadata is auto-fetched
3. Optionally assign to a **Collection** and add **Tags**
4. Save!

### Organizing

- **Collections**: Group related links (Work, Personal, Learning, etc.)
- **Tags**: Cross-collection labels with custom colors
- **Favorites**: Star important bookmarks for quick access
- **Archive**: Hide bookmarks without deleting them
- **Trash**: Soft-delete with 30-day recovery

### Admin Features

Access **Admin Panel** → **Settings**:

- Manage users and permissions
- Configure company-wide bookmarks
- Customize branding and theme
- View system stats

---

## 🗺️ Roadmap

- [ ] Browser extension for one-click bookmarking
- [ ] Import from browser bookmarks (HTML/JSON)
- [ ] Public bookmark sharing links
- [ ] API for third-party integrations
- [ ] Mobile app (React Native)
- [ ] Collaborative collections

**Got ideas?** [Open an issue](https://github.com/stephenstack/laravel-bookmarks/issues) or vote on existing ones!

---

## 🤝 Contributing

Contributions are **welcome and appreciated**! Here's how:

1. **Fork** the repository
2. **Create** a feature branch (`git checkout -b feature/amazing-idea`)
3. **Write tests** for your changes
4. **Run tests** to ensure nothing breaks:
    ```bash
    php artisan test
    ```
5. **Commit** your changes (`git commit -m 'Add amazing idea'`)
6. **Push** to your branch (`git push origin feature/amazing-idea`)
7. **Open** a Pull Request

### Development Commands

```bash
npm run dev          # Vite dev server with HMR
php artisan serve    # Laravel dev server
php artisan test     # Run test suite
npm run build        # Production build - This app comes with pre-built assets, but you can rebuild if needed
```

---

## 📸 Credits & Inspiration

- UI/UX inspired by [Square UI Bookmarks](https://square-ui-bookmarks.vercel.app/) by [@ln-dev7](https://github.com/ln-dev7)
- Built with ❤️ using Laravel, Vue.js, and Inertia.js
- Icons by [Lucide](https://lucide.dev)

---

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

---

## 💬 Support

- **Issues**: [GitHub Issues](https://github.com/stephenstack/laravel-bookmarks/issues)
- **Discussions**: [GitHub Discussions](https://github.com/stephenstack/laravel-bookmarks/discussions)
- **Email**: your@email.com

---

<p align="center">
  Made with ☕ by <a href="https://github.com/stephenstack">@stephenstack</a>
  <br>
  <sub>If you find this useful, consider giving it a ⭐</sub>
</p>
