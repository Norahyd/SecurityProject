# Secure vs Vulnerable User Registration System

## 📌 Project Overview

This project demonstrates the difference between a **secure** and a **vulnerable** user registration and login system using PHP and MySQL. It includes two versions of the application:

- ✅ **Secure Version:** Implements modern security best practices including input validation, hashed passwords (bcrypt), prepared SQL statements, session token cookies, and role-based access control.
- ❌ **Vulnerable Version:** Intentionally exposes common web vulnerabilities like SQL injection, weak password storage (MD5), and insecure authentication methods for learning and testing purposes.

## 🔐 Security Features Demonstrated

- Secure password hashing using `password_hash()` (bcrypt)
- Session token stored as secure HTTP-only cookie
- Role-based access (admin vs. user)
- SQL injection vulnerabilities in the unsafe version
- Cookie-based authentication in the secure version

## 🧪 How to Test Security Features

### 🔓 1. SQL Injection in Vulnerable Register Page

- Go to `notsec/register.php`
- In the **username** field, enter the following:
DROP TABLE users;
- Click **Register**
- ❗ This will trigger the injection and attempt to delete the `users` table.

### 🔐 2. Prevented SQL Injection in Secure Register Page

- Go to `secure/register.php`
- Try the same payload:
DROP TABLE users;
- ✅ The system will reject the input due to prepared statements and input validation.


### 🔓 3. SQL Injection in Vulnerable Login Page
- Go to notsec/login.php
- In the username or password field, enter the following:
' OR '1'='1
- Click Login
- ❗ Because the page is not secure, you will be logged in without providing valid credentials. This demonstrates a classic SQL Injection vulnerability due to lack of prepared statements.

### 🔐 4. Prevented SQL Injection in Secure Login Page
- Go to secure/login.php
- Enter the same input:
' OR '1'='1
- ✅ The system will reject the login attempt because it uses prepared statements and input sanitization to prevent SQL injection.


### 🔐 5. Cookie-Based Authentication Test

- Login using the secure login page.
- Open browser developer tools > Application > Cookies.
- ✅ You should see a cookie named `session_token`.
- Try deleting this cookie and refreshing `dashboard.php`.
- ✅ You will be redirected to `login.php`, confirming proper auth control.

### 🔐 6. Role-Based Access Control

- Login as a regular user:
- Try to access `addbook.php` or delete books in `books.php`.
- ✅ You will be redirected or blocked.
- Login as an admin:
- ✅ You will have full access to add/delete books and moderate reviews.

## ✅ Pages Included

- `register.php` – Secure and vulnerable versions
- `login.php` – Secure login with token cookie
- `dashboard.php` – Protected user landing page
- `books.php` – View books, admin can delete
- `addbook.php` – Admin-only book creation
- `reviews.php` – User-generated reviews with access control
- `logout.php` – Clears cookies and ends session

---

## 🛠️ Requirements

- PHP 7.4+
- MySQL
- XAMPP, MAMP, or any local PHP server
- Enable HTTPS manually for cookie security (optional but recommended)

