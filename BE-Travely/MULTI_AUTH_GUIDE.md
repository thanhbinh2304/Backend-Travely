# 🔐 Multi-Authentication Guide - Travely Backend

## 📋 Tổng quan

Hệ thống hỗ trợ **3 phương thức đăng nhập**:
1. ✅ **Username/Password** - Đăng ký và đăng nhập thông thường
2. ✅ **Google OAuth** - Đăng nhập bằng tài khoản Google
3. ✅ **Facebook OAuth** - Đăng nhập bằng tài khoản Facebook

## 🚀 API Endpoints

### 1. Đăng ký Username/Password

```http
POST /api/register
Content-Type: application/json

{
    "userName": "nguyenvana",
    "email": "nguyenvana@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phoneNumber": "0912345678",
    "address": "Ha Noi, Vietnam"
}
```

**Response (201):**
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
            "address": "Ha Noi, Vietnam",
            "role_id": 2,
            "email_verified": false,
            "is_admin": false,
            "google_id": null,
            "facebook_id": null,
            "avatar_url": null
        },
        "token": "1|abcdefghijklmnopqrstuvwxyz1234567890"
    }
}
```

---

### 2. Đăng nhập Username/Password

```http
POST /api/login
Content-Type: application/json

{
    "email": "nguyenvana@example.com",
    "password": "password123"
}
```

**Response (200):**
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

---

### 3. Đăng nhập bằng Google

```http
POST /api/login/google
Content-Type: application/json

{
    "google_id": "103876543210987654321",
    "email": "user@gmail.com",
    "name": "Nguyen Van A",
    "avatar": "https://lh3.googleusercontent.com/a/..."
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Google login successful",
    "data": {
        "user": {
            "userID": "6ba7b810-9dad-11d1-80b4-00c04fd430c8",
            "userName": "nguyenvana",
            "email": "user@gmail.com",
            "google_id": "103876543210987654321",
            "avatar_url": "https://lh3.googleusercontent.com/a/...",
            "email_verified": true,
            "role_id": 2,
            "is_admin": false
        },
        "token": "3|googletoken123456789"
    }
}
```

**Lưu ý:**
- Nếu user đã tồn tại (cùng email hoặc google_id), hệ thống sẽ login và cập nhật thông tin
- Nếu user mới, hệ thống tự động tạo account với:
  - UUID tự động
  - Username từ name (loại bỏ ký tự đặc biệt)
  - Password random (user không cần biết vì đăng nhập bằng Google)
  - Email verified = true

---

### 4. Đăng nhập bằng Facebook

```http
POST /api/login/facebook
Content-Type: application/json

{
    "facebook_id": "123456789012345",
    "email": "user@facebook.com",
    "name": "Nguyen Van B",
    "avatar": "https://graph.facebook.com/123456789012345/picture"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Facebook login successful",
    "data": {
        "user": {
            "userID": "7c9e6679-7425-40de-944b-e07fc1f90ae7",
            "userName": "nguyenvanb",
            "email": "user@facebook.com",
            "facebook_id": "123456789012345",
            "avatar_url": "https://graph.facebook.com/123456789012345/picture",
            "email_verified": true,
            "role_id": 2,
            "is_admin": false
        },
        "token": "4|facebooktoken987654321"
    }
}
```

**Lưu ý:**
- Email có thể null (một số user Facebook không public email)
- Logic tương tự Google: tự động tạo user mới hoặc login user cũ
- Username được generate tự động từ name

---

## 🔄 Flow đăng nhập Social

### Google Login Flow

```
Frontend                    Backend                      Google
   |                          |                            |
   |-- Click "Login Google" ->|                            |
   |                          |                            |
   |<-- Redirect to Google ---|                            |
   |                          |                            |
   |-- User authorize -------->|                            |
   |                          |                            |
   |<-- Get user info + id_token ---------------------------|
   |                          |                            |
   |-- POST /api/login/google ->                           |
   |    (google_id, email,    |                            |
   |     name, avatar)        |                            |
   |                          |                            |
   |                          |-- Check/Create User        |
   |                          |-- Generate Token           |
   |                          |                            |
   |<-- Return user + token --|                            |
   |                          |                            |
```

### Facebook Login Flow

```
Frontend                    Backend                    Facebook
   |                          |                            |
   |-- Click "Login FB" ----->|                            |
   |                          |                            |
   |<-- Redirect to FB -------|                            |
   |                          |                            |
   |-- User authorize -------->|                            |
   |                          |                            |
   |<-- Get user info + access_token ----------------------|
   |                          |                            |
   |-- POST /api/login/facebook ->                        |
   |    (facebook_id, email,  |                            |
   |     name, avatar)        |                            |
   |                          |                            |
   |                          |-- Check/Create User        |
   |                          |-- Generate Token           |
   |                          |                            |
   |<-- Return user + token --|                            |
   |                          |                            |
```

---

## 🛠️ Frontend Implementation

### React Example - Google Login

```javascript
import { GoogleOAuthProvider, GoogleLogin } from '@react-oauth/google';
import { jwtDecode } from 'jwt-decode';

function LoginPage() {
  const handleGoogleSuccess = async (credentialResponse) => {
    // Decode JWT token from Google
    const decoded = jwtDecode(credentialResponse.credential);
    
    // Send to backend
    const response = await fetch('http://localhost:8000/api/login/google', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        google_id: decoded.sub,
        email: decoded.email,
        name: decoded.name,
        avatar: decoded.picture,
      }),
    });
    
    const data = await response.json();
    
    if (data.success) {
      // Save token
      localStorage.setItem('token', data.data.token);
      localStorage.setItem('user', JSON.stringify(data.data.user));
      // Redirect to dashboard
    }
  };

  return (
    <GoogleOAuthProvider clientId="YOUR_GOOGLE_CLIENT_ID">
      <GoogleLogin
        onSuccess={handleGoogleSuccess}
        onError={() => console.log('Login Failed')}
      />
    </GoogleOAuthProvider>
  );
}
```

### React Example - Facebook Login

```javascript
import FacebookLogin from 'react-facebook-login';

function LoginPage() {
  const handleFacebookResponse = async (response) => {
    if (response.accessToken) {
      // Send to backend
      const apiResponse = await fetch('http://localhost:8000/api/login/facebook', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          facebook_id: response.userID,
          email: response.email,
          name: response.name,
          avatar: response.picture?.data?.url,
        }),
      });
      
      const data = await apiResponse.json();
      
      if (data.success) {
        localStorage.setItem('token', data.data.token);
        localStorage.setItem('user', JSON.stringify(data.data.user));
      }
    }
  };

  return (
    <FacebookLogin
      appId="YOUR_FACEBOOK_APP_ID"
      fields="name,email,picture"
      callback={handleFacebookResponse}
    />
  );
}
```

### Vue.js Example - Google Login

```vue
<template>
  <div>
    <GoogleLogin :callback="handleGoogleLogin" />
  </div>
</template>

<script setup>
import { decodeCredential } from 'vue3-google-login'

const handleGoogleLogin = async (response) => {
  const userData = decodeCredential(response.credential)
  
  const apiResponse = await fetch('http://localhost:8000/api/login/google', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      google_id: userData.sub,
      email: userData.email,
      name: userData.name,
      avatar: userData.picture,
    }),
  })
  
  const data = await apiResponse.json()
  
  if (data.success) {
    localStorage.setItem('token', data.data.token)
    // Navigate to dashboard
  }
}
</script>
```

---

## 🔧 Configuration

### 1. Google OAuth Setup

1. Truy cập [Google Cloud Console](https://console.cloud.google.com/)
2. Tạo project mới hoặc chọn project
3. Enable Google+ API
4. Tạo OAuth 2.0 credentials
5. Add authorized redirect URIs:
   - `http://localhost:3000` (development)
   - `https://yourdomain.com` (production)
6. Copy Client ID

### 2. Facebook OAuth Setup

1. Truy cập [Facebook Developers](https://developers.facebook.com/)
2. Tạo app mới
3. Add Facebook Login product
4. Configure OAuth redirect URIs:
   - `http://localhost:3000` (development)
   - `https://yourdomain.com` (production)
5. Copy App ID

### 3. Backend .env Configuration (Optional)

```env
# Google OAuth
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret

# Facebook OAuth
FACEBOOK_APP_ID=your-facebook-app-id
FACEBOOK_APP_SECRET=your-facebook-app-secret
```

---

## 📝 Database Structure

### Migration đã tạo

```bash
php artisan migrate
```

Migration `2025_11_04_080100_add_facebook_id_to_users_table.php` thêm cột:
- `facebook_id` (string, nullable) - Facebook user ID

Cấu trúc users table:
```sql
users
├── userID (UUID, primary)
├── userName
├── passWord (bcrypt)
├── email
├── phoneNumber
├── address
├── role_id
├── google_id (nullable)
├── facebook_id (nullable)  ← MỚI
├── avatar_url (nullable)
├── email_verified
├── is_admin
└── timestamps
```

---

## 🧪 Testing

### Test Google Login (PowerShell)

```powershell
curl -X POST http://localhost:8000/api/login/google `
  -H "Content-Type: application/json" `
  -d '{\"google_id\":\"103876543210\",\"email\":\"test@gmail.com\",\"name\":\"Test User\",\"avatar\":\"https://example.com/avatar.jpg\"}'
```

### Test Facebook Login (PowerShell)

```powershell
curl -X POST http://localhost:8000/api/login/facebook `
  -H "Content-Type: application/json" `
  -d '{\"facebook_id\":\"123456789\",\"email\":\"test@facebook.com\",\"name\":\"Test User\",\"avatar\":\"https://example.com/avatar.jpg\"}'
```

### Test Normal Login

```powershell
curl -X POST http://localhost:8000/api/login `
  -H "Content-Type: application/json" `
  -d '{\"email\":\"test@example.com\",\"password\":\"password123\"}'
```

---

## 🔐 Security Notes

1. **Google/Facebook Authentication:**
   - Frontend nên verify token với Google/Facebook trước khi gửi lên backend
   - Backend tin tưởng data từ frontend (cần validate từ Google/Facebook API trong production)

2. **Password cho Social Users:**
   - User đăng nhập bằng Google/Facebook có password random
   - Họ có thể set password riêng bằng API `/change-password` (cần modify logic)

3. **Token Management:**
   - Sanctum token có thời gian sống dài
   - Nên implement refresh token mechanism
   - Revoke token khi logout

4. **Email Verification:**
   - User từ Google/Facebook tự động verified
   - User thường cần verify email (implement riêng)

---

## 📚 Next Steps

1. ✅ Đã implement 3 phương thức login
2. 🔄 Cần verify Google/Facebook token từ server-side
3. 🔄 Implement link/unlink social accounts
4. 🔄 Implement email verification cho user thường
5. 🔄 Implement forgot password
6. 🔄 Add middleware check social login provider
7. 🔄 Add rate limiting cho login endpoints

---

## ⚠️ Important Commands

```powershell
# Run migration
php artisan migrate

# Create test user
php artisan tinker
>>> User::factory()->create()

# Check routes
php artisan route:list --path=api/login
```

## 🎯 Summary

✅ **3 Authentication Methods:**
- Username/Password: `/api/register`, `/api/login`
- Google: `/api/login/google`
- Facebook: `/api/login/facebook`

✅ **Auto-features:**
- UUID auto-generate for new users
- Password auto-bcrypt
- Username auto-generate from social name
- Email auto-verified for social users

✅ **Token-based:**
- Laravel Sanctum
- Same token format for all methods
- Use `Authorization: Bearer {token}` for protected routes
