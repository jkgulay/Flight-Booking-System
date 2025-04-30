# ✈️ Flight Booking System

A web-based Flight Booking System developed using **Laravel**, **PHP**, and **PostgreSQL**. This system allows users to search for available flights, book tickets, manage bookings, and view travel history. Designed for ease of use with an intuitive interface and robust backend, the project is built and run using **Laragon** for local development.

---

## 🔧 Features

- ✍️ User registration & authentication
- 🧾 Flight search and ticket booking
- 📄 Booking history and ticket management
- 👨‍💼 Admin panel for flight CRUD operations
- 📊 PostgreSQL database integration
- 📬 Email-based booking confirmations (optional)
- 📱 Responsive UI with Bootstrap

---

## 🧰 Tech Stack

- **Backend:** Laravel (PHP 8+)
- **Frontend:** Blade templating engine, Bootstrap 5
- **Database:** PostgreSQL
- **Development Environment:** Laragon
- **Database Management:** PgAdmin 4

---

## 🛠️ Installation & Setup (Using Laragon)

### 1. Clone the Repository
git clone https://github.com/yourusername/flight-booking-system.git
cd flight-booking-system

---

### 2. Set Up .env
cp .env.example .env
Update the following lines in .env for your PostgreSQL configuration:
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=flight_booking_db
DB_USERNAME=your_pgsql_user
DB_PASSWORD=your_pgsql_password

---

### 3. Install Dependencies
composer install

---

### 4. Generate Application Key
php artisan key:generate

---

### 5. Run Migrations
php artisan migrate

---

### 6. Serve the Application
If you're using Laragon:
Open Laragon and start Apache and PostgreSQL
Visit: http://localhost/flight-booking-system/public
Or use:
php artisan serve
Then access via http://127.0.0.1:8000

---

🐘 PostgreSQL with PgAdmin (Optional)
If you prefer a GUI for managing your PostgreSQL data:

Open PgAdmin 4
Connect to your PostgreSQL server
Create a new database named flight_booking_db
Run migrations or import an SQL dump if available

---

📝 License
This project is open-source and available under the MIT License.
