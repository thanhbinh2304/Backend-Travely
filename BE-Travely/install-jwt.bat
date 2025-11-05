@echo off
REM JWT Installation & Migration Script for Windows Command Prompt

echo =====================================
echo    JWT Installation ^& Migration
echo =====================================
echo.

cd /d E:\Travely\BE-Travely

REM Step 1: Install JWT Package
echo [Step 1/7] Installing JWT package...
call composer require tymon/jwt-auth
if errorlevel 1 (
    echo Error: Failed to install JWT package
    exit /b 1
)
echo [OK] JWT package installed
echo.

REM Step 2: Publish JWT Config
echo [Step 2/7] Publishing JWT config...
call php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
if errorlevel 1 (
    echo Error: Failed to publish JWT config
    exit /b 1
)
echo [OK] JWT config published
echo.

REM Step 3: Generate JWT Secret
echo [Step 3/7] Generating JWT secret...
call php artisan jwt:secret
if errorlevel 1 (
    echo Error: Failed to generate JWT secret
    exit /b 1
)
echo [OK] JWT secret generated
echo.

REM Step 4: Backup original files
echo [Step 4/7] Backing up original files...
copy /Y app\Http\Controllers\AuthController.php app\Http\Controllers\AuthController_Sanctum.php.bak
copy /Y routes\api.php routes\api_sanctum.php.bak
copy /Y config\auth.php config\auth_sanctum.php.bak
echo [OK] Original files backed up
echo.

REM Step 5: Replace with JWT versions
echo [Step 5/7] Replacing with JWT versions...
copy /Y app\Http\Controllers\AuthController_JWT.php app\Http\Controllers\AuthController.php
copy /Y routes\api_jwt.php routes\api.php
copy /Y config\auth_jwt.php config\auth.php
echo [OK] JWT versions applied
echo.

REM Step 6: Clear cache
echo [Step 6/7] Clearing cache...
call php artisan config:clear
call php artisan cache:clear
call php artisan route:clear
echo [OK] Cache cleared
echo.

REM Step 7: Composer dump-autoload
echo [Step 7/7] Running composer dump-autoload...
call composer dump-autoload
echo [OK] Autoload dumped
echo.

echo =====================================
echo    [OK] JWT Migration Complete!
echo =====================================
echo.
echo Next Steps:
echo 1. Check .env file for JWT_SECRET
echo 2. Test API endpoints with new JWT tokens
echo 3. Update Postman collection for JWT format
echo.
echo Backup files created:
echo - app\Http\Controllers\AuthController_Sanctum.php.bak
echo - routes\api_sanctum.php.bak
echo - config\auth_sanctum.php.bak
echo.
echo To rollback, rename .bak files back to original names
echo.

pause
