#!/bin/bash

# Script để sửa lỗi Service class redeclare trên production server

echo "=== Bắt đầu sửa lỗi Service class redeclare ==="

# 1. Clear tất cả cache
echo "1. Clearing cache..."
php artisan config:clear
php artisan view:clear
php artisan cache:clear
php artisan route:clear
php artisan optimize:clear

# 2. Xóa compiled views
echo "2. Removing compiled views..."
rm -rf storage/framework/views/*.php

# 3. Kiểm tra file services/index.blade.php có chứa code PHP không
echo "3. Checking services/index.blade.php for PHP code..."
if grep -q "class Service extends Model" resources/views/services/index.blade.php; then
    echo "ERROR: Found PHP code in Blade file!"
    echo "Removing PHP code from Blade file..."
    # Tìm và xóa code PHP từ file Blade
    sed -i '/^<?php/,/^?>/d' resources/views/services/index.blade.php
    sed -i '/^namespace/,/^$/d' resources/views/services/index.blade.php
    sed -i '/^use Illuminate/,/^$/d' resources/views/services/index.blade.php
    sed -i '/^class Service/,/^}$/d' resources/views/services/index.blade.php
    echo "PHP code removed from Blade file"
else
    echo "OK: No PHP code found in Blade file"
fi

# 4. Kiểm tra các file Blade khác
echo "4. Checking other Blade files..."
for file in resources/views/services/*.blade.php; do
    if grep -q "class Service extends Model" "$file"; then
        echo "ERROR: Found PHP code in $file"
        sed -i '/^<?php/,/^?>/d' "$file"
        sed -i '/^namespace/,/^$/d' "$file"
        sed -i '/^use Illuminate/,/^$/d' "$file"
        sed -i '/^class Service/,/^}$/d' "$file"
        echo "PHP code removed from $file"
    fi
done

# 5. Chạy migration nếu cần
echo "5. Running migrations..."
php artisan migrate --force

# 6. Recompile views
echo "6. Recompiling views..."
php artisan view:cache

# 7. Optimize
echo "7. Optimizing application..."
php artisan optimize

echo "=== Hoàn thành sửa lỗi ==="
echo "Vui lòng kiểm tra lại trang /services"
