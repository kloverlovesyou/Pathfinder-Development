@echo off
REM Quick script to generate APP_KEY for Render deployment (Windows)

echo 🔑 Generating Laravel APP_KEY...
echo.

cd backend 2>nul || (
    echo ❌ Error: backend directory not found
    echo Make sure you're in the project root directory
    pause
    exit /b 1
)

php artisan key:generate --show

echo.
echo ✅ Copy the key above (starts with base64:)
echo 📋 Paste it into Render environment variables as APP_KEY
pause

