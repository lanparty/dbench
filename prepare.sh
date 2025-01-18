#!/bin/bash

# อัปเดตแพ็กเกจและติดตั้ง PHP repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# ติดตั้ง PHP 8.2 และส่วนเสริมที่จำเป็น
sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-{bz2,curl,mbstring,intl,dom,xml,sqlite3}

# ติดตั้ง Composer
wget -q -O composer-setup.php https://getcomposer.org/installer
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php

# โคลนโปรเจกต์และติดตั้ง dependencies
#git clone https://github.com/lanparty/dbench.git
#cd dbench/
composer install

# คัดลอกไฟล์ .env และตั้งค่า key
cp .env.example .env
php artisan key:generate

# รันการ migrate database
php artisan migrate --force

# เปิดใช้งาน PHP-FPM
sudo a2enconf php8.2-fpm
sudo systemctl reload apache2
