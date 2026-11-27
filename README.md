# RoadMate 🚗

A car rental website for our CS 157A final project, built with PHP and MySQL.

**Team:** Evie Ho, Mari Yamaguchi, Keyi Tan, Angel Cruz, Quang Lam Trinh

---

## Part 1: One-time setup (everyone does this once)

You need 3 things installed: **MySQL + MySQL Workbench**, **PHP**, and **Git**.
Check PHP and Git by typing these in Terminal (Mac) or Command Prompt (Windows):

```
php -v
git --version
```

If either one says "command not found", see [Troubleshooting](#troubleshooting).

### Step 1: Download the project

```
cd Documents
git clone https://github.com/Evie0007/RoadMate.git
cd RoadMate
```

✅ You should now have a `RoadMate` folder in your Documents.

### Step 2: Create the database

1. Open **MySQL Workbench** and click your local connection.
2. Click **File → Open SQL Script** and choose `database/roadmate_schema.sql`.
3. Click the **⚡ lightning bolt** button (the plain one, not the one with a cursor on it).
4. Do the same thing for `database/roadmate_seed.sql`.

**Order matters:** run `roadmate_schema.sql` first, then `roadmate_seed.sql`.

✅ Click the refresh icon in the **Schemas** panel on the left. You should see a database called `roadmate`.

### Step 3: Add your MySQL password

The website needs your MySQL password to connect, but your password should **never** go on GitHub. So everyone makes their own private copy of the settings file.

Make the copy:

```
# Mac
cp config/config.example.php config/config.php

# Windows
copy config\config.example.php config\config.php
```

Then open `config/config.php` and change this one line to the password you use in MySQL Workbench:

```php
'password' => 'YOUR_MYSQL_PASSWORD',
```

✅ Save the file. GitHub will automatically ignore `config.php`, so your password stays on your computer.

### Step 4: Start the website

Inside the `RoadMate` folder, run:

```
php -S localhost:8000
```

Leave this window open while you work. To stop the website, press **Control + C**.

Now open your browser and go to: **http://localhost:8000/db_test.php**

✅ You should see a green box saying **"Connected to the roadmate database."** 🎉 You're done with setup!

Other pages:
- Admin dashboard: http://localhost:8000/admin/dashboard.php
- Vehicle search: http://localhost:8000/catalog/search.php

---

## Part 2: Every time you work on the project

```
# 1. Get everyone's latest work
git checkout main
git pull

# 2. Make your own branch (only the first time for each feature)
git checkout -b feature/your-feature-name

# 3. Save your work
git add .
git commit -m "Short description of what you changed"

# 4. Upload your branch
git push -u origin feature/your-feature-name
```

Then go to the repo on GitHub and click **Compare & pull request**. Evie will review it and merge it into `main`.

**3 rules:**
1. ❌ Never upload `config/config.php` (it has your password).
2. ❌ Never push straight to `main`. Always use your own branch and a pull request.
3. ❌ Don't change the database files without asking the group first. If the database changes, everyone has to redo Step 2.

---

## Part 3: Using the database in your page

Put this line at the top of any page inside `admin/` or `catalog/`:

```php
require_once __DIR__ . '/../includes/db.php';
```

Now you have `$conn`, the connection to the database. Use it instead of the fake demo arrays.

**Example: get all available SUVs**

```php
$stmt = $conn->prepare('SELECT * FROM Vehicle WHERE Status = ?');
$status = 'Available';
$stmt->bind_param('s', $status);          // 's' = string, 'i' = number
$stmt->execute();
$vehicles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
```

Always use `prepare` + `bind_param` like this when a value comes from a form. It stops hackers from breaking the database (this is called SQL injection).

**Showing database errors to the user**

Some database rules (like "this car is already booked for those dates") come back as errors. Catch them like this:

```php
try {
    $stmt->execute();
} catch (mysqli_sql_exception $e) {
    $error = $e->getMessage();   // e.g. "This vehicle is already reserved for an overlapping rental period."
}
```

The full list of tables and columns is in `database/roadmate_schema.sql`, and the ER diagram is in `docs/`.

---

## Test accounts

These accounts are already in the database from Step 2.

| Who | Email | Password |
|---|---|---|
| Customer | jordan.miller@example.com | `RoadMate123!` |
| Admin | maria.lopez@roadmate.com | `Admin123!` |

Every customer account uses `RoadMate123!` and every admin account uses `Admin123!`.
To check a password in PHP, use `password_verify()`.

Want to start over with fresh sample data? Just redo Step 2.

---

## Troubleshooting

| You see this | What to do |
|---|---|
| `Missing config/config.php` | You skipped Step 3. Make the copy of the config file. |
| `Access denied for user 'root'` | The password in `config/config.php` is wrong. Use the same one as in MySQL Workbench. |
| `Unknown database 'roadmate'` | You skipped Step 2, or ran only one of the two files. |
| `Can't connect to MySQL server` | MySQL isn't running. Mac: **System Settings → MySQL → Start**. Windows: open **Services** and start **MySQL80**. |
| `php: command not found` | Install PHP. Mac: `brew install php` (get Homebrew from https://brew.sh). Windows: install XAMPP and only use its PHP. |
| `git: command not found` | Mac: run `xcode-select --install`. Windows: install Git from https://git-scm.com. |
| `Address already in use` | The website is already running in another window. Use that one, or run `php -S localhost:8001` instead. |
| Red ❌ in Workbench when running a SQL file | Make sure you used the plain ⚡ button and ran `roadmate_schema.sql` before `roadmate_seed.sql`. |

Still stuck? Send a screenshot of the error in the group chat.

---

## What's in each folder

| Folder / file | What it's for | Who owns it |
|---|---|---|
| `admin/` | Admin dashboard pages | Mari |
| `catalog/` | Vehicle search and details pages | Keyi |
| `config/` | Database settings (your password goes in `config.php`) | Everyone |
| `database/` | SQL files that build the database and add sample data | Evie |
| `docs/` | ER diagram | Evie |
| `includes/db.php` | Connects every page to the database | Evie |
| `db_test.php` | Checks that your setup works | Evie |
