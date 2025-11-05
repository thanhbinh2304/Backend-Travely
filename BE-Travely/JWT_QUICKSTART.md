# 🚀 JWT Authentication - Quick Start

## 📦 Installation (Chọn 1 cách)

### Cách 1: PowerShell (Khuyến nghị)
```powershell
cd E:\Travely\BE-Travely
.\install-jwt.ps1
```

### Cách 2: Command Prompt
```cmd
cd E:\Travely\BE-Travely
install-jwt.bat
```

### Cách 3: Manual
Xem chi tiết trong `JWT_MIGRATION_GUIDE.md`

---

## ✅ Kiểm tra sau khi cài

### 1. Check JWT_SECRET trong .env
```bash
JWT_SECRET=your_generated_secret_here
```

### 2. Check config/jwt.php đã tồn tại
```bash
ls config/jwt.php
```

### 3. Check Users model
```php
// app/Models/Users.php should implement JWTSubject
class Users extends Authenticatable implements JWTSubject
```

### 4. Test API
```bash
php artisan serve
```

---

## 🔧 JWT Token Format

### Response khi Login/Register:
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "userID": "uuid-here",
      "userName": "testuser",
      "email": "test@example.com"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

### Sử dụng token trong request:
```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

---

## 📚 API Endpoints

### Public Routes (Không cần token)
- `POST /api/register` - Đăng ký
- `POST /api/login` - Login thường
- `POST /api/login/google` - Login Google
- `POST /api/login/facebook` - Login Facebook

### Protected Routes (Cần token)
- `GET /api/profile` - Xem profile
- `PUT /api/profile` - Update profile
- `POST /api/change-password` - Đổi password
- `POST /api/refresh` - Refresh token
- `POST /api/logout` - Logout

---

## 🧪 Test với Postman

### Import Collection
1. Mở Postman
2. Import → Upload Files
3. Chọn `Travely_Auth_JWT.postman_collection.json`
4. Import `Travely_Local_Environment.postman_environment.json`

### Auto-save Token
Collection đã cấu hình auto-save token vào environment variable `jwt_token`

---

## ⚙️ JWT Configuration

File: `config/jwt.php`

```php
// Token expiration time (minutes)
'ttl' => env('JWT_TTL', 60),

// Refresh token expiration (minutes)
'refresh_ttl' => env('JWT_REFRESH_TTL', 20160), // 2 weeks

// Algorithm
'algo' => env('JWT_ALGO', 'HS256'),
```

### Custom trong .env:
```
JWT_TTL=120          # 2 hours
JWT_REFRESH_TTL=43200  # 30 days
```

---

## 🔄 Refresh Token Flow

1. Access token hết hạn (401)
2. Gọi `/api/refresh` với token cũ
3. Nhận token mới
4. Dùng token mới cho requests tiếp theo

```javascript
// Auto refresh example (JavaScript)
axios.interceptors.response.use(
  response => response,
  async error => {
    if (error.response.status === 401) {
      const newToken = await refreshToken();
      error.config.headers['Authorization'] = `Bearer ${newToken}`;
      return axios.request(error.config);
    }
    return Promise.reject(error);
  }
);
```

---

## 🔐 JWT Token Structure

JWT token gồm 3 phần (ngăn cách bởi `.`):

```
eyJ0eXAiOiJKV1QiLCJhbGc.eyJzdWIiOiIxMjM0NTY3ODkw.SflKxwRJSMeKKF2QT4fwpMeJ
[    HEADER    ]  .  [    PAYLOAD     ]  .  [  SIGNATURE  ]
```

### Decode tại: https://jwt.io

**Header:**
```json
{
  "typ": "JWT",
  "alg": "HS256"
}
```

**Payload (Claims):**
```json
{
  "iss": "http://localhost:8000",
  "iat": 1234567890,
  "exp": 1234571490,
  "nbf": 1234567890,
  "jti": "unique-token-id",
  "sub": "user-uuid",
  "prv": "hash",
  "userName": "testuser",
  "email": "test@example.com",
  "role_id": 1
}
```

---

## ⚠️ Security Best Practices

1. **Không lưu JWT trong localStorage**
   - Dùng httpOnly cookies (production)
   - Hoặc sessionStorage

2. **Token expiration**
   - Access token: 15-60 phút
   - Refresh token: 7-30 ngày

3. **HTTPS only**
   - Production phải dùng HTTPS

4. **JWT_SECRET**
   - Phải phức tạp, random
   - Không commit vào Git

5. **Token blacklist**
   - Implement cho logout
   - Xem package: `tymon/jwt-auth` cache driver

---

## 🐛 Troubleshooting

### Error: "Class 'Tymon\JWTAuth\...' not found"
```bash
composer dump-autoload
php artisan config:clear
```

### Error: "The token has been blacklisted"
```bash
# Clear cache
php artisan config:clear
php artisan cache:clear

# Hoặc login lại để nhận token mới
```

### Error: "Token Signature could not be verified"
```bash
# Check JWT_SECRET trong .env
# Generate lại nếu cần
php artisan jwt:secret --force
```

### Token không hoạt động
1. ✅ Check middleware: `auth:api` (không phải `auth:sanctum`)
2. ✅ Check header format: `Authorization: Bearer {token}`
3. ✅ Check token chưa expired
4. ✅ Check config/auth.php: guard 'api' dùng driver 'jwt'

---

## 📁 Files Changed

### ✅ Đã tạo/sửa:
- ✅ `app/Models/Users.php` - Implement JWTSubject
- ✅ `app/Http/Controllers/AuthController_JWT.php` - JWT version
- ✅ `routes/api_jwt.php` - JWT routes
- ✅ `config/auth_jwt.php` - JWT config
- ✅ `install-jwt.ps1` - Auto install script
- ✅ `install-jwt.bat` - Auto install script (CMD)
- ✅ `Travely_Auth_JWT.postman_collection.json` - Postman collection
- ✅ `JWT_MIGRATION_GUIDE.md` - Chi tiết migration
- ✅ `JWT_QUICKSTART.md` - File này

### 📦 Backup files (tạo tự động):
- `app/Http/Controllers/AuthController_Sanctum.php.bak`
- `routes/api_sanctum.php.bak`
- `config/auth_sanctum.php.bak`

---

## 🔄 Rollback to Sanctum

Nếu muốn quay lại Sanctum:

```powershell
# Restore backup files
Copy-Item "app\Http\Controllers\AuthController_Sanctum.php.bak" "app\Http\Controllers\AuthController.php" -Force
Copy-Item "routes\api_sanctum.php.bak" "routes\api.php" -Force
Copy-Item "config\auth_sanctum.php.bak" "config\auth.php" -Force

# Clear cache
php artisan config:clear
php artisan cache:clear
```

---

## 📖 Documentation

- **JWT Package**: https://jwt-auth.readthedocs.io/
- **JWT Standard**: https://jwt.io/introduction
- **Laravel Auth**: https://laravel.com/docs/9.x/authentication

---

## 🆘 Support

Nếu gặp lỗi:
1. Xem `JWT_MIGRATION_GUIDE.md` - Troubleshooting section
2. Check Laravel logs: `storage/logs/laravel.log`
3. Enable debug: `.env` → `APP_DEBUG=true`

---

**Made with ❤️ for Travely Project**
