# Headless Webshop API

This is a **personal learning project** focused on building the backend architecture for a modern e-commerce platform. By following a "headless" approach, this API serves as the centralized engine for product data, cart management, and order processing, allowing any frontend to connect to it.

---

## 🏗 Project Goal
The objective of this project is to understand how to manage complex relational data (products, categories, users) and provide a secure, dead simple, and scalable interface for client-side applications.

## 🚀 Key Features
- **Product Catalog**: Read operations for safely fetching products from a database.
- **Dynamic Filtering**: Fetch products by category, price range, or availability.
- **Cart Logic**: State management for adding/removing items and calculating totals. Both for logged in and not logged in users.
- **Authentication**: Secure endpoints for user profiles and order history.

## 🛠 Tech Stack
* **Runtime/Language:** PHP 8.4
* **Framework:** Symfony 8
* **Database:** MySQL 9.2
* **Auth:** JWT

---

## Getting Started

1. **Clone the repo:**
   ```bash
   git clone https://github.com/basileaelterman/Headless-Webshop-API.git
   ```
   
2. **Install dependencies:**
   ```bash
   composer install
   ```
   
3. **Configure environment:**
   Create or configure an <code>.env</code> file to connect it to your database

4. **Run the server:**
   ```bash
   symfony serve
   ```

---

**Note:** Again, this is a personal project for educational purposes. I am not accepting code contributions at this time, but I am always open to advice and feedback!
