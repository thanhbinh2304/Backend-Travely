# Start Laravel HTTPS Development Server
Write-Host "Starting Laravel Backend with HTTPS..." -ForegroundColor Green

# Start Laravel HTTP server in background
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd E:\Travely\BE-Travely\BE-Travely; Write-Host 'Laravel HTTP Server' -ForegroundColor Cyan; php artisan serve"

# Wait a bit for Laravel to start
Start-Sleep -Seconds 2

# Start HTTPS proxy in background
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd E:\Travely\BE-Travely\BE-Travely; Write-Host 'HTTPS Proxy Server' -ForegroundColor Cyan; node https-proxy.js"

Write-Host "`nBackend is starting..." -ForegroundColor Green
Write-Host "Laravel HTTP: http://127.0.0.1:8000" -ForegroundColor Yellow
Write-Host "HTTPS Proxy:  https://127.0.0.1:8443" -ForegroundColor Yellow
Write-Host "`nAPI URL: https://127.0.0.1:8443/api" -ForegroundColor Cyan
