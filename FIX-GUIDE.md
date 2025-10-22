# Hướng dẫn sửa lỗi và cập nhật

## Lỗi đã sửa:

### 1. Lỗi "Cannot redeclare class App\Models\Service"
- **Nguyên nhân:** Code PHP của model Service bị copy nhầm vào file Blade view
- **Đã sửa:** Loại bỏ code PHP không cần thiết khỏi file Blade

### 2. Lỗi nhập giá không chính xác
- **Nguyên nhân:** Input có `step="1000"` chỉ cho phép nhập số chia hết cho 1000
- **Đã sửa:** Thay đổi thành `step="1"` để cho phép nhập bất kỳ số nào

## Commands cần chạy trên production server:

### 1. Clear cache để đảm bảo thay đổi có hiệu lực:
```bash
php artisan config:clear
php artisan view:clear
php artisan cache:clear
php artisan route:clear
```

### 2. Chạy migration (nếu chưa chạy):
```bash
php artisan migrate
```

### 3. Kiểm tra trạng thái migration:
```bash
php artisan migrate:status
```

### 4. Restart web server (nếu cần):
```bash
# Nếu dùng Apache
sudo systemctl restart apache2

# Nếu dùng Nginx + PHP-FPM
sudo systemctl restart nginx
sudo systemctl restart php8.4-fpm
```

## Kiểm tra sau khi sửa:

1. **Kiểm tra trang quản lý dịch vụ:** Truy cập `/services` để đảm bảo không còn lỗi fatal
2. **Kiểm tra nhập giá:** Thử nhập giá 1111, 5000, 12345 để đảm bảo hoạt động bình thường
3. **Kiểm tra các trường mới:** Đảm bảo các cột mới hiển thị đúng

## Lưu ý:
- Sau khi clear cache, trang web có thể chậm hơn một chút trong lần đầu truy cập
- Nếu vẫn còn lỗi, kiểm tra log: `tail -f storage/logs/laravel.log`
