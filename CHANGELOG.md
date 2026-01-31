# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0] - 2026-01-31

### Added

- **Unified Bookmark Dashboard**: A sleek, high-performance interface for managing all your links.
- **Link Interrogation Service**: Automated metadata fetching for titles, descriptions, and thumbnails.
- **Dynamic Themes**: Support for light, dark, and custom background images with adjustable opacity.
- **Smart Components**:
    - Real-time Clock (12h/24h).
    - Weather Card (Auto-detecting locations, cached).
    - Statistical Overview (Quick counts).
- **Admin Control Panel**:
    - Site-wide branding (Logos, Site Name).
    - Global "Company Resources" collection for team-wide links.
    - System health checks and setup wizard.
- **Robust Management**:
    - Soft-delete (Trash) with restoration.
    - Archiving system for cleaner views.
    - Favorites (Starring) system.
    - Drag-and-drop reordering for collections.
- **Security**:
    - Admin-only settings access.
    - `make:admin` CLI command for secure initial setup.
    - SMTP testing tools built into the dashboard.

### Fixed

- Resolved issues with Favorites/Archive views not syncing properly with the store.
- Fixed tag filtering logic for company bookmarks.
- Corrected route name collisions for system actions.
- Improved sidebar active state detection.

### Inspiration

- UI inspired by [Square UI Bookmarks](https://square-ui-bookmarks.vercel.app/).
- Reimagined and built from scratch using Laravel & Inertia.
