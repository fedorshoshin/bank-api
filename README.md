# 💰 Laravel Balance Management API

This project provides a simple **API for managing user balances**, allowing for **transfers**, **deposits**, **withdrawals**, and **balance checks**.  
It is built with **Laravel** and uses **[Knuckleswtf/Scribe](https://scribe.knuckles.wtf)** for automatic API documentation generation.

---

## 🚀 Features

- 💸 Transfer funds between users  
- ➕ Deposit money to a user’s account  
- ➖ Withdraw money from a user’s account  
- 📊 Check user balance  
- 🧩 Safe transactions using database transactions  
- 🧪 Automated tests for key logic  
- 🧾 Auto-generated API docs

---

## ⚙️ Requirements

- PHP >= 8.1  
- Composer  
- Laravel >= 10.x  
- SQLite or MySQL  
- Node.js (optional, for frontend assets)

---

## 📦 Installation

```bash
# Clone the repository
https://github.com/fedorshoshin/bank-api.git
cd bank-api

# Install dependencies
composer install

# Copy environment file and generate key
cp .env.example .env
php artisan key:generate

# Deploy app on Docker
docker compose up -d --build
