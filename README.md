# Homepage

A modern, fast, and customizable self-hosted start page for developers and power users. Built with Laravel, Vue.js, and Inertia, this application serves as your central hub for bookmarks, resources, and quick stats.

## 🚀 Features

- **Bookmark Management**: Organize your links into **Collections** and **Tags**.
- **Smart Dashboard**: Customizable stats cards including:
    - **Weather**: Live weather updates via Open-Meteo (no API key required) with 15-minute caching and city selection.
    - **Clock**: Real-time digital clock with toggleable 12h/24h formats.
    - **Stats**: Quick view of total bookmarks, favorites, and collections.
    - **Recent & Archived**: Access your history and keep your main view clean.
- **Search**: Fast search across all your bookmarks and tags.
- **Dark/Light Mode**: Fully themable UI using Tailwind CSS v4.
- **Drag & Drop**: Intuitive sorting for your collections.
- **Collections**:
    - **Slug-based Routing**: Shareable, clean URLs for your collections (e.g., `/bookmarks/collection/my-project`).
    - **System Views**: Dedicated views for Favorites, Archive, and Trash.
- **Privacy Focused**: Self-hosted means your data stays with you.

## 🛠️ Tech Stack

- **Backend**: [Laravel 12](https://laravel.com)
- **Frontend**: [Vue 3](https://vuejs.org) (Composition API, TypeScript)
- **Routing**: [Inertia.js 2.0](https://inertiajs.com)
- **Styling**: [Tailwind CSS 4](https://tailwindcss.com)
- **Icons**: [Lucide](https://lucide.dev)
- **Database**: SQLite (Default) / MySQL / PostgreSQL

## 📦 Prerequisites

Ensure you have the following installed on your server or local machine:

- **PHP**: 8.2 or higher
- **Node.js**: 20.x or higher
- **Composer**: Latest version
- **SQLite** (optional, but enabled by default)

## 💿 Installation & Deployment

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/homepage.git
cd homepage
```

### 2. Install Dependencies

Install PHP backend dependencies:

```bash
composer install --optimize-autoloader --no-dev
```

Install Node.js frontend dependencies:

```bash
npm install
```

### 3. Environment Setup

Copy the example environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

### 4. Database Setup

By default, the application uses SQLite. Create the database file:

```bash
touch database/database.sqlite
```

Run the migrations to create the database schema:

```bash
php artisan migrate --force
```

### 5. Build Assets

Build the frontend assets for production:

```bash
npm run build
```

### 6. Serving the Application

You can serve the application using PHP's built-in server (for testing) or a web server like Nginx/Apache.

**Local Development:**

```bash
composer run dev
```

(This runs `php artisan serve`, `npm run dev`, and queue workers concurrently)

**Production (Nginx Example):**
Point your Nginx document root to the `public/` directory. Ensure proper permissions are set for `storage/` and `bootstrap/cache/`:

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## 🎮 Usage

1.  **Register/Login**: Create your admin account.
2.  **Dashboard**: Click the **Settings (⚙️)** icon next to "DASHBOARD" to toggle visibility of cards (Weather, Clock, Stats).
3.  **Add Bookmarks**: Use the **+ Add Bookmark** button. You can assign them to collections and add tags immediately.
4.  **Organize**:
    - **Archive**: Move old links to the archive to keep things tidy.
    - **Trash**: Deleted items go to trash and can be restored or permanently deleted.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

This project is open-source and licensed under the [MIT permissions](LICENSE).
