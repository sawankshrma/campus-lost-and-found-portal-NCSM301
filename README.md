# Campus Lost & Found Portal

A small web application for reporting lost and found items on campus. People can submit reports, search existing reports, submit claims, and resolve a report after confirming the reporter's email address.

This project is intentionally simple. It is suitable for learning PHP, HTML, JavaScript, SQL, and basic AJAX. It is not intended to be a production-ready system with accounts, email verification, or administrator controls.

## Contents

- [What the application does](#what-the-application-does)
- [How the workflow works](#how-the-workflow-works)
- [Important note about resolution security](#important-note-about-resolution-security)
- [Technology used](#technology-used)
- [Project files](#project-files)
- [Requirements](#requirements)
- [Installation on Linux](#installation-on-linux)
- [Installation on Windows](#installation-on-windows)
- [Opening the application](#opening-the-application)
- [Using the application](#using-the-application)
- [Understanding the database](#understanding-the-database)
- [How the code works](#how-the-code-works)
- [Testing the application](#testing-the-application)
- [Troubleshooting](#troubleshooting)
- [Limitations and possible improvements](#limitations-and-possible-improvements)

## What the application does

The portal provides four main capabilities:

1. **Submit a report**
   - Report an item as lost or found.
   - Add its title, category, location, date, description, and reporter contact details.

2. **Find reports**
   - Search by title, description, or location.
   - Filter by item type, category, and status.

3. **Submit a claim**
   - A person who believes an item belongs to them can submit their name, email, and an explanation.
   - The report changes from `Open` to `Claim Requested`.

4. **Resolve a report**
   - The original reporter can click `Resolve` after a claim has been submitted.
   - They must enter the email address used when creating the report.
   - The status changes to `Resolved` without reloading the whole page.

## How the workflow works

Each report follows this basic status flow:

```text
Open
  |
  | Someone submits a claim
  v
Claim Requested
  |
  | Reporter enters the matching reporter email
  v
Resolved
```

### 1. A report is created

The user fills in the **Submit a Report** form on the home page. The form sends the data to `add.php`, which inserts it into the `reports` table with the initial status `Open`.

### 2. A claim is submitted

The user clicks **Claim** beside an open report. A small form asks for:

- The claimant's name
- The claimant's email
- An explanation of why the item belongs to them

The form is sent to `claim.php` using AJAX. The claim is stored in the `claims` table. If the report is still open, its status becomes `Claim Requested`.

### 3. The report is resolved

When a report has the status `Claim Requested`, a **Resolve** button appears. Clicking it asks for the reporter's email. The browser sends the report ID, the requested status, and the entered email to `status.php`.

The server checks all of the following:

- The report ID is valid.
- The requested status is allowed.
- An email was supplied.
- The report is currently `Claim Requested`.
- The supplied email matches the stored reporter email, ignoring surrounding spaces and letter case.

Only if all checks pass does the server update the report to `Resolved`.

## Important note about resolution security

This project does not have user accounts, passwords, sessions, or real email verification. The email check is a lightweight ownership check: it confirms that the person knows the email address stored on the report.

That means anyone who knows or guesses the reporter's email could potentially resolve the report. This is acceptable for a minimal classroom project, but it should not be treated as strong authentication.

A production application should use an account system, a one-time email link, or a randomly generated secret code instead.

## Technology used

| Part | Technology |
| --- | --- |
| Frontend | HTML, CSS, JavaScript, jQuery 3.7.1 |
| Backend | PHP with PDO |
| Database | MariaDB or MySQL |
| Web server | Apache httpd |
| Database communication | Prepared SQL statements |

## Project files

```text
.
├── add.php        Handles new report submissions
├── claim.php      Handles claim submissions
├── database.sql   Creates the database tables and inserts sample data
├── db.php         Creates the PDO database connection
├── index.php      Main page, forms, report list, search, filters, and AJAX code
├── status.php     Handles status updates, including email-protected resolution
├── style.css      Page styling
└── README.md      This guide
```

### `index.php`

This is the page visitors open in their browser. It:

- Reads search and filter values from the URL.
- Queries reports from the database.
- Displays the report submission form.
- Displays all matching reports.
- Opens the claim form when **Claim** is clicked.
- Sends claims and resolution requests using jQuery AJAX.

### `add.php`

Reads the report form fields, checks that they are not empty, and inserts the report into the database using a prepared statement.

### `claim.php`

Reads the claim form fields, inserts a new claim, and changes an `Open` report to `Claim Requested`.

### `status.php`

Processes status changes. Resolution requests are protected by checking the supplied email against `reports.reporter_email` on the server.

### `db.php`

Contains the database connection settings. Change this file if your database host, database name, username, or password is different.

### `database.sql`

Contains the schema for:

- `reports`: one row for each lost or found item.
- `claims`: one row for each claim submitted against a report.

It also contains sample reports so the page is not empty after installation.

## Requirements

You need:

- A computer running Linux, macOS, or Windows.
- PHP 7.4 or newer. PHP 8.x is recommended.
- The PHP PDO MySQL driver.
- MariaDB or MySQL.
- Apache or another PHP-capable web server.
- A browser.
- Internet access for the jQuery CDN used by `index.php`, unless you replace it with a local copy of jQuery.

The commands below are written for Fedora, RHEL, or another Linux system using `dnf`.

## Installation on Linux

### 1. Install the required software

Open a terminal and run:

```bash
sudo dnf install httpd php php-mysqlnd mariadb-server
```

The packages provide:

- `httpd`: the Apache web server.
- `php`: the PHP interpreter.
- `php-mysqlnd`: PHP's MySQL/MariaDB driver.
- `mariadb-server`: the database server.

### 2. Start Apache and MariaDB

```bash
sudo systemctl enable --now httpd
sudo systemctl enable --now mariadb
```

To check that both services are running:

```bash
sudo systemctl status httpd
sudo systemctl status mariadb
```

Press `q` to leave a service status screen.

### 3. Create the database and application user

Run the following command:

```bash
sudo mariadb
```

Then paste the following SQL into the MariaDB prompt:

```sql
CREATE DATABASE IF NOT EXISTS lost_and_found;

CREATE USER IF NOT EXISTS 'lnf_user'@'localhost'
IDENTIFIED BY 'lnf_pass123';

CREATE USER IF NOT EXISTS 'lnf_user'@'127.0.0.1'
IDENTIFIED BY 'lnf_pass123';

GRANT ALL PRIVILEGES ON lost_and_found.* TO 'lnf_user'@'localhost';
GRANT ALL PRIVILEGES ON lost_and_found.* TO 'lnf_user'@'127.0.0.1';

FLUSH PRIVILEGES;
EXIT;
```

The username and password above must match the values in `db.php`.

> For a real deployment, use a long, unique database password instead of the example password in this guide.

### 4. Import the tables and sample data

From the project directory, run:

```bash
mariadb -u lnf_user -p lost_and_found < database.sql
```

When asked for the password, enter:

```text
lnf_pass123
```

The SQL file creates the tables and inserts sample reports. If you run this command more than once, the sample `INSERT` statements may create duplicate sample rows.

You can confirm that the tables exist:

```bash
mariadb -u lnf_user -p -e "USE lost_and_found; SHOW TABLES;"
```

You should see:

```text
claims
reports
```

### 5. Copy the project into Apache's web directory

From the directory containing this project, run:

```bash
sudo mkdir -p /var/www/html/lostfound
sudo cp -r . /var/www/html/lostfound/
sudo chown -R apache:apache /var/www/html/lostfound
```

If your Linux distribution uses a different Apache user, replace `apache:apache` with the correct web-server user. For example, Ubuntu commonly uses `www-data:www-data`.

### 6. Check the database connection settings

Open `db.php` and verify these values:

```php
"mysql:host=127.0.0.1;dbname=lost_and_found;charset=utf8mb4",
"lnf_user",
"lnf_pass123",
```

They must match your MariaDB setup:

| Setting | Expected value in the default setup |
| --- | --- |
| Database host | `127.0.0.1` |
| Database name | `lost_and_found` |
| Database user | `lnf_user` |
| Database password | `lnf_pass123` |

## Installation on Windows

The easiest way to run this project on Windows is with **XAMPP**. XAMPP bundles Apache, PHP, MariaDB, and phpMyAdmin into one installer.

### 1. Install XAMPP

1. Download XAMPP for Windows from the official Apache Friends website: <https://www.apachefriends.org/>.
2. Run the installer.
3. Keep these components selected:
   - Apache
   - MySQL
   - PHP
   - phpMyAdmin
4. Install XAMPP in the default location:

   ```text
   C:\xampp
   ```

If you choose another installation folder, use that folder in place of `C:\xampp` in the instructions below.

### 2. Copy the project into XAMPP

Create a folder named `lostfound` inside XAMPP's web directory:

```text
C:\xampp\htdocs\lostfound
```

Copy all project files into that folder. The final layout should look similar to this:

```text
C:\xampp\htdocs\lostfound\index.php
C:\xampp\htdocs\lostfound\db.php
C:\xampp\htdocs\lostfound\add.php
C:\xampp\htdocs\lostfound\claim.php
C:\xampp\htdocs\lostfound\status.php
C:\xampp\htdocs\lostfound\database.sql
C:\xampp\htdocs\lostfound\style.css
```

Do not open `index.php` by double-clicking it. PHP files must be opened through Apache using a browser URL.

### 3. Start Apache and MySQL

1. Open **XAMPP Control Panel**.
2. Click **Start** beside `Apache`.
3. Click **Start** beside `MySQL`.

Both modules should show a green `Running` status.

If Windows Firewall asks whether Apache should be allowed through the firewall, allow it on private networks so the local application can work.

### 4. Create the database and application user

Open this address in your browser:

```text
http://localhost/phpmyadmin
```

Then:

1. Click the **SQL** tab.
2. Paste the following SQL.
3. Click **Go**.

```sql
CREATE DATABASE IF NOT EXISTS lost_and_found;

CREATE USER IF NOT EXISTS 'lnf_user'@'127.0.0.1'
IDENTIFIED BY 'lnf_pass123';

GRANT ALL PRIVILEGES ON lost_and_found.*
TO 'lnf_user'@'127.0.0.1';

FLUSH PRIVILEGES;
```

This creates the same database credentials expected by the default `db.php` file.

> XAMPP commonly opens phpMyAdmin as the MariaDB `root` user. That is why the SQL above should be run in phpMyAdmin before using the separate application user.

### 5. Import the tables and sample data

In phpMyAdmin:

1. Click the `lost_and_found` database in the left sidebar.
2. Click the **Import** tab.
3. Click **Choose File**.
4. Select:

   ```text
   C:\xampp\htdocs\lostfound\database.sql
   ```

5. Scroll down and click **Import** or **Go**.

After a successful import, click the database name in the left sidebar. You should see the `reports` and `claims` tables.

The SQL file also inserts sample reports. Importing it repeatedly may create duplicate sample reports.

### 6. Check the database connection settings

Open this file in a text editor:

```text
C:\xampp\htdocs\lostfound\db.php
```

The default connection should look like this:

```php
"mysql:host=127.0.0.1;dbname=lost_and_found;charset=utf8mb4",
"lnf_user",
"lnf_pass123",
```

If you changed the database username or password, update the same values in `db.php`.

### 7. Open the application

Open this address:

```text
http://localhost/lostfound/index.php
```

You should see the portal and its sample reports.

### 8. Stopping the application

When you are finished, open the XAMPP Control Panel and click **Stop** beside Apache and MySQL. You do not need to uninstall anything; start them again whenever you want to use the project.

### Windows troubleshooting

#### Apache will not start

Another program may already be using port `80` or `443`. Common examples include IIS, Skype, or another web server.

In XAMPP Control Panel, click **Config** beside Apache and open `httpd.conf`. Find:

```apache
Listen 80
```

Change it to another unused port, such as:

```apache
Listen 8080
```

Then find:

```apache
ServerName localhost:80
```

Change it to:

```apache
ServerName localhost:8080
```

Restart Apache and open the application using:

```text
http://localhost:8080/lostfound/index.php
```

#### MySQL will not start

Another MySQL or MariaDB service may already be running. Open Windows **Services**, look for another MySQL/MariaDB service, and stop it temporarily if it is not needed. Then start MySQL from the XAMPP Control Panel.

#### “Access denied for user” appears

Confirm that:

- MySQL is running in XAMPP.
- The `lnf_user` account was created for host `127.0.0.1`.
- The password in `db.php` is `lnf_pass123`, or matches the password you chose.
- The database is named `lost_and_found`.

#### The browser downloads the PHP file or displays PHP code

You opened the file directly instead of through Apache. Use:

```text
http://localhost/lostfound/index.php
```

Do not use a path beginning with `file:///`.

## Opening the application

Once Apache and MariaDB are running, open:

```text
http://localhost/lostfound/index.php
```

You can also use:

```text
http://127.0.0.1/lostfound/index.php
```

You should see the portal page and the sample reports.

## Using the application

### Search and filter reports

At the top of the page:

1. Type a word into **Search**. The search checks the title, description, and location.
2. Optionally choose a type: `Lost` or `Found`.
3. Optionally choose a category.
4. Optionally choose a status.
5. Click **Search**.

Click **Clear** to return to the complete report list.

### Submit a report

Complete every field in **Submit a Report**:

- Choose `Lost` or `Found`.
- Enter a short title.
- Choose a category.
- Enter the location.
- Select the date on which the item was lost or found.
- Enter the reporter's name.
- Enter the reporter's email.
- Describe the item.

Click **Submit Report**. The form is sent to the server and the page returns to the report list.

The email is important because it is used later to confirm resolution.

### Submit a claim

1. Find an item that may belong to you.
2. Click **Claim**.
3. Enter your name, email, and explanation.
4. Click **Submit Claim**.

The claim is submitted in the background. The page then reloads so that the updated report status is visible.

### Resolve a report

1. Find a report with the status `Claim Requested`.
2. Click **Resolve**.
3. Enter the reporter email originally used for that report.
4. Confirm the request.

If the email matches, the status changes to `Resolved` immediately without a full page reload. If it does not match, the report remains unchanged.

## Understanding the database

### `reports` table

The `reports` table stores the main item reports.

Important columns include:

| Column | Purpose |
| --- | --- |
| `id` | Unique ID for the report |
| `type` | Either `lost` or `found` |
| `title` | Short item title |
| `category` | Item category |
| `location` | Where the item was lost or found |
| `date_occurred` | Date of the incident |
| `description` | Item details |
| `reporter_name` | Name of the person who created the report |
| `reporter_email` | Email used for resolution confirmation |
| `status` | `Open`, `Claim Requested`, or `Resolved` |
| `created_at` | Time the report was created |
| `updated_at` | Time the report was last updated |

### `claims` table

The `claims` table stores claims connected to reports.

| Column | Purpose |
| --- | --- |
| `id` | Unique ID for the claim |
| `report_id` | ID of the related report |
| `claimer_name` | Name of the person making the claim |
| `claimer_email` | Claimant's email |
| `message` | Explanation for the claim |
| `created_at` | Time the claim was created |

The `report_id` column is a foreign key. If a report is deleted, its claims are deleted automatically because the schema uses `ON DELETE CASCADE`.

## How the code works

### Normal form submission

The report form uses a normal HTML `POST` request:

```text
index.php -> add.php -> reports table
```

After saving the report, `add.php` redirects the browser back to `index.php`.

### AJAX claim submission

The claim form uses jQuery to send data without navigating to a new page:

```text
index.php JavaScript -> claim.php -> claims table
                                   -> reports.status
```

The browser displays the server response and reloads the page to show the new status.

### AJAX resolution

The resolution flow is:

```text
Resolve button
    |
    v
Ask for reporter email
    |
    v
POST id, status, email to status.php
    |
    v
Server checks report ID, current status, and email
    |
    v
Update reports.status to Resolved
```

The email comparison is performed in SQL using `LOWER(TRIM(...))`, so these values are treated as equivalent:

```text
person@example.com
 PERSON@example.com
```

## Testing the application

### Basic browser test

After opening the site, test this sequence:

1. Confirm that sample reports appear.
2. Search for `backpack`.
3. Filter by `Lost`.
4. Submit a new report.
5. Click **Claim** on an open report.
6. Confirm that the report changes to `Claim Requested`.
7. Click **Resolve**.
8. Enter an incorrect email and confirm that resolution fails.
9. Enter the correct reporter email and confirm that the status changes to `Resolved` without a full page reload.

### PHP syntax checks

From the project directory, run:

```bash
php -l index.php
php -l add.php
php -l claim.php
php -l status.php
php -l db.php
```

Each file should report `No syntax errors detected`.

### Inspecting the database manually

```bash
mariadb -u lnf_user -p lost_and_found
```

Then run:

```sql
SELECT id, title, status, reporter_email FROM reports;
SELECT id, report_id, claimer_name, claimer_email FROM claims;
EXIT;
```

## Troubleshooting

### “Unable to connect to database” or a blank database error

Check the following:

1. MariaDB is running:

   ```bash
   sudo systemctl status mariadb
   ```

2. The database exists:

   ```bash
   mariadb -u lnf_user -p -e "SHOW DATABASES;"
   ```

3. The values in `db.php` match the database username, password, host, and database name.
4. The PHP MySQL driver is installed:

   ```bash
   php -m | grep -i pdo
   ```

   You should see `PDO` and `pdo_mysql`.

### “Access denied for user”

The MariaDB user may not have been created for the same host used in `db.php`. The default connection uses `127.0.0.1`, so create and grant permissions to `'lnf_user'@'127.0.0.1'` as shown in the installation instructions.

### The browser shows a 404 error

Check that the project was copied to the expected Apache directory:

```bash
ls /var/www/html/lostfound
```

You should see `index.php`, `db.php`, and the other project files.

Also check Apache:

```bash
sudo systemctl status httpd
```

### The page displays PHP source code instead of running it

Apache is serving PHP as plain text instead of processing it. Confirm that PHP and the Apache PHP integration are installed, then restart Apache:

```bash
sudo dnf install php php-mysqlnd
sudo systemctl restart httpd
```

### Claims or resolution do not work

Open the browser developer tools and check the **Network** tab for the request to `claim.php` or `status.php`. Also check the Apache error log:

```bash
sudo tail -f /var/log/httpd/error_log
```

For resolution, confirm that:

- The report status is `Claim Requested`.
- The email is the reporter's email, not the claimant's email.
- The email was entered without a typo.

### The page looks unstyled

Confirm that `style.css` exists in the same directory as `index.php` and that the browser can load:

```text
http://localhost/lostfound/style.css
```

The page also loads jQuery from a CDN. If the computer is offline, the AJAX features will not work unless jQuery is downloaded and served locally.

## Limitations and possible improvements

This is a deliberately minimal project. The following features are not included:

- User registration and login.
- Passwords or sessions.
- Real email ownership verification.
- Administrator moderation.
- File or image upload handling, although the schema has an unused `image_url` column.
- Pagination for large numbers of reports.
- CSRF protection.
- Rate limiting or abuse prevention.
- Detailed server-side validation for every field.
- A separate page for viewing claims.
- Automated tests.

Possible future improvements include:

1. Add login accounts for reporters and administrators.
2. Send a one-time email link for resolution.
3. Add a random secret code during report creation.
4. Add CSRF tokens to all POST requests.
5. Add stronger validation and rate limiting.
6. Add image upload and secure file storage.
7. Add pagination and better accessibility.
8. Return JSON responses from AJAX endpoints instead of plain text.
9. Add automated unit and browser tests.
10. Move database credentials into environment variables instead of storing them directly in `db.php`.

## License

No license has been specified for this project. Add a license file if you plan to publish or redistribute it.
