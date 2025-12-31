<p align="center">

<p align="center">
  <a href="https://shirooni.infinityfreeapp.com/">
    <img src="assets/banner.png" alt="StudyShare Banner" width="100%" style="border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);" />
  </a>
</p>

<div align="center">

# 📂 ShareStudy
### **The Ultimate Student Resource Hub & Real-Time Collaboration Ecosystem**

[![Status](https://img.shields.io/website?url=https%3A%2F%2Fshirooni.infinityfreeapp.com%2F&label=System%20Status&style=for-the-badge&logo=instatus&logoColor=white&color=2ea44f)](https://shirooni.infinityfreeapp.com/)
[![Maintenance](https://img.shields.io/badge/Maintained%3F-Yes-blue?style=for-the-badge&logo=github)](https://github.com/0902cs231028-sys/StudyShare/graphs/commit-activity)

</div>

---

<p align="center">
  <a href="https://shirooni.infinityfreeapp.com/">
    <img src="https://img.shields.io/badge/Live_Demo-Launch_Now-007bff?style=for-the-badge&logo=rocket&logoColor=white" alt="Live Demo" />
  </a>
  <a href="./admin/dashboard.php">
    <img src="https://img.shields.io/badge/Admin-God_Mode-crimson?style=for-the-badge&logo=fortinet&logoColor=white" alt="Admin Panel" />
  </a>
  <a href="./includes/db_connect.php">
    <img src="https://img.shields.io/badge/PHP-PDO_Secure-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP" />
  </a>
  <a href="./db.sql">
    <img src="https://img.shields.io/badge/Database-Schema_Ready-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="Download SQL" />
  </a>
  <a href="./LICENSE">
    <img src="https://img.shields.io/badge/License-MIT-gold?style=for-the-badge&logo=open-source-initiative&logoColor=white" alt="License MIT" />
  </a>
</p>

---

## 🌟 Introduction

**StudyShare Supreme** is a next-generation academic platform built for seamless learning, file sharing, and peer interaction. It empowers students to upload, manage, and access shared study materials with **drag-and-drop support**, **real-time chat**, and an elegant **Glassmorphism UI** – all running in a secure, responsive ecosystem.

---

## 🧭 Table of Contents

- 🚀 [Features](#-features)
- 🧩 [Tech Stack](#-tech-stack)
- 🌐 [Live Demo](#-live-demo)
- ⚙️ [Getting Started](#-getting-started)
- 📁 [Project Structure](#-project-structure)
- 🧠 [Core Engines](#-core-engines)
- 📜 [License](#-license)

---

## 🚀 Features

### 🧮 Admin Command Center ("God Mode")
- File: [`admin/dashboard.php`](./admin/dashboard.php)
- Manage the entire StudyShare system with **ban controls**, **force-delete capabilities**, and a **Gold Pulsing Avatar** that marks administrative authority.
- The "God Mode" dashboard allows instant moderation and global message broadcasts.

### 📁 Intelligent File Ecosystem
- File: [`upload_file.php`](./upload_file.php)
- Provides **drag & drop uploads**, **live JSON feedback**, **secure file renaming**, and **download counters** to track resource engagement.
- Powered by robust MIME validation for maximum safety.

### 💬 Real-Time Global Chat
- File: [`message.php`](./message.php)
- Facilitates **instant, no-reload communication** using AJAX-based polling.
- Messages from Admins feature glowing gold or gradient styles for clear visual distinction.

### 💎 UI/UX Excellence
- File: [`css/style.css`](./css/style.css)
- Implements **Glassmorphism design**, **translucent containers**, and a user-toggleable **Light/Dark mode**.
- Ensures an aesthetic, distraction-free experience for collaborative sessions.

---

## 🧩 Tech Stack

| Layer | Technology | Description |
|-------|-------------|-------------|
| **Backend** | PHP 8.2+ | PDO-based, secure prepared statements. |
| **Database** | MySQL | Atomic transactions with cascade deletion. |
| **Frontend** | Bootstrap 5, FontAwesome 6, Vanilla JS | Responsive layout with async AJAX/Fetch requests. |

---

## 🌐 Live Demo

👉 **[Visit StudyShare Supreme Live](https://shirooni.infinityfreeapp.com/)**

---

## ⚙️ Getting Started

### Step 1: Clone the Repository
git clone https://github.com/0902cs231028-sys/StudyShare.git

### Step 2: Database Setup
- Create a database named **`studyshare_db`**
- Import the schema from [`db.sql`](./db.sql)  
  <a href="./db.sql"><img src="https://img.shields.io/badge/Database-Schema%20Ready-blue?logo=mysql&style=for-the-badge" /></a>

**Database Credentials:**
- Host: `localhost`
- Username: `root`
- Password: *(leave empty)*

### Step 3: Launch Locally
- Move the project folder into your `htdocs` directory.
- Open your browser and go to:  
  [`http://localhost/StudyShare/index.php`](./index.php)

---

## 📂 Project Structure

```text
StudyShare/
├── admin/                  # 🛡️ THE COMMAND CENTER
│   └── dashboard.php       # Admin control panel (User bans, File deletion)
├── assets/                 # 🎨 Branding
│   └── banner.png          # Project Banner
├── css/                    # 💅 Styling
│   └── style.css           # Glassmorphism & Theme Logic
├── includes/               # 🔌 Configuration
│   └── db_connect.php      # Database Connection
├── uploads/                # ☁️ Storage
│   └── avatars/            # User profile pictures
├── message.php             # ⚡ JSON API for Global Chat
├── upload_file.php         # ⚡ JSON API for File Uploads
├── dashboard.php           # 🏠 Main Student Dashboard
├── db.sql                  # 🗄️ Database Structure (Import this!)
├── index.php               # 🚪 Landing Page
├── profile.php.            # User personal customization hub
└── LICENSE                 # ⚖️ Legal
```
---

## 🔧 Core Engines

### The Chat Engine (`chat_backend.php`)
This file acts as a **REST-like API** for the platform.
- **POST Requests:** Handles secure message insertion into the database.
- **GET Requests:** Fetches the last 50 messages in **JSON format**, allowing the frontend to update the chat box asynchronously without refreshing the page.
- **Security:** Checks user session and role (Student vs. Admin) before allowing data access.

### The Upload Engine (`upload_file.php`)
A robust file handling system designed for shared hosting environments.
- **Validation:** Strictly checks MIME types (PDF, DOCX, IMG) and enforces a **10MB limit**.
- **Security:** Uses `uniqid()` to rename files on the server (preventing overwrites and directory traversal attacks) while storing the original name in the database for display.

---

## 📜 History & Legal

### 🔄 Project History
See how StudyShare has evolved over time.
<br>
[![Changelog](https://img.shields.io/badge/View_Changelog-History-orange?style=for-the-badge&logo=clock&logoColor=white)](./CHANGELOG.md)

### ⚖️ License
This project is open-source and available under the **MIT License**.
<br>
[![License](https://img.shields.io/badge/Read_License-MIT-green?style=for-the-badge&logo=open-source-initiative&logoColor=white)](./LICENSE)

<br>
<p align="center">
  Made with <strong>Supreme Logic</strong> & ❤️ by <strong>Shirooni23</strong>
</p>
