# Docker Setup Guide - Travely Backend

## 📋 Yêu cầu

- Docker Desktop (Windows/Mac) hoặc Docker Engine (Linux)
- Docker Compose v2.0+
- Git

## 🚀 Môi trường Development

### Khởi động dự án

```bash
# Clone và di chuyển vào thư mục dự án
cd BE-Travely

# Copy file .env
cp .env.example .env

# Chỉnh sửa .env với các thông tin sau:
# DB_HOST=mysql
# DB_DATABASE=travely
# DB_USERNAME=travely_user
# DB_PASSWORD=travely_password
# REDIS_HOST=redis
# CACHE_DRIVER=redis
# SESSION_DRIVER=redis

# Build và chạy containers
docker-compose -f docker-compose.dev.yml up -d

# Chờ containers khởi động, sau đó chạy migrations
docker exec travely-backend-dev php artisan key:generate
docker exec travely-backend-dev php artisan migrate --seed
docker exec travely-backend-dev php artisan storage:link
docker exec travely-backend-dev php artisan l5-swagger:generate
```

### Truy cập services

- **Backend API**: http://localhost:8000
- **phpMyAdmin**: http://localhost:8080
- **Mailhog**: http://localhost:8025
- **API Documentation**: http://localhost:8000/api/documentation

### Các lệnh thường dùng

```bash
# Xem logs
docker-compose -f docker-compose.dev.yml logs -f app

# Chạy artisan commands
docker exec travely-backend-dev php artisan <command>

# Truy cập vào container
docker exec -it travely-backend-dev sh

# Chạy composer
docker exec travely-backend-dev composer install
docker exec travely-backend-dev composer update

# Chạy tests
docker exec travely-backend-dev php artisan test

# Dừng containers
docker-compose -f docker-compose.dev.yml down

# Dừng và xóa volumes (xóa database)
docker-compose -f docker-compose.dev.yml down -v

# Rebuild containers
docker-compose -f docker-compose.dev.yml up -d --build
```

## 🏭 Môi trường Production

### Build image

```bash
# Build production image
docker build -t travely-backend:latest .

# Hoặc sử dụng docker-compose
docker-compose up -d --build
```

### Chạy production containers

```bash
# Khởi động
docker-compose up -d

# Chạy migrations (lần đầu)
docker exec travely-backend php artisan migrate --force

# Tối ưu hóa
docker exec travely-backend php artisan config:cache
docker exec travely-backend php artisan route:cache
docker exec travely-backend php artisan view:cache
```

### Environment Variables quan trọng

Cập nhật các biến môi trường trong `docker-compose.yml`:

```yaml
environment:
  - APP_ENV=production
  - APP_DEBUG=false
  - APP_KEY=<your-app-key>
  - DB_HOST=mysql
  - DB_DATABASE=travely
  - DB_USERNAME=travely_user
  - DB_PASSWORD=<strong-password>
  - REDIS_HOST=redis
```

## 🔧 Cấu trúc Docker

```
BE-Travely/
├── Dockerfile              # Production image
├── Dockerfile.dev          # Development image
├── docker-compose.yml      # Production setup
├── docker-compose.dev.yml  # Development setup
├── .dockerignore          # Files to exclude
└── docker/
    ├── nginx/
    │   ├── nginx.conf
    │   └── default.conf
    ├── php/
    │   ├── php.ini         # Production PHP config
    │   ├── php.dev.ini     # Development PHP config
    │   ├── opcache.ini
    │   └── xdebug.ini      # Debug configuration
    └── supervisor/
        ├── supervisord.conf     # Production supervisor
        └── supervisord.dev.conf # Development supervisor
```

## 🐛 Debug với Xdebug (Development)

Xdebug đã được cấu hình sẵn trong môi trường development.

### VS Code Configuration

Thêm vào `.vscode/launch.json`:

```json
{
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for Xdebug",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "pathMappings": {
                "/var/www/html": "${workspaceFolder}"
            }
        }
    ]
}
```

## 📊 Services

### MySQL
- Port: 3306
- Database: travely
- User: travely_user
- Password: travely_password (dev) / travely_secure_password (prod)

### Redis
- Port: 6379
- Sử dụng cho cache, sessions, và queues

### Nginx + PHP-FPM
- Port: 80 (trong container) → 8000 (host)
- PHP 8.2 với các extensions cần thiết

### Supervisor
- Quản lý PHP-FPM, Nginx, Queue workers, và Schedule

## 🔒 Bảo mật Production

1. **Thay đổi mật khẩu mặc định** trong `docker-compose.yml`
2. **Đặt APP_DEBUG=false** trong production
3. **Sử dụng HTTPS** với reverse proxy (Nginx/Traefik/Caddy)
4. **Giới hạn quyền truy cập** vào phpMyAdmin
5. **Backup database** thường xuyên
6. **Update images** định kỳ

## 📈 Performance Tuning

### OPcache
- Đã được enable trong production
- Tối ưu cho performance

### Redis
- Sử dụng cho cache, sessions, và queues
- Persistent storage với AOF

### Queue Workers
- 2 workers chạy song song
- Auto-restart nếu bị lỗi

## 🆘 Troubleshooting

### Lỗi permissions
```bash
docker exec travely-backend-dev chown -R www-data:www-data storage bootstrap/cache
docker exec travely-backend-dev chmod -R 775 storage bootstrap/cache
```

### Lỗi database connection
```bash
# Kiểm tra MySQL container
docker-compose -f docker-compose.dev.yml ps mysql

# Xem logs
docker-compose -f docker-compose.dev.yml logs mysql
```

### Reset toàn bộ
```bash
docker-compose -f docker-compose.dev.yml down -v
docker-compose -f docker-compose.dev.yml up -d --build
docker exec travely-backend-dev php artisan migrate:fresh --seed
```

## 📝 Notes

- Development environment mount source code để hot-reload
- Production environment copy code vào image để tối ưu
- Sử dụng multi-stage build để giảm kích thước image
- Health check được cấu hình tại `/api/health`
