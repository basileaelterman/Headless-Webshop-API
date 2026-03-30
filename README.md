# Headless Webshop API

**Important:** This is a personal project in order to learn how APIs work and to better understand security. I chose to make this with Symfony 8 (PHP) since it's very robust overall and a solid choice for large, complex APIs.

---

## Key Features
- **Product Catalog**: Read operations for safely fetching products from a database.
- **Dynamic Filtering**: Fetch products by category, price range, name, slug.
- **Cart Logic**: State management for performing CRUD operations on items and calculating totals. Both for logged in and not logged in users.
- **Authentication**: Secure endpoints for user profiles.

## Tech Stack I Used
* **Language:** PHP (8.4)
* **Framework:** Symfony (8)
* **Database:** MySQL (9.2)
* **Authentication:** JWT

---

If you're curious to check, stress test or even tweak some things, feel free to clone this project:

1. **Clone the repo:**
   ```bash
   git clone https://github.com/basileaelterman/Headless-Webshop-API.git
   ```
   
2. **Install dependencies:**
   ```bash
   composer install
   ```
   
3. **Configure environment:**
   Configure an <code>.env</code> file to connect it to your database. Use <code>.env.example</code> as a starting point.

4. **Run the server:**
   ```bash
   symfony serve
   ```

After you've done all this, you can write tests to push the API to it's absolute limits, fix some mistakes I made (please let me know if there are any!) or just continue building from where I left off.

---

## [LICENSE](LICENSE)
This project is available under the [MIT-LICENSE](LICENSE). Too unbothered to fully explain what you can or cannot do with projects under this license, so feel free to read the license. 😉
