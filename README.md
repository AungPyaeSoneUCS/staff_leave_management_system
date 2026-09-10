# Staff Leave Management System

A comprehensive web-based Staff Leave Management System built with Laravel, MySQL, Nginx, and Tailwind CSS. Designed to streamline leave requests, approvals, department management, and administrative workflows for educational institutions.

## Features

- Role-based access control (Staff, Admin, Super Admin)
- Leave request submission and multi-level approval workflow
- Duty exchange requests and management
- Leave types and department configuration
- Responsive user interface with modern styling

---

## Prerequisites

Ensure your local machine or server meets the following requirements:
- **PHP** >= 8.4
- **Composer**
- **Node.js & npm**
- **MySQL** >= 8.0
- **Nginx** (for production server)

---

## Local Installation Guide

<Steps>
  <Step title="Clone the Repository">
    Clone the project from GitHub and navigate into the project directory:
    ```bash
    git clone [https://github.com/AungPyaeSoneUCS/staff_leave_management_system.git](https://github.com/AungPyaeSoneUCS/staff_leave_management_system.git)
    cd staff_leave_management_system
    ```
  </Step>

  <Step title="Install PHP & JavaScript Dependencies">
    Install all required backend and frontend packages:
    ```bash
    composer install
    npm install
    ```
  </Step>

  <Step title="Configure Environment File">
    Copy the example environment configuration file and generate your application key:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    Open the `.env` file and update your database credentials:
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=staff_leave_management_system
    DB_USERNAME=your_db_user
    DB_PASSWORD=your_db_password
    ```
  </Step>

  <Step title="Run Database Migrations and Seeders">
    Execute migrations to create the database schema:
    ```bash
    php artisan migrate --seed
    ```
  </Step>

  <Step title="Build Frontend Assets">
    Compile your frontend assets using Vite:
    ```bash
    npm run build
    ```
  </Step>

  <Step title="Link Storage & Start Local Server">
    Create the storage symlink and start the local development server:
    ```bash
    php artisan storage:link
    php artisan serve
    ```
    *Access your local application at `http://127.0.0.1:8000`.*
  </Step>
</Steps>

---

## Production Server Installation Guide (Ubuntu / Nginx)

<Steps>
  <Step title="Clone & Set Ownership">
    Clone your repository into the web root directory and adjust directory permissions for the web server:
    ```bash
    cd /var/www/html/
    git clone [https://github.com/AungPyaeSoneUCS/staff_leave_management_system.git](https://github.com/AungPyaeSoneUCS/staff_leave_management_system.git) Leave_Manage
    sudo chown -R www-data:www-data /var/www/html/Leave_Manage
    ```
  </Step>

  <Step title="Install Dependencies on Server">
    Switch to your system user to install dependencies, compile frontend assets, and link storage:
    ```bash
    cd /var/www/html/Leave_Manage
    composer install --no-dev --optimize-autoloader
    chmod +x node_modules/.bin/vite
    npm run build
    php artisan storage:link
    ```
  </Step>

  <Step title="Configure Environment & Database">
    Configure your `.env` file for production (`APP_DEBUG=false`, production database credentials), then run migrations:
    ```bash
    php artisan migrate --force
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    ```
  </Step>

  <Step title="Configure Nginx Server Block">
    Create an Nginx configuration file for your domain (e.g., `/etc/nginx/sites-available/leave.ucsh.edu.mm`):
    ```nginx
    server {
        listen 443 ssl http2;
        server_name leave.ucsh.edu.mm;
        
        root /var/www/html/Leave_Manage/public;
        index index.php index.html;

        ssl_certificate /etc/ssl/certs/ucsh_fullchain.crt;
        ssl_certificate_key /etc/ssl/private/ucsh.key;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/run/php/php8.4-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }

        location ~ /\.(?!well-known).* {
            deny all;
        }
    }

    server {
        listen 80;
        server_name leave.ucsh.edu.mm;
        return 301 https://$host$request_uri;
    }
    ```
    Enable the site, test the configuration, and reload Nginx:
    ```bash
    sudo ln -s /etc/nginx/sites-available/leave.ucsh.edu.mm /etc/nginx/sites-enabled/
    sudo nginx -t
    sudo systemctl reload nginx
    ```
  </Step>
</Steps>

---

## Git Workflow: Pull and Push

### Pulling Latest Updates (on Server or Local)
When code updates are pushed to GitHub, pull them down and update dependencies or database schemas:

```bash
# If working as a non-root user, ensure safe directory configuration if prompted:
git config --global --add safe.directory /var/www/html/Leave_Manage

# Pull the latest code
git pull origin main

# Install any new dependencies or run migrations if required
composer install --no-dev --optimize-autoloader
npm run build
php artisan migrate --force
php artisan optimize:clear
