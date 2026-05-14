# ♻️ Smart Garbage Management System (Smart GMS)

A web-based garbage management platform developed for **Karur City**, Tamil Nadu.
Built as a final year project for **B.Sc Computer Science (2023–2026)**.

---

## 👩‍💻 Developer

| Detail | Info |
|---|---|
| **Name** | BALABARATHI M |
| **Register No** | 1234567880123456 |
| **Degree** | III Year B.Sc Computer Science |
| **College** | CSI Bishop Solomon Doraisawmy College of Arts & Science, Karur |
| **University** | Bharathidasan University |
| **Year** | 2023 – 2026 |

---

## 📌 About the Project

Smart GMS is a full-stack web application that bridges the gap between citizens and
municipal administrators for efficient garbage management in Karur city.

### Problems It Solves
- Citizens had no way to check garbage collection schedules online
- No digital platform to find nearby dustbin locations
- No proper channel to report sanitation issues with evidence
- Administrators managed everything manually

---

## ✅ Features

### 👤 Citizen Features
- Register and login securely
- Search garbage collection schedule by zone and area
- View dustbin locations on an interactive map
- Submit complaints with photo evidence
- Track complaint status and read admin replies

### 🛡️ Admin Features
- Dashboard with statistics overview
- Add / Edit / Delete garbage collection schedules
- Add dustbin locations on interactive Leaflet.js map
- View, respond to, and resolve citizen complaints
- Manage citizen accounts

---

## 🛠️ Technologies Used

| Technology | Purpose |
|---|---|
| **PHP 7.4** | Server-side scripting and business logic |
| **MySQL 5.7** | Database management |
| **Bootstrap 5** | Responsive UI design |
| **Leaflet.js** | Interactive dustbin location map |
| **OpenStreetMap** | Free map tiles for Leaflet |
| **JavaScript** | Dynamic dropdowns and map interactions |
| **XAMPP** | Local server environment (Apache + MySQL) |
| **HTML5 / CSS3** | Page structure and styling |

---

## 🗄️ Database Tables

| Table | Description |
|---|---|
| `users` | Stores citizen and admin accounts |
| `schedules` | Garbage collection schedules by zone and area |
| `dustbins` | Dustbin GPS locations and status |
| `complaints` | Citizen-submitted complaints with photo |
| `complaint_responses` | Admin replies to complaints |

---

## 📁 Project Structure

```
smart_gms/
├── index.php                  → Landing page
├── login.php                  → Login page (Citizen + Admin)
├── register.php               → Citizen registration
├── logout.php                 → Session logout
│
├── includes/
│   ├── db.php                 → Database connection (PDO)
│   ├── header.php             → Shared HTML head section
│   └── footer.php             → Shared footer scripts
│
├── admin/
│   ├── dashboard.php          → Admin dashboard with stats
│   ├── schedule.php           → Add / Edit / Delete schedules
│   ├── dustbins.php           → Manage dustbin locations on map
│   ├── complaints.php         → View and respond to complaints
│   └── users.php              → Manage citizen accounts
│
├── citizen/
│   ├── dashboard.php          → Citizen home dashboard
│   ├── schedule.php           → Search collection schedule
│   ├── complaint.php          → Submit complaint with photo
│   ├── map.php                → View dustbin map
│   └── my_complaints.php      → Track own complaints
│
├── assets/
│   ├── css/
│   │   ├── bootstrap.min.css  → Bootstrap 5 CSS
│   │   └── style.css          → Custom CSS styles
│   └── js/
│       └── bootstrap.bundle.min.js → Bootstrap 5 JS
│
├── uploads/                   → Complaint photo uploads
│
└── database/
    └── smart_gms.sql          → Database export (import this first!)
```

---

## ⚙️ How to Run This Project Locally

### Step 1 — Install XAMPP
Download and install XAMPP from [https://www.apachefriends.org](https://www.apachefriends.org)

### Step 2 — Copy Project Files
Copy the entire `smart_gms` folder into:
```
C:\xampp\htdocs\smart_gms\
```

### Step 3 — Import the Database
1. Start XAMPP → Start **Apache** and **MySQL**
2. Open browser → go to `http://localhost/phpmyadmin`
3. Click **New** → Create database named `smart_gms`
4. Click on `smart_gms` database → click **Import**
5. Choose file → `database/smart_gms.sql` → Click **Go**

### Step 4 — Run the Project
Open browser and go to:
```
http://localhost/smart_gms/
```

---

## 🔐 Login Credentials

| Role | Email | Password |
|---|---|---|
| **Admin** | admin@smartgms.com | admin123 |
| **Citizen** | citizen@gmail.com | citizen123 |

> ⚠️ Change these credentials after setting up for security.

---

## 🗺️ Karur City Zones Covered

| Zone | Areas |
|---|---|
| **North Zone** | Karur Town, K.K. Nagar, Vengamedu |
| **South Zone** | Manmangalam, Thanthonimalai, Jawahar Nagar |
| **East Zone** | Kovilpalayam, Kadavur, Krishnarayapuram |
| **West Zone** | Aravakurichi, Kulithalai, Pallapatti |

---

## 🔒 Security Features

- Passwords stored using **bcrypt hashing** (`password_hash()`)
- SQL injection prevented using **PDO prepared statements**
- Unauthorized access blocked using **PHP session checks**
- Session fixation prevented using **`session_regenerate_id()`**

---

## 📊 Project Statistics

| Stat | Value |
|---|---|
| PHP Files | 17 |
| Lines of Code | 2,686 |
| Database Tables | 5 |
| Test Cases | 29 (all passed) |
| Modules | 6 |

---

## 🚀 Future Enhancements

- IoT smart dustbin sensors for real-time fill level updates
- Mobile application for Android and iOS
- SMS and email notifications for citizens
- Cloud deployment for public access
- Integration with Karur Municipal Corporation database

---

## 📄 License

This project is developed for **academic purposes only**.
Not for commercial use.

---

> *"Smart GMS — Making Karur Cleaner, One Report at a Time."* ♻️
