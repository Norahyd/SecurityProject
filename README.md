# Secure vs Vulnerable User Registration System

##  Project Overview

This project demonstrates the difference between a **secure** and a **vulnerable** user registration and login system using PHP and MySQL. It includes two versions of the application:

-  **Secure Version:** Implements modern security best practices including input validation, hashed passwords (bcrypt), prepared SQL statements, session token cookies, and role-based access control.
-  **Vulnerable Version:** Intentionally exposes common web vulnerabilities like SQL injection, weak password storage (MD5), XSS, and insecure authentication methods for learning and testing purposes.

##  How to Run the Application
Use these steps for both the secure and insecure versions:

1. Clone this repository

2. Visit [https://www.infinityfree.com/], register and verify your account

3. Create a hosting account and choose a subdomain (e.g., `yourproject.infinityfreeapp.com`)

4. Open the **Control Panel (cPanel)** for your hosting account

5. Go to **MySQL Databases**, create a new database, and import `book_reviews.sql`

6. Use the **Online File Manager** to upload the desired project folder (`secure/` or `notsec/`) and the `/uploads` folder

7. Both versions are now deployed:
 Insecure Version (No SSL):
   http://notsecbookreview.infinityfreeapp.com/notsec/login.php?i=1
   No SSL certificate issued — this helps demonstrate the lack of security.

8. the secure version, go to the "Free SSL Certificate" tab in InfinityFree.

9. Generate a free SSL certificate, verify domain ownership, and install the certificate.

10. After installation, enable HTTPS in your domain settings via cPanel.
  The secure version is now available at:
    https://bookreview.infinityfreeapp.com/sec/login.php

###  Notes:
- Add books manually through the UI or database (or use SQL insert)
users table

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
- All users are assigned the `user` role by default — promote a user by updating their role in the database:
  ```sql
  UPDATE users SET role = 'admin' WHERE username = 'your_username';


books tabele

CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    image VARCHAR(255)
);

reviews table

CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);



##  Security Features Demonstrated

- Secure password hashing using `password_hash()` (bcrypt)
- Session token stored as secure HTTP-only cookie
- Role-based access (admin vs. user)
- SQL injection vulnerabilities in the unsafe version
- Cross-Site Scripting (XSS) in the vulnerable version
- Cookie-based authentication in the secure version

##  How to Test Security Features

###  1. SQL Injection in Vulnerable Register Page

- Go to `notsec/register.php`
- In the **username** field, enter the following:
DROP TABLE users;
- Click **Register**
-  This will trigger the injection and attempt to delete the `users` table.

###  2. Prevented SQL Injection in Secure Register Page

- Go to `secure/register.php`
- Try the same payload:
DROP TABLE users;
-  The system will reject the input due to prepared statements and input validation.


###  3. SQL Injection in Vulnerable Login Page
- Go to notsec/login.php
- In the username or password field, enter the following:
' OR '1'='1
- Click Login
-  Because the page is not secure, you will be logged in without providing valid credentials. This demonstrates a classic SQL Injection vulnerability due to lack of prepared statements.

###  4. Prevented SQL Injection in Secure Login Page
- Go to secure/login.php
- Enter the same input:
' OR '1'='1
-  The system will reject the login attempt because it uses prepared statements and input sanitization to prevent SQL injection.


###  5. Cookie-Based Authentication Test

- Login using the secure login page.
- Open browser developer tools > Application > Cookies.
-  You should see a cookie named `session_token`.
- Try deleting this cookie and refreshing `dashboard.php`.
-  You will be redirected to `login.php`, confirming proper auth control.

###  6. Role-Based Access Control

- Login as a regular user:
- Try to access `addbook.php` or delete books in `books.php`.
- You will be redirected or blocked.
- Login as an admin:
- You will have full access to add/delete books and moderate reviews.

###  7. XSS in Vulnerable Reviews Page
- Go to `notsec/reviews.php`
- In the review content box, submit:
```html
<script>alert('XSS')</script>
- The script will execute in the browser, showing an alert — demonstrating a stored XSS vulnerability due to missing output sanitization.

###  8. Prevented XSS in Secure Reviews Page
- Go to secure/reviews.php
- Submit the same payload:
- <script>alert('XSS')</script>
-  The script will be safely displayed as text, not executed, because of the use of htmlspecialchars().

##  Pages Included

- `register.php` – Secure and vulnerable versions
- `login.php` – Secure login with token cookie
- `dashboard.php` – Protected user landing page
- `books.php` – View books, admin can delete
- `addbook.php` – Admin-only book creation
- `reviews.php` – User-generated reviews with access control
- `logout.php` – Clears cookies and ends session

---

##  Requirements

- PHP 7.4+
- MySQL
- XAMPP, MAMP, or any local PHP server
- Enable HTTPS manually for cookie security (optional but recommended)

