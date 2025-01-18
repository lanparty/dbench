#!/bin/bash

# อัปเดตแพ็กเกจและติดตั้ง PHP repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# ติดตั้ง PHP 8.2 และส่วนเสริมที่จำเป็น
sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-{zip-bz2,curl,mbstring,intl,dom,xml,sqlite3,mysql}

# ติดตั้ง MariaDB และสร้างฐานข้อมูล laravel
sudo apt install -y mariadb-server
sudo systemctl start mariadb
sudo systemctl enable mariadb

# Create database and user for Laravel without password
sudo mysql -e "CREATE DATABASE laravel;"
sudo mysql -e "CREATE USER 'laravel'@'localhost' IDENTIFIED BY '';"
sudo mysql -e "GRANT ALL PRIVILEGES ON laravel.* TO 'laravel'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

# ติดตั้ง Composer
wget -q -O composer-setup.php https://getcomposer.org/installer
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php

# โคลนโปรเจกต์และติดตั้ง dependencies
#git clone https://github.com/lanparty/dbench.git
#cd dbench/
composer install

# คัดลอกไฟล์ .env และตั้งค่า key
cp .env.example .env
php artisan key:generate

# ตั้งค่า MySQL ใน .env
sed -i '' 's/DB_CONNECTION=.*/DB_CONNECTION=mysql/' .env
sed -i '' 's/DB_HOST=.*/DB_HOST=127.0.0.1/' .env
sed -i '' 's/DB_PORT=.*/DB_PORT=3306/' .env
sed -i '' 's/DB_DATABASE=.*/DB_DATABASE=laravel/' .env
sed -i '' 's/DB_USERNAME=.*/DB_USERNAME=laravel/' .env
sed -i '' 's/DB_PASSWORD=.*/DB_PASSWORD=/' .env

# รันการ migrate database
php artisan migrate --force

# เปิดใช้งาน PHP-FPM
sudo a2enconf php8.2-fpm
sudo systemctl reload apache2
