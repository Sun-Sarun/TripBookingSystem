# ✈️ TripBookingSystem

A streamlined solution for managing travel and bookings, built with PHP and MySQL.

---

## 🛠️ System Setup

Follow these steps to get the environment running on your local machine.

### **Step 1: Environment & Installation**
1. Download and install **[XAMPP](https://www.apachefriends.org/download.html)**.
2. Clone this repository into your XAMPP web root directory:
   * **Linux:** `/opt/lampp/htdocs/`
   * **Windows:** `C:\xampp\htdocs\`

### **Step 2: Start Services**
Launch the XAMPP Control Panel and start the **Apache** and **MySQL** modules.

### **Step 3: Database Configuration**
1. Open your browser and go to **[phpMyAdmin](http://localhost/phpmyadmin/)**.
2. Create a new database named: `tripBookingPOS`.

### **Step 4: Import Data**
1. Select the `tripBookingPOS` database in phpMyAdmin.
2. Navigate to the **Import** tab.
3. Choose the SQL file located at: 
   `.../htdocs/Booking-Master/database/data/tripBookingPOS.sql`
4. Click **Go** to execute the import.

### **Step 5: Launch Application**
Access the system by visiting:
👉 **[http://localhost/Booking-Master/home/index.php](http://localhost/Booking-Master/home/index.php)**

---

## 🏗️ Design & References

### **Database Design**
Inspired by the architecture detailed in:
* [How to Develop an Online Event Booking System (PHP & MySQL)](https://dev.to/rakeebmkhan/how-to-develop-an-online-event-booking-system-using-php-mysql-3b4p)

### **Project Inspiration**
* [Trip.com](https://www.trip.com/)
* [Booking.com](https://www.booking.com/)

---

## 🎨 Templates Used

| Component | Template Source |
| :--- | :--- |
| **Landing Page** | [Restaurant – Free Tailwind CSS](https://themewagon.com/themes/restaurant-tailwind/) |
| **Login Form** | [Neumorphism Login Form](https://github.com/puikinsh/login-forms/tree/main/forms/neumorphism) |
| **Admin Dashboard** | [Davidgrzyb Tailwind Admin](https://github.com/davidgrzyb/tailwind-admin-template) |
