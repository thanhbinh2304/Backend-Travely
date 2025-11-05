# 🚀 Quick Start Guide - Travely Backend

## ✅ Đã hoàn thành

### 📁 Models (15 files)
- ✅ `Users.php` - User authentication với UUID + bcrypt
- ✅ `Role.php` - Vai trò người dùng
- ✅ `Permission.php` - Quyền hạn
- ✅ `Tour.php` - Tour du lịch
- ✅ `Booking.php` - Đặt tour
- ✅ `Review.php` - Đánh giá
- ✅ `Checkout.php` - Thanh toán
- ✅ `Invoice.php` - Hóa đơn
- ✅ `Promotion.php` - Khuyến mãi
- ✅ `Wishlist.php` - Yêu thích
- ✅ `History.php` - Lịch sử
- ✅ `TourImage.php` - Hình ảnh tour
- ✅ `TourItinerary.php` - Lịch trình
- ✅ `ChatConversation.php` - Hội thoại
- ✅ `ChatMessage.php` - Tin nhắn

### 🔐 Authentication
- ✅ `AuthController.php` - Register, Login, Logout, Profile, Change Password
- ✅ Routes API với Sanctum middleware
- ✅ UserFactory với UUID và bcrypt

## 🎯 Các lệnh cần chạy

```powershell
# 1. Cài đặt dependencies (nếu chưa có)
cd E:\Travely\BE-Travely
composer install

# 2. Copy .env file (nếu chưa có)
cp .env.example .env

# 3. Generate app key
php artisan key:generate

# 4. Cấu hình database trong .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=travely
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Chạy migrations
php artisan migrate

# 6. (Optional) Seed data
php artisan db:seed

# 7. Start server
php artisan serve
```

## 🧪 Test API

### Register User
```powershell
curl -X POST http://localhost:8000/api/register `
  -H "Content-Type: application/json" `
  -d '{\"userName\":\"testuser\",\"email\":\"test@example.com\",\"password\":\"password123\",\"password_confirmation\":\"password123\"}'
```

### Login
```powershell
curl -X POST http://localhost:8000/api/login `
  -H "Content-Type: application/json" `
  -d '{\"email\":\"test@example.com\",\"password\":\"password123\"}'
```

### Get Profile (cần token từ login)
```powershell
curl -X GET http://localhost:8000/api/profile `
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 📊 Cấu trúc Database

```
users (UUID: userID)
├── bookings
│   ├── checkout
│   ├── invoice
│   └── chat_conversations
├── reviews
├── wishlist
├── history
└── chat_messages

roles
├── users
└── permissions (many-to-many)

tour
├── bookings
├── reviews
├── tour_images
├── tour_itinerary
└── wishlist
```

## 🔑 Features chính

### 1. UUID Primary Key
- Users: `userID`
- ChatConversation: `conversation_id`
- ChatMessage: `message_id`

### 2. Auto Bcrypt Password
```php
$user->passWord = 'plaintext'; // Tự động bcrypt
```

### 3. Laravel Sanctum Auth
```php
// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Your routes
});
```

### 4. Eloquent Relationships
```php
$user->bookings()->with('tour')->get();
$tour->reviews()->with('user')->get();
$booking->user()->first();
```

## 📝 Các bước tiếp theo

1. ✅ Đã tạo tất cả models với relationships
2. ✅ Đã setup authentication với Sanctum
3. ✅ Đã có UserFactory với UUID và bcrypt
4. 🔄 Cần tạo: Controllers cho Tour, Booking, Review, etc.
5. 🔄 Cần tạo: Seeders cho Role, Permission, Tours
6. 🔄 Cần implement: Email verification
7. 🔄 Cần implement: Password reset
8. 🔄 Cần implement: File upload cho avatar và tour images
9. 🔄 Cần implement: Authorization policies
10. 🔄 Cần implement: API resources/transformers

## 🆘 Troubleshooting

### Lỗi "Class 'Laravel\Sanctum\HasApiTokens' not found"
```powershell
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

### Lỗi "SQLSTATE[HY000] [2002] Connection refused"
- Check MySQL đang chạy
- Check config trong `.env`

### Token không hoạt động
- Check middleware `'auth:sanctum'` trong routes
- Check header: `Authorization: Bearer {token}`

## 📚 Documentation

Chi tiết đầy đủ xem file: `MODELS_README.md`
