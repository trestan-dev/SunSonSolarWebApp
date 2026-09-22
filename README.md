# SunSonSolarWebApp
A web app to help SunSonSolar to efficiently process their current processes.

· ‎Katherine sinagaraw (kat) Founder of SunSon Solar, together with his father Santio Sinagaraw (Sonny) 

· ‎Very first office in Pasig City 

· ‎Create a website / system where customer can easily see our products. 

· ‎We sell panels, inverters, batteries, racking, mounting and wires

· ‎Services such as consultations, designing, permitting, installation, maintenance, repair, and monitoring 

· ‎Not very tech-savvy

· ‎When we give the website she wants an account right away

· ‎Username (Kittykat16) 

· ‎Password (K@tSunShine16)

· ‎Add their IT head (Sol. Solis) one of the admins. Dont know what username he wants yet

· ‎Just Kat and sol as the first registered users

· ‎Other employees like technician and dispathcher Apollo Santos will registered on their own

· ‎Techinician suggestions; they love to time in directly at the site instead na pumunta pa sa office

· ‎Need super user friendly 

· ‎Just one button that can records their name, time, coordinates

· ‎Sol username (admin)

· ‎Sol password (admin123)

· ‎Sol wants the system to collect information for customers; First name, last name, middle name, birthdate, gender, email, phone number, address, username, and password.

· ‎For employees; The same details as customers + their department 

· ‎User must have primary key

## XAMPP setup

The current login and registration flow uses PHP with MySQL/MariaDB instead of Supabase.

1. Copy this project folder into XAMPP's `htdocs` directory, for example `C:\xampp\htdocs\SunSonSolarWebApp`.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin`.
4. Use the **Import** tab to run the root `database.sql` file.
5. Open `http://localhost/SunSonSolarWebApp/F4/login.html`.

The PHP database connection is in `F4/db.php`. It uses the default XAMPP MySQL settings: host `127.0.0.1`, database `sunsonsolar`, username `root`, and an empty password. Change those values if your MySQL installation uses a password.

Customer registration is available at `F4/registration.html`. Employee registration is available at `F4/employee-registration.html` and requires the `SUNSON_EMPLOYEE_INVITE_CODE` environment variable to be configured for Apache/PHP. Do not place the invite code in the public form or commit it to the repository. Employee registrations create `role = employee`; public registration cannot create employee or admin accounts.

To configure the employee invite code in XAMPP, add this line to Apache's `httpd.conf`, replacing the example value with a private code:

```apache
SetEnv SUNSON_EMPLOYEE_INVITE_CODE "replace-with-a-private-code"
```

Restart Apache after changing the file. Give the code only to employees such as Apollo; it does not create administrator accounts.

Passwords are never stored as plain text. Registration stores a `password_hash`, and login verifies it with PHP's `password_verify()` function.

To create an administrator, register the account normally, then run this in phpMyAdmin's SQL tab:

```sql
update sunsonsolar.users
set role = 'admin'
where email = 'admin@example.com';
```
