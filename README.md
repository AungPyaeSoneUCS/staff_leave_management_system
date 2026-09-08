# Leave Management System

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
</p>

<p align="center">
  <strong>Staff Leave Management System</strong>
</p>

<p align="center">
  A web-based leave management system developed to streamline staff leave requests, approvals, leave balances, and administrative management.
</p>

<p align="center">
  <a href="https://leave.ucsh.edu.mm">Live System</a> •
  <a href="https://github.com/AungPyaeSoneUCS/Leave_Manage">GitHub Repository</a>
</p>

---

## About the Project

The **Leave Management System** is a web-based application designed to digitize and simplify the process of managing staff leave within an organization.

The system provides a centralized platform where employees can submit leave applications, monitor their leave status and balances, while authorized administrators can review applications, manage leave types, maintain staff information, and oversee the overall leave management process.

The system is available online at:

**https://leave.ucsh.edu.mm**

Source code:

**https://github.com/AungPyaeSoneUCS/Leave_Manage**

---

## Objectives

The main objectives of the Leave Management System are to:

* Digitize the staff leave application process.
* Reduce manual paperwork and administrative workload.
* Provide a centralized leave management platform.
* Allow staff to submit leave applications online.
* Allow authorized personnel to review and approve or reject leave requests.
* Automatically maintain leave balances.
* Provide clear leave history and application status.
* Manage different types of staff leave.
* Improve transparency and efficiency in leave administration.
* Provide a secure and user-friendly web application.

---

## Key Features

### Staff Management

* Staff account management
* Staff profile information
* Staff status management
* User authentication and authorization
* Active/inactive user management

### Leave Management

* Submit leave applications
* Select leave type
* Specify leave dates
* Provide leave reasons
* View submitted applications
* View leave application history
* Track application status
* View available leave balances

### Leave Approval

Authorized users can:

* Review leave applications
* Approve leave requests
* Reject leave requests
* Review application details
* Monitor pending leave applications

### Leave Types

Administrators can manage different leave categories, including:

* Annual Leave
* Casual Leave
* Medical Leave
* Maternity Leave
* Other organization-specific leave types

Leave types can be configured according to the organization's requirements.

### Leave Balance Management

The system can maintain leave balances for staff members and provide information about:

* Allocated leave
* Used leave
* Remaining leave
* Leave history

### Administrative Management

Administrators can manage:

* Staff accounts
* User roles
* Leave types
* Leave applications
* Leave balances
* System settings

### Authentication

The system includes authentication functionality for secure access to the application.

Depending on the user's role, different features and management functions are available.

---

## User Roles

The system supports role-based access to ensure that users can access only the functions appropriate to their responsibilities.

### Staff

Staff members can:

* Log in to the system.
* View their profile.
* View leave balances.
* Submit leave applications.
* View leave application history.
* Monitor application status.

### Administrator

Administrators can:

* Manage staff accounts.
* Manage leave types.
* Review leave applications.
* Approve or reject applications.
* Monitor leave information.
* Manage system data.

### Super Administrator

The Super Administrator provides higher-level system administration functions, including management of administrative access and system-level configuration.

---

## Technology Stack

The project is built using modern web development technologies.

### Backend

* **PHP 8.4+**
* **Laravel**
* **Laravel Eloquent ORM**
* **Laravel Blade**
* **MySQL**

### Frontend

* **HTML5**
* **CSS3**
* **JavaScript**
* **Tailwind CSS**
* **Vite**

### Development Tools

* **Composer**
* **Node.js**
* **npm**
* **Git**
* **GitHub**

---

## System Requirements

Before installing the project, make sure your development environment includes:

* PHP 8.4 or compatible PHP version
* Composer
* Node.js
* npm
* MySQL
* Git
* A web server or Laravel development environment

You can verify your installed versions with:

```bash
php -v
composer -V
node -v
npm -v
git --version
```

---

## Installation

### 1. Clone the Repository

Clone the project from GitHub:

```bash
git clone https://github.com/AungPyaeSoneUCS/Leave_Manage.git
```

Enter the project directory:

```bash
cd Leave_Manage
```

---

### 2. Install PHP Dependencies

Install the Laravel dependencies:

```bash
composer install
```

---

### 3. Create the Environment File

Copy the example environment file:

```bash
cp .env.example .env
```

On Windows Git Bash, the same command can be used:

```bash
cp .env.example .env
```

---

### 4. Generate the Application Key

Run:

```bash
php artisan key:generate
```

---

### 5. Configure the Database

Open the `.env` file and configure the database connection.

Example:

```env
APP_NAME="Leave Management System"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://leave_manage.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=leave_management
DB_USERNAME=root
DB_PASSWORD=
```

Update the database name, username, and password according to your local MySQL configuration.

---

### 6. Create the Database

Create a MySQL database, for example:

```text
leave_management
```

Then run the Laravel migrations:

```bash
php artisan migrate
```

If the project contains database seeders, run:

```bash
php artisan db:seed
```

or:

```bash
php artisan migrate --seed
```

---

### 7. Install Frontend Dependencies

Install Node.js dependencies:

```bash
npm install
```

---

### 8. Build Frontend Assets

For production:

```bash
npm run build
```

For development:

```bash
npm run dev
```

> The production build generates the Vite assets required by Laravel.

---

### 9. Storage Link

If the application uses Laravel's public storage system, run:

```bash
php artisan storage:link
```

---

### 10. Start the Application

For local Laravel development:

```bash
php artisan serve
```

The application will normally be available at:

```text
http://127.0.0.1:8000
```

If using Laravel Herd or another local development environment, configure the project domain accordingly.

---

## Production Deployment

The production system is deployed at:

**https://leave.ucsh.edu.mm**

For a production deployment, configure the server with:

* PHP
* MySQL
* Composer
* Node.js/npm
* Web server such as Apache or Nginx
* SSL/TLS certificate
* Proper Laravel environment configuration

The production `.env` should use appropriate settings, for example:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://leave.ucsh.edu.mm
```

After deploying the application, install dependencies:

```bash
composer install --no-dev --optimize-autoloader
```

Build frontend assets:

```bash
npm install
npm run build
```

Clear and rebuild Laravel caches:

```bash
php artisan optimize
```

---

## Environment Configuration

Do not commit sensitive environment information to GitHub.

The `.env` file should remain local/server-side and should not contain publicly exposed credentials.

Important configuration values include:

```env
APP_NAME=
APP_ENV=
APP_KEY=
APP_DEBUG=
APP_URL=

DB_CONNECTION=
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

---

## Vite and Frontend Assets

The project uses Vite to compile CSS and JavaScript assets.

Development:

```bash
npm run dev
```

Production:

```bash
npm run build
```

Laravel loads the compiled assets through the Vite directive:

```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

If Laravel reports:

```text
Vite manifest not found
```

run:

```bash
npm run build
```

and verify that:

```text
public/build/manifest.json
```

has been generated.

---

## Project Structure

The main Laravel project follows the standard Laravel application structure.

```text
Leave_Manage/
│
├── app/
│   ├── Http/
│   ├── Models/
│   └── ...
│
├── bootstrap/
│
├── config/
│
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
│
├── public/
│   ├── build/
│   ├── images/
│   └── ...
│
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│
├── routes/
│   ├── web.php
│   └── ...
│
├── storage/
│
├── tests/
│
├── .env.example
├── composer.json
├── package.json
├── vite.config.js
└── README.md
```

---

## Documentation

Additional project documentation and materials are maintained in the project documentation directory.

These materials may include:

* Installation Guide
* User Guide
* Presentation PowerPoint
* Intern Report Book
* Intern Project Book
* Abstract Paper
* Project Coding Documentation
* Poster
* Leaflet / လက်ကမ်းစာစောင်

---

## Live Application

The Leave Management System is available at:

**https://leave.ucsh.edu.mm**

---

## Source Code

The source code is maintained on GitHub:

**https://github.com/AungPyaeSoneUCS/Leave_Manage**

---

## Development Workflow

After making changes to the project:

```bash
git status
```

Add the required files:

```bash
git add .
```

Commit the changes:

```bash
git commit -m "Describe your changes"
```

Push to GitHub:

```bash
git push origin main
```

Check the configured remote with:

```bash
git remote -v
```

The expected remote is:

```text
origin  https://github.com/AungPyaeSoneUCS/Leave_Manage.git
```

---

## Security

Security is an important part of the system.

When deploying the application:

* Do not expose `.env`.
* Do not commit database passwords.
* Do not commit API keys or other credentials.
* Set `APP_DEBUG=false` in production.
* Use HTTPS for the production application.
* Use strong administrator passwords.
* Keep PHP, Laravel, Node.js, and dependencies updated.
* Restrict database access to authorized users and applications.

If a security vulnerability is discovered, please report it to the project administrator rather than publicly exposing sensitive details.

---

## License

This project is developed as a **Leave Management System** for organizational and educational purposes.

The Laravel framework used by this project is open-sourced software licensed under the [MIT License](https://opensource.org/licenses/MIT).

---

## Acknowledgements

This project is developed using the Laravel framework and related open-source technologies.

Special thanks to the developers and open-source communities behind:

* Laravel
* PHP
* Tailwind CSS
* Vite
* MySQL
* Node.js
* Git
* GitHub

---

## Project Information

| Item                  | Details                                         |
| --------------------- | ----------------------------------------------- |
| **Project Name**      | Leave Management System                         |
| **Application URL**   | https://leave.ucsh.edu.mm                       |
| **GitHub Repository** | https://github.com/AungPyaeSoneUCS/Leave_Manage |
| **Framework**         | Laravel                                         |
| **Backend**           | PHP                                             |
| **Database**          | MySQL                                           |
| **Frontend**          | Blade, Tailwind CSS, JavaScript                 |
| **Build Tool**        | Vite                                            |
| **Version Control**   | Git / GitHub                                    |

---

<p align="center">
  <strong>Leave Management System</strong>
</p>

<p align="center">
  Developed to make staff leave management simpler, faster, and more efficient.
</p>
