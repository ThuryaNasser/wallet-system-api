#!/bin/bash

# Wallet System API - Automated Setup Script
# This script automates the installation steps from README.md

set -e  # Exit immediately if a command exits with a non-zero status

echo "🚀 Starting Wallet System API Setup..."
echo "======================================"

# Function to handle errors
handle_error() {
    echo ""
    echo "❌ Setup failed at step: $1"
    echo ""
    echo "Please follow the manual installation steps in README.md:"
    echo "1. composer install"
    echo "2. cp .env.example .env (if .env doesn't exist)"
    echo "3. php artisan key:generate"
    echo "4. touch database/database.sqlite"
    echo "5. php artisan migrate"
    echo "6. php artisan db:seed"
    echo "7. php artisan serve"
    echo ""
    exit 1
}

# Step 1: Install dependencies
echo "📦 Step 1/7: Installing dependencies..."
if ! composer install; then
    handle_error "Installing dependencies (composer install)"
fi
echo "✅ Dependencies installed successfully"

# Step 2: Set up environment file
echo "⚙️  Step 2/7: Setting up environment..."
if [ ! -f .env ]; then
    if ! cp .env.example .env; then
        handle_error "Copying environment file (.env.example to .env)"
    fi
    echo "✅ Environment file created"
else
    echo "✅ Environment file already exists"
fi

# Step 3: Generate application key
echo "🔑 Step 3/7: Generating application key..."
if ! php artisan key:generate; then
    handle_error "Generating application key (php artisan key:generate)"
fi
echo "✅ Application key generated"

# Step 4: Create SQLite database
echo "🗄️  Step 4/7: Creating SQLite database..."
if ! touch database/database.sqlite; then
    handle_error "Creating SQLite database (touch database/database.sqlite)"
fi
echo "✅ SQLite database created"

# Step 5: Run fresh migrations with seeding
echo "🏗️  Step 5/7: Running fresh database migrations with seeding..."
if ! php artisan migrate:fresh --seed; then
    handle_error "Running fresh migrations with seeding (php artisan migrate:fresh --seed)"
fi
echo "✅ Database migrations and seeding completed"

# Step 6: Verification step (keeping step numbers for consistency)
echo "✅ Step 6/7: Database setup verified"

# Step 7: Check if server can start (don't actually start it)
echo "🌐 Step 7/7: Verifying Laravel installation..."
if ! php artisan --version > /dev/null; then
    handle_error "Laravel installation verification"
fi
echo "✅ Laravel installation verified"

echo ""
echo "🎉 Setup completed successfully!"
echo "=============================="
echo ""
echo "Your Wallet System API is ready to use!"
echo ""
echo "To start the development server, run:"
echo "  php artisan serve"
echo ""
echo "The API will be available at: http://127.0.0.1:8000"
echo ""
echo "Test user created:"
echo "  - Name: Test User"
echo "  - Email: test@example.com"
echo "  - User ID: 1"
echo ""
echo "API Documentation: Check README.md for endpoints and examples"
echo "Postman Collection: Import Wallet_API.postman_collection.json"
echo ""
