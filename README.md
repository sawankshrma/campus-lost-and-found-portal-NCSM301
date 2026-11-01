# Campus Lost & Found Portal

## Code file structure

```text
.
├── index.php       Main page, report list, search, filters, and AJAX actions
├── add.php         Inserts new lost/found reports
├── claim.php       Inserts claims and updates report status
├── claims.php      Returns claims for a report as JSON
├── status.php      Handles email-protected report resolution
├── db.php          Creates the PDO connection to MariaDB
├── database.sql    Creates the database tables and sample data
├── style.css       Application styles
└── README.md       File structure and deployment instructions
```

## Deploy instructions

1. Install XAMPP for Windows with Apache, PHP 7.4 or newer, and MariaDB.
2. Copy the project folder to:

   ```text
   C:\xampp\htdocs\lostfound
   ```

3. Start Apache and MariaDB from the XAMPP Control Panel.
4. Open phpMyAdmin at `http://localhost/phpmyadmin`.
5. Create a MariaDB database named `lost_and_found`.
6. Create the application database user with the same credentials configured in `db.php`:

   ```text
   Username: lnf_user
   Password: lnf_pass123
   Host: 127.0.0.1
   ```

7. Import `database.sql` into the `lost_and_found` database.
8. Confirm that `db.php` contains the correct MariaDB host, database name, username, and password.
9. Open the application at:

   ```text
   http://localhost/lostfound/index.php
   ```
