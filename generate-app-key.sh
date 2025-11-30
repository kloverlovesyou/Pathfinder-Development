#!/bin/bash
# Quick script to generate APP_KEY for Render deployment

echo "🔑 Generating Laravel APP_KEY..."
echo ""

cd backend 2>/dev/null || {
    echo "❌ Error: backend directory not found"
    echo "Make sure you're in the project root directory"
    exit 1
}

php artisan key:generate --show

echo ""
echo "✅ Copy the key above (starts with base64:)"
echo "📋 Paste it into Render environment variables as APP_KEY"

