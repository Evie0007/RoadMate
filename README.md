# RoadMate: Car Rental Management System

CS 157A Final Project (PHP + MySQL)

Team: Evie Ho, Mari Yamaguchi, Keyi Tan, Angel Cruz, Quang Lam Trinh

## Folder structure

```
RoadMate/
├── admin/                   admin dashboard pages
├── catalog/                 vehicle search and details pages
├── config/
│   ├── config.example.php   template for your database login (committed)
│   └── config.php           YOUR copy with your password (NOT committed)
├── database/
│   ├── roadmate_schema.sql  creates the database, tables, triggers, view
│   └── roadmate_seed.sql    sample data for every table
├── docs/                    ER diagram (draw.io file + PNG)
├── includes/
│   └── db.php               shared MySQL connection, used by every page
└── db_test.php              connection test page
```

## First-time setup

### 1. Get the code
Clone this repo with GitHub Desktop (File > Clone repository) or:
```
git clone https://github.com/Evie0007/RoadMate.git
```

### 2. Create the database (MySQL Workbench)
1. Open your local MySQL connection.
2. File > Open SQL Script > `database/roadmate_schema.sql` > click the plain lightning bolt.
3. File > Open SQL Script > `database/roadmate_seed.sql` > click the plain lightning bolt.
4. Refresh the Schemas panel. You should see `roadmate` with 12 tables.

To reset to the starting data at any time, run both scripts again in the same order.

### 3. Add your database password
1. Copy `config/config.example.php` and name the copy `config/config.php`.
2. In `config.php`, replace `YOUR_MYSQL_PASSWORD` with the password you use in MySQL Workbench.

`config.php` is in `.gitignore`, so it stays on your computer only.

### 4. Run the site
From the RoadMate folder, start PHP's built-in server:
```
php -S localhost:8000
```
Then open:
- http://localhost:8000/db_test.php: should say "Connected to the roadmate database" and show row counts
- http://localhost:8000/admin/dashboard.php
- http://localhost:8000/catalog/search.php

Don't have PHP? On Mac: `brew install php`. On Windows: install XAMPP and use only its PHP (don't start XAMPP's MySQL, since you already run MySQL).

## Using the database in your page

Pages inside `admin/` or `catalog/` connect like this:

```php
<?php
require_once __DIR__ . '/../includes/db.php';   // gives you $conn

// Always use prepared statements when a value comes from the user (prevents SQL injection)
$stmt = $conn->prepare('SELECT * FROM Vehicle WHERE LocationID = ? AND Status = ?');
$stmt->bind_param('is', $locationId, $status);   // i = integer, s = string
$stmt->execute();
$vehicles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
```

This replaces the temporary `$_SESSION['demo_...']` arrays and hard-coded arrays in the prototype pages.

Database errors, including trigger messages such as "This vehicle is already reserved for an overlapping rental period.", come back as a `mysqli_sql_exception`:

```php
try {
    $stmt->execute();
} catch (mysqli_sql_exception $e) {
    $error = $e->getMessage();   // show this to the user
}
```

The table and column names are in `database/roadmate_schema.sql` and the ER diagram in `docs/`.

## Demo logins (from the seed data)

| Role | Email | Password |
|---|---|---|
| Customer | jordan.miller@example.com | RoadMate123! |
| Admin | maria.lopez@roadmate.com | Admin123! |

All customers use `RoadMate123!` and all admins use `Admin123!`. Check passwords with PHP's `password_verify()`.

## Team workflow

1. Before starting work, switch to `main` and get the latest code (GitHub Desktop: Fetch origin, then Pull).
2. Create a branch from `main` for your feature, e.g. `feature/reservations`.
3. Commit small, clear changes ("Add availability search query").
4. Push your branch and open a Pull Request into `main`.
5. The Database & Integration Lead reviews and merges it.

Rules:
- Never commit `config/config.php`.
- Don't change the database scripts on your own. Ask in the group chat first. If the schema changes, everyone must re-run both scripts.
- Never push straight to `main`.
