
# 🚗 ProDrivers

**ProDrivers** is a professional driver booking and management platform built with **PHP** and **MySQL**.  
It connects verified **drivers** with **employers** looking for reliable and experienced personnel, enabling a seamless process from **registration** to **booking**, **payment**, and **administration** — all in one place.

---

## 🌟 Key Features

### 👨‍✈️ Driver Module
- Driver registration with photo and document upload  
- Profile management (personal, driving, banking, and residential info)  
- Secure login and dashboard  
- View and update bookings  

### 👩‍💼 Employer (User) Module
- Create an account and log in  
- Book a driver instantly for trips, events, or private engagements  
- Instant Paystack payment integration after booking  
- View booking history and receipts  

### 🧑‍💻 Admin Module
- Dashboard with key statistics  
- Manage drivers, employers, and bookings  
- Approve or suspend drivers  
- Payment management and reports  

---

## 💳 Payment Integration
- **Paystack API** is used for secure, real-time payments.  
- After a booking, users are redirected to Paystack for confirmation before finalizing the order.  

---

## 🛠️ Tech Stack

| Category | Technology |
|-----------|-------------|
| Frontend | HTML5, CSS3, Bootstrap, JavaScript |
| Backend | PHP (Procedural) |
| Database | MySQL |
| Payment | Paystack API |
| Server | XAMPP / Localhost or Cloud Hosting |

---

## 🧩 Folder Structure

```
📁 prodrivers/
│
├── 📁 admin/
│   ├── 📄 dashboard.php
│   ├── 📄 manage_drivers.php
│   └── 📄 manage_bookings.php
│
├── 📁 driver/
│   ├── 📄 register.php
│   ├── 📄 login.php
│   ├── 📄 dashboard.php
│   └── 📄 edit_profile.php
│
├── 📁 user/
│   ├── 📄 book_driver.php
│   ├── 📄 paystack_payment.php
│   └── 📄 bookings.php
│
├── 📁 includes/
│   ├── 📄 db_connect.php
│   ├── 📄 auth.php
│   └── 📄 header.php
│
├── 📁 assets/
│   ├── 📁 css/
│   ├── 📁 js/
│   └── 📁 images/
│
└── 📄 index.php
```
## ⚙️ Installation & Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/yourusername/prodrivers.git

2. Move the project to your local server directory (XAMPP htdocs).


3. Create the database:

  Open phpMyAdmin
  Create a new database named prodrivers_db
  Import the prodrivers_db.sql file (if included)



4. Update database connection:

Edit /includes/db_connect.php with your DB c## ⚙️ Installation & Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/yourusername/prodrivers.git

2. Move the project to your local server directory (XAMPP htdocs).


3. Create the database:

  Open phpMyAdmin
  Create a new database named prodrivers_db
  Import the prodrivers_db.sql file (if included)



4. Update database connection:

  Edit /includes/db_connect.php with your DB                        credentials:
  $conn = mysqli_connect('localhost', 'root', '',           'prodrivers_db');


5. Start your local server and visit:
   http://localhost/prodrivers

