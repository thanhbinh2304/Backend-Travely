# 🔐 JWT Installation & Migration Script
# Tự động cài đặt và cấu hình JWT cho Laravel

Write-Host "=====================================" -ForegroundColor Cyan
Write-Host "   JWT Installation & Migration" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""

# Change to Laravel directory
$laravelDir = "E:\Travely\BE-Travely"
Set-Location $laravelDir

# Step 1: Install JWT Package
Write-Host "[Step 1/7] Installing JWT package..." -ForegroundColor Yellow
composer require tymon/jwt-auth
if ($LASTEXITCODE -ne 0) {
    Write-Host "Error: Failed to install JWT package" -ForegroundColor Red
    exit 1
}
Write-Host "✓ JWT package installed" -ForegroundColor Green
Write-Host ""

# Step 2: Publish JWT Config
Write-Host "[Step 2/7] Publishing JWT config..." -ForegroundColor Yellow
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
if ($LASTEXITCODE -ne 0) {
    Write-Host "Error: Failed to publish JWT config" -ForegroundColor Red
    exit 1
}
Write-Host "✓ JWT config published" -ForegroundColor Green
Write-Host ""

# Step 3: Generate JWT Secret
Write-Host "[Step 3/7] Generating JWT secret..." -ForegroundColor Yellow
php artisan jwt:secret
if ($LASTEXITCODE -ne 0) {
    Write-Host "Error: Failed to generate JWT secret" -ForegroundColor Red
    exit 1
}
Write-Host "✓ JWT secret generated" -ForegroundColor Green
Write-Host ""

# Step 4: Backup original files
Write-Host "[Step 4/7] Backing up original files..." -ForegroundColor Yellow
Copy-Item "app\Http\Controllers\AuthController.php" "app\Http\Controllers\AuthController_Sanctum.php.bak" -Force
Copy-Item "routes\api.php" "routes\api_sanctum.php.bak" -Force
Copy-Item "config\auth.php" "config\auth_sanctum.php.bak" -Force
Write-Host "✓ Original files backed up" -ForegroundColor Green
Write-Host ""

# Step 5: Replace with JWT versions
Write-Host "[Step 5/7] Replacing with JWT versions..." -ForegroundColor Yellow
Copy-Item "app\Http\Controllers\AuthController_JWT.php" "app\Http\Controllers\AuthController.php" -Force
Copy-Item "routes\api_jwt.php" "routes\api.php" -Force
Copy-Item "config\auth_jwt.php" "config\auth.php" -Force
Write-Host "✓ JWT versions applied" -ForegroundColor Green
Write-Host ""

# Step 6: Clear cache
Write-Host "[Step 6/7] Clearing cache..." -ForegroundColor Yellow
php artisan config:clear
php artisan cache:clear
php artisan route:clear
Write-Host "✓ Cache cleared" -ForegroundColor Green
Write-Host ""

# Step 7: Composer dump-autoload
Write-Host "[Step 7/7] Running composer dump-autoload..." -ForegroundColor Yellow
composer dump-autoload
Write-Host "✓ Autoload dumped" -ForegroundColor Green
Write-Host ""

Write-Host "=====================================" -ForegroundColor Green
Write-Host "   ✓ JWT Migration Complete!" -ForegroundColor Green
Write-Host "=====================================" -ForegroundColor Green
Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Cyan
Write-Host "1. Check .env file for JWT_SECRET" -ForegroundColor White
Write-Host "2. Test API endpoints with new JWT tokens" -ForegroundColor White
Write-Host "3. Update Postman collection for JWT format" -ForegroundColor White
Write-Host ""
Write-Host "Backup files created:" -ForegroundColor Yellow
Write-Host "- app\Http\Controllers\AuthController_Sanctum.php.bak" -ForegroundColor White
Write-Host "- routes\api_sanctum.php.bak" -ForegroundColor White
Write-Host "- config\auth_sanctum.php.bak" -ForegroundColor White
Write-Host ""
Write-Host "To rollback, rename .bak files back to original names" -ForegroundColor Gray
Write-Host ""
