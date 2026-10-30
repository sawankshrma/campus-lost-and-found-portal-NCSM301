# Campus Lost & Found Portal

A minimal web portal to report lost/found items on campus. Built with HTML, CSS, JavaScript, jQuery, PHP (PDO), and MariaDB.

## Features

- Report lost or found items with category, location, date, description
- Search and filter reports by keyword, type, category, status
- Submit a claim on an item
- Status updates (Open → Claim Requested → Resolved) via AJAX without full page reload

## Tech Stack

| Layer     | Technology          |
|-----------|---------------------|
| Frontend  | HTML, CSS, JavaScript, jQuery 3.7.1 |
| Backend   | PHP (PDO)           |
| Database  | MariaDB             |
| Server    | Apache (httpd)      |

## Setup

```bash
# 1. Install dependencies
sudo dnf install httpd php php-mysqlnd mariadb-server

# 2. Start services
sudo systemctl start httpd mariadb

# 3. Create database
sudo mariadb -u root -e "
  CREATE DATABASE IF NOT EXISTS lost_and_found;
  CREATE USER IF NOT EXISTS 'lnf_user'@'localhost' IDENTIFIED BY 'lnf_pass123';
  GRANT ALL PRIVILEGES ON lost_and_found.* TO 'lnf_user'@'localhost';
  FLUSH PRIVILEGES;
"

# 4. Import schema and sample data
sudo mariadb -u root lost_and_found < database.sql

# 5. Deploy to Apache
sudo cp -r . /var/www/html/lostfound/
sudo chown -R apache:apache /var/www/html/lostfound/

# 6. Open in browser
xdg-open http://localhost/lostfound/index.php
```

## Project Structure

```
├── db.php          # Database connection (PDO)
├── index.php       # Main page: list, search, filter, report form, claim modal
├── add.php         # Handles new report submission
├── claim.php       # Handles claim submission (AJAX)
├── status.php      # Handles status update (AJAX, no page reload)
├── style.css       # Minimal CSS
├── database.sql    # Schema + sample data
└── README.md       # This file
```
