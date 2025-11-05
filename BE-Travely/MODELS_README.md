# Travely Backend - User Authentication & Models

## 📋 Tổng quan

Backend Laravel cho hệ thống đặt tour du lịch Travely với các tính năng:
- ✅ **UUID** làm Primary Key cho Users
- ✅ **Bcrypt** mã hóa password
- ✅ **Laravel Sanctum** cho Authentication (Token-based)
- ✅ **13 Models** với đầy đủ relationships

## 🗂️ Danh sách Models đã tạo

| Model | Table | Primary Key | Description |
|-------|-------|-------------|-------------|
| `Users` | users | userID (UUID) | Người dùng hệ thống |
| `Role` | roles | role_id | Vai trò người dùng |
| `Permission` | permissions | permission_id | Quyền hạn |
| `Tour` | tour | tourID | Tour du lịch |
| `Booking` | booking | bookingID | Đặt tour |
| `Review` | review | reviewID | Đánh giá tour |
| `Checkout` | checkout | checkoutID | Thanh toán |
| `Invoice` | invoice | invoiceID | Hóa đơn |
| `Promotion` | promotion | promotionID | Khuyến mãi |
| `Wishlist` | wishlist | - | Danh sách yêu thích |
| `History` | history | historyID | Lịch sử hoạt động |
| `TourImage` | tour_images | imageID | Hình ảnh tour |
| `TourItinerary` | tour_itinerary | itineraryID | Lịch trình tour |
| `ChatConversation` | chat_conversations | conversation_id (UUID) | Hội thoại chat |
| `ChatMessage` | chat_messages | message_id (UUID) | Tin nhắn chat |

## 🔐 Authentication API Endpoints

### Public Routes (Không cần token)

#### 1. Register (Đăng ký)
```http
POST /api/register
Content-Type: application/json

{
    "userName": "nguyenvana",
    "email": "nguyenvana@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phoneNumber": "0912345678",
    "address": "123 Nguyen Trai, Ha Noi"
}
```

**Response Success (201):**
```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user": {
            "userID": "550e8400-e29b-41d4-a716-446655440000",
            "userName": "nguyenvana",
            "email": "nguyenvana@example.com",
            "phoneNumber": "0912345678",
            "address": "123 Nguyen Trai, Ha Noi",
            "role_id": 2,
            "email_verified": false,
            "is_admin": false,
            "created_at": "2025-11-04T10:00:00.000000Z",
            "updated_at": "2025-11-04T10:00:00.000000Z"
        },
        "token": "1|abcdefghijklmnopqrstuvwxyz1234567890"
    }
}
```

#### 2. Login (Đăng nhập)
```http
POST /api/login
Content-Type: application/json

{
    "email": "nguyenvana@example.com",
    "password": "password123"
}
```

**Response Success (200):**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": { ... },
        "token": "2|xyz123456789abcdefghijklmnop"
    }
}
```

### Protected Routes (Cần Authorization header)

Header required cho tất cả các protected routes:
```http
Authorization: Bearer {token}
```

#### 3. Get Profile (Xem thông tin cá nhân)
```http
GET /api/profile
Authorization: Bearer {token}
```

#### 4. Update Profile (Cập nhật thông tin)
```http
PUT /api/profile
Authorization: Bearer {token}
Content-Type: application/json

{
    "userName": "nguyenvana_updated",
    "phoneNumber": "0987654321",
    "address": "456 Le Loi, TP HCM",
    "avatar_url": "https://example.com/avatar.jpg"
}
```

#### 5. Change Password (Đổi mật khẩu)
```http
POST /api/change-password
Authorization: Bearer {token}
Content-Type: application/json

{
    "current_password": "password123",
    "new_password": "newpassword456",
    "new_password_confirmation": "newpassword456"
}
```

#### 6. Logout (Đăng xuất)
```http
POST /api/logout
Authorization: Bearer {token}
```

## 🔄 Model Relationships

### Users Model
```php
// Quan hệ với các bảng khác
$user->role           // Role của user
$user->bookings       // Tất cả booking của user
$user->reviews        // Tất cả review của user
$user->wishlist       // Danh sách tour yêu thích
$user->history        // Lịch sử hoạt động
$user->conversationsAsUser  // Chat conversations (as user)
$user->conversationsAsAdmin // Chat conversations (as admin)
$user->chatMessages   // Tin nhắn đã gửi
```

### Tour Model
```php
$tour->bookings       // Tất cả booking của tour
$tour->reviews        // Tất cả review của tour
$tour->images         // Hình ảnh của tour
$tour->itineraries    // Lịch trình tour
$tour->wishlist       // Users đã yêu thích tour
```

### Booking Model
```php
$booking->user        // User đã đặt
$booking->tour        // Tour đã đặt
$booking->checkout    // Thông tin thanh toán
$booking->invoice     // Hóa đơn
$booking->conversations // Chat liên quan booking
```

## 💾 Database Migration

### Chạy migrations
```bash
# Migrate database
php artisan migrate

# Hoặc reset và migrate lại
php artisan migrate:fresh
```

### Rollback migrations
```bash
php artisan migrate:rollback
```

## 🌱 Seeding Data

Tạo file seeder cho Users:

```bash
php artisan make:seeder UsersSeeder
```

**database/seeders/UsersSeeder.php:**
```php
<?php

namespace Database\Seeders;

use App\Models\Users;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run()
    {
        // Tạo 1 admin user
        Users::factory()->admin()->create([
            'userName' => 'admin',
            'email' => 'admin@travely.com',
            'passWord' => 'admin123', // Sẽ tự động bcrypt
        ]);

        // Tạo 10 users thường
        Users::factory()->count(10)->create();
    }
}
```

Chạy seeder:
```bash
php artisan db:seed --class=UsersSeeder
```

## 📝 Sử dụng Models trong Code

### Tạo User mới
```php
use App\Models\Users;
use Illuminate\Support\Str;

$user = Users::create([
    'userID' => (string) Str::uuid(),
    'userName' => 'testuser',
    'passWord' => 'password123', // Tự động bcrypt
    'email' => 'test@example.com',
    'role_id' => 2,
    'created_by' => 'system',
    'updated_by' => 'system',
]);
```

### Tìm User và kiểm tra password
```php
$user = Users::where('email', 'test@example.com')->first();

if ($user && Hash::check('password123', $user->passWord)) {
    // Password đúng
}
```

### Tạo Tour mới
```php
use App\Models\Tour;

$tour = Tour::create([
    'title' => 'Tour Hạ Long 3 ngày 2 đêm',
    'description' => 'Khám phá vịnh Hạ Long...',
    'quantity' => 50,
    'priceAdult' => 2500000,
    'priceChild' => 1500000,
    'destination' => 'Hạ Long',
    'availability' => true,
    'startDate' => '2025-12-01',
    'endDate' => '2025-12-03',
]);
```

### Tạo Booking
```php
use App\Models\Booking;

$booking = Booking::create([
    'tourID' => $tour->tourID,
    'userID' => $user->userID,
    'numAdults' => 2,
    'numChildren' => 1,
    'totalPrice' => 6500000,
    'paymentStatus' => 'pending',
    'bookingStatus' => 'confirmed',
    'specialRequests' => 'Phòng view biển',
]);
```

### Lấy tất cả bookings của user với tour info
```php
$userBookings = Users::with(['bookings.tour'])
    ->find($userID)
    ->bookings;

foreach ($userBookings as $booking) {
    echo $booking->tour->title;
}
```

### Lấy reviews của một tour với thông tin user
```php
$tourReviews = Tour::with(['reviews.user'])
    ->find($tourID)
    ->reviews;

foreach ($tourReviews as $review) {
    echo $review->user->userName . ': ' . $review->comment;
}
```

## 🔧 Configuration

### Sanctum Configuration
Đảm bảo file `config/sanctum.php` đã được cấu hình đúng:

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
))),
```

### CORS Configuration
File `config/cors.php`:

```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'supports_credentials' => true,
```

## 🧪 Testing

### Test Registration
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "userName": "testuser",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Test Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

### Test Protected Route
```bash
curl -X GET http://localhost:8000/api/profile \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 📚 Tài liệu tham khảo

- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Sanctum](https://laravel.com/docs/sanctum)
- [Eloquent Relationships](https://laravel.com/docs/eloquent-relationships)

## ⚠️ Lưu ý quan trọng

1. **UUID Primary Keys**: Users, ChatConversation, ChatMessage sử dụng UUID tự động generate
2. **Password Hashing**: Model Users có mutator tự động bcrypt password
3. **Sanctum Tokens**: Cần install và publish Sanctum: `php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`
4. **Role ID**: Mặc định user mới có `role_id = 2` (user thường), admin có `role_id = 1`
5. **Timestamps**: Một số table không có `updated_at`, chỉ có `created_at` hoặc custom timestamp fields

## 🚀 Bước tiếp theo

1. Chạy migrations: `php artisan migrate`
2. Tạo seeders cho Role và Permission
3. Test authentication endpoints
4. Tạo controllers cho các models còn lại (Tour, Booking, etc.)
5. Implement authorization với Gates/Policies
6. Add validation rules và FormRequests
7. Implement email verification
8. Add refresh token functionality
