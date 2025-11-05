# 🌱 GreenCoins – Carbon Credit & Environmental Contribution System

GreenCoins is a web-based platform built using **PHP & MySQL** that encourages individuals and organizations to contribute towards environmental sustainability. Users earn **Green Coins** by planting trees, while factories can track and offset their pollution through green credits. An admin manages verification, transactions, and platform data.

---

## 🚀 Features

### 👤 User (Individual)
- Register & Login with **Email OTP verification** (PHPMailer)
- Upload images as proof of tree planting
- Earn **Green Coins** for each verified plantation
- Sell Green Coins in exchange for monetary value
- View earnings, planting history & credit balance
- Dashboard with leaderboard style progress

### 🏭 Factory
- Register factory details and pollution output
- Receive **Green Credits** for supporting sustainability activities
- Dashboard for monitoring pollution & contribution score

### 🔑 Admin
- Approve or Reject user plantation proofs
- Manage factories and user accounts
- Monitor **pollution data**, factory contributions & system activity
- Approve Green Coin **sell** requests
- Generates transaction & verification logs
- Full control panel dashboard

---

## 🗄 Database Structure (Key Tables)
| Table Name | Purpose |
|-----------|---------|
| `users` | Stores user login & profile info |
| `factories` | Stores registered factory data |
| `planting_proofs` | Stores uploaded tree plantation proofs |
| `verifications` | Tracks admin verification actions |
| `green_credits` / `user_credits` | Stores credit/coin balances |
| `pollution_data` | Records pollution levels of factories |
| `credit_transactions` | Contains buy/sell credit transaction logs |

---

## 🛠 Tech Stack
| Component | Technology |
|----------|------------|
| Frontend | HTML, CSS, Tailwind CSS, JavaScript |
| Backend | PHP (Core PHP) |
| Database | MySQL |
| Email Service | PHPMailer (SMTP) |
| Other | QR Code Utility (Optional) |

---

## 📦 Installation & Setup

1. Extract the project into your web server directory:

2. Start **Apache** and **MySQL** in XAMPP.

3. Import the database:
- Open **phpMyAdmin**
- Create a database (example: `greencoins`)
- Import the provided `.sql` file located in the project folder.

4. Update database credentials in:

5. Open the system in browser:

---

## 🧑‍💻 Project Roles Credentials (Example)
| Role | Email | Password |
|------|-------|----------|
| Admin | admin@example.com | admin123 |
| User | user@example.com | user123 |
| Factory | factory@example.com | factory123 |

> *Update based on real data from your database.*

---

## 📜 License
This project is created for educational/demo purposes.  
Feel free to modify and improve it.

---

## ❤️ Acknowledgements
- Tailwind CSS
- PHPMailer
- MySQL / phpMyAdmin
