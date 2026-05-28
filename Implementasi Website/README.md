# ToySight — Smart Analytics for Toy Retail Business

![ToySight](public/assets/img/favicon.svg)

A modern **Business Intelligence (BI) web application** built with PHP MVC for toy retail business analytics. Built on top of a Mexican Toys Sales data warehouse (star schema with 829K+ sales records), ToySight provides real-time dashboards, deep analytics, CRUD management, and exportable reports.

> **ToySight – Smart Analytics for Toy Retail Business**
> Professional · Modern · Clean · Built for Enterprise Dashboard usage.

---

## ✨ Key Features

### 📊 Analytics & Dashboards
- **Live KPI Dashboard** — Revenue, Orders, Units Sold, Gross Profit with growth trends and sparklines
- **Sales Analytics** — Daily/monthly trends, store ranking, category mix, day-of-week behavior
- **Product Analytics** — Category performance, price-tier distribution, top revenue products with margins
- **Inventory Analytics** — Stock distribution (out / low / normal / overstock), per-store and per-category breakdowns, low-stock alerts
- **Store Performance** — Revenue by city, location type analysis, store ranking with age-vs-revenue

### 🔐 Role-Based Access Control (RBAC)
Four pre-configured roles with distinct permissions:

| Role | Access |
|------|--------|
| **Admin** | Full system access — all analytics, all CRUD, user management |
| **Manager** | All analytics + reports (read-only, no CRUD) |
| **Sales** | Dashboard + Sales analytics + Sales CRUD + Product view |
| **Warehouse** | Dashboard + Inventory analytics + Inventory/Product CRUD |

### 📝 CRUD Operations
- **Products** — Add/edit/delete products with automatic price-tier classification (Budget / Mid-Range / Premium)
- **Sales** — Record sales with auto-calculated revenue, COGS, gross profit, and margin %
- **Inventory** — Per-store stock management with low-stock alerts
- **Stores** — Manage retail network across 29 Mexican cities, 4 location types
- **Users** — Admin can manage system users and roles

### 📑 Reports & Export
- 4 pre-built report types: Sales Summary, Product Performance, Store Ranking, Category Sales
- CSV export with UTF-8 BOM (Excel-friendly)
- Print / Save as PDF with branded ToySight header

### 🎨 Design
- Modern enterprise dashboard with glassmorphism touches
- Navy + Cyan + Orange brand palette
- Fully responsive (desktop / tablet / mobile)
- Inter font, ApexCharts for visualizations
- Light & accessible UI

---

## 🛠️ Tech Stack

- **Backend**: PHP 8.0+ (native MVC, no framework dependencies)
- **Database**: SQLite (default, included) — MySQL configuration provided
- **Frontend**: Native HTML/CSS/JS + ApexCharts (CDN)
- **Architecture**: MVC with helpers (Database, Router, Auth, Controller)
- **Auth**: Session-based with bcrypt password hashing and CSRF protection

---

## 🚀 Quick Start

### Requirements
- **PHP** 8.0 or higher (`pdo`, `pdo_sqlite`, `mbstring`, `json` extensions)
- Web browser (Chrome / Firefox / Safari / Edge — latest)
- No additional dependencies — runs out of the box

### Installation (1 minute)

1. **Extract the ZIP** into your preferred directory.
2. **Verify the database** exists at:
   ```
   database/mexican_toys_dws.db
   ```
3. **Start PHP's built-in server** from the project root:
   ```bash
   php -S localhost:8000
   ```
4. **Open your browser** at:
   ```
   http://localhost:8000
   ```
5. **Login** with one of the demo accounts (see below).

### Production Deployment (Apache / Nginx)
- Point your web root to the project root (`.htaccess` handles routing)
- Or set the document root to `public/` for cleaner URLs
- Ensure `storage/` and `database/` are writable by the web server

---

## 👤 Demo Accounts

All accounts are auto-created on first run. Use these to log in:

| Username | Password | Role | Full Name |
|----------|----------|------|-----------|
| `admin` | `admin123` | Admin | Carlos Hernandez |
| `manager` | `manager123` | Manager | Sofia Ramirez |
| `sales` | `sales123` | Sales | Diego Lopez |
| `warehouse` | `warehouse123` | Warehouse | Maria Gonzalez |

> The login page has a one-click autofill for each demo account.

---

## 📁 Project Structure

```
toysight/
├── app/
│   ├── controllers/         # AuthController, DashboardController, SalesController, etc.
│   ├── models/              # Database models for each entity
│   └── views/
│       ├── analytics/       # sales.php, product.php, inventory.php, store.php
│       ├── auth/            # login.php
│       ├── crud/            # *_index.php for CRUD pages
│       ├── dashboard/       # index.php (main dashboard)
│       ├── errors/          # 403, 404, 500
│       ├── layouts/         # main.php, print.php
│       ├── partials/        # sidebar.php, topbar.php
│       ├── reports/         # index.php, print.php
│       └── users/           # User management
├── config/
│   ├── app.php              # App name, version, tagline
│   ├── database.php         # SQLite/MySQL connection config
│   └── roles.php            # Role permissions matrix
├── database/
│   └── mexican_toys_dws.db  # SQLite warehouse DB (~85MB)
├── helpers/
│   ├── Auth.php             # Authentication & RBAC helper
│   ├── Controller.php       # Base controller
│   ├── Database.php         # PDO wrapper
│   ├── Router.php           # Simple router
│   └── url.php              # URL/asset/escape helpers
├── public/
│   ├── assets/
│   │   ├── css/app.css      # Main stylesheet (~1100 lines)
│   │   ├── css/print.css    # Print stylesheet
│   │   ├── js/app.js        # Sidebar, modals, toasts, shortcuts
│   │   └── img/favicon.svg
│   └── index.php            # Front controller
├── routes/
│   └── web.php              # Route definitions
├── storage/
│   └── exports/             # CSV exports go here
├── bootstrap.php            # App bootstrap
└── index.php                # Entry point (forwards to public/)
```

---

## 🗄️ Database Schema

ToySight reads from a **star schema warehouse** (table prefix `dw__`):

### Dimensions
- **`dw__dim_product`** — 35 products with cost, price, category, price tier
- **`dw__dim_store`** — 50 stores across 29 Mexican cities
- **`dw__dim_category`** — 5 categories (Toys, Art & Crafts, Games, Electronics, Sports)
- **`dw__dim_date`** — 638 date dimension records (2022-01-01 to 2023-09-30)

### Facts
- **`dw__fact_sales`** — 829,262 sales records (~$14.4M total revenue)
- **`dw__fact_inventory`** — 1,593 inventory snapshots

### App Tables
- **`app_users`** — Application user accounts (auto-created on first run)

---

## 🔧 Configuration

### Switch to MySQL

Edit `config/database.php`:
```php
return [
    'driver'   => 'mysql',          // Change from 'sqlite' to 'mysql'
    'mysql' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'database' => 'toysight',
        'username' => 'root',
        'password' => '',
        'charset'  => 'utf8mb4',
    ],
];
```

You'll need to migrate the SQLite data to MySQL using a tool like `sqlite3-to-mysql` or export/import via CSV.

### Customize App Name & Tagline

Edit `config/app.php`:
```php
return [
    'name'    => 'ToySight',
    'tagline' => 'Smart Analytics for Toy Retail Business',
    'version' => '1.0.0',
    // ...
];
```

### Add/Modify Roles

Edit `config/roles.php` — each role has a `permissions` array (e.g. `analytics.sales`, `crud.products`) and a `menu` array controlling sidebar visibility.

---

## ⌨️ Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `⌘ + K` (or `Ctrl + K`) | Focus global search |
| `Esc` | Close any open modal |

---

## 🎨 Branding & Customization

The brand identity is centralized:
- **Logo** — Inline SVG in `partials/sidebar.php`, `partials/topbar.php`, `auth/login.php`, `reports/print.php`, and `public/assets/img/favicon.svg`
- **Colors** — CSS variables at the top of `public/assets/css/app.css`:
  ```css
  --primary: #1E3A5F;     /* Navy */
  --secondary: #F59E0B;   /* Orange */
  --accent: #22D3EE;      /* Cyan */
  ```

To rebrand, update those three values + the SVG logo files.

---

## 🐛 Troubleshooting

**"could not find driver" error**
→ Install the SQLite PDO extension: `php -m | grep sqlite` should show `pdo_sqlite`. On Ubuntu: `sudo apt install php-sqlite3`.

**Blank page or 500 error**
→ Check PHP error log. Enable display errors temporarily by editing `bootstrap.php`:
```php
ini_set('display_errors', '1');
error_reporting(E_ALL);
```

**Database file is missing**
→ Make sure `database/mexican_toys_dws.db` exists. If you cloned the project without the DB, you'll need to obtain the original file.

**Forgot password**
→ Delete the `app_users` table from the SQLite DB; it auto-seeds on next page load with default demo credentials.

---

## 📜 License

This project is for educational and demonstration purposes. The Mexican Toys Sales dataset is publicly available for analytical exercises.

---

## 💡 Built With

- 💙 Care for clean code & enterprise UX
- ☕ A lot of coffee
- 🧸 A passion for retail analytics

---

**ToySight** — Smart Analytics for Toy Retail Business
v1.0.0 · © 2026
