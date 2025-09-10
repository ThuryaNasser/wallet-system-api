# Wallet System API

A simple wallet system API built with Laravel to manage user accounts with basic wallet operations including account creation, balance top-up, and balance charging.

## Features

- **Create Account**: Create a new user account with wallet functionality
- **Top-Up**: Add balance to a user account with precise decimal handling
- **Charge**: Deduct balance from a user account with balance validation
- **Balance Inquiry**: Get current user balance
- **Transaction History**: View user transaction history
- **Duplicate Prevention**: Prevents duplicate transactions using unique references
- **Currency Precision**: Handles all monetary values with 2 decimal places
- **Negative Balance Prevention**: Ensures balances never go below zero

## Prerequisites

- PHP 8.1 or higher
- Composer
- SQLite (included with PHP)

## API Configuration

This is an **API-only** Laravel application optimized for JSON responses:
- No web routes or Blade templates required
- Automatic JSON content negotiation (no need for `Accept: application/json` header)
- Session and CSRF middleware disabled for API routes
- Optimized middleware stack for better performance

## Installation & Setup

### Option 1: Automated Setup (Recommended)

1. **Clone or download the project**
   ```bash
   cd wallet-system-api
   ```

2. **Run the automated setup script**
   ```bash
   ./setup.sh
   ```

3. **Start the development server**
   ```bash
   php artisan serve
   ```

The API will be available at `http://127.0.0.1:8000`

**Note:** If the automated setup fails at any step, it will show you which step failed and provide manual instructions.

### Option 2: Manual Setup

1. **Clone or download the project**
   ```bash
   cd wallet-system-api
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Set up environment**
   ```bash
   cp .env.example .env  # If .env doesn't exist
   php artisan key:generate
   ```

4. **Create SQLite database**
   ```bash
   touch database/database.sqlite
   ```

5. **Run fresh migrations with seeding**
   ```bash
   php artisan migrate:fresh --seed
   ```
   
   *Note: This drops all existing tables and recreates them with sample data. Use `php artisan migrate` followed by `php artisan db:seed` if you want to preserve existing data.*

7. **Start the development server**
   ```bash
   php artisan serve
   ```

The API will be available at `http://127.0.0.1:8000`

## API Endpoints

### Base URL
```
http://127.0.0.1:8000/api/v1/wallet
```

### 1. Create Account
**POST** `/account`

Creates a new user account with zero balance.

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Account created successfully",
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "balance": "0.00"
    }
}
```

### 2. Top-Up Balance
**POST** `/top-up`

Adds balance to a user account.

**Request Body:**
```json
{
    "user_id": 1,
    "amount": 100.50,
    "reference": "TOP001",
    "description": "Initial deposit"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Top-up successful",
    "data": {
        "transaction_id": 1,
        "user_id": 1,
        "type": "top_up",
        "amount": "100.50",
        "new_balance": "100.50",
        "reference": "TOP001"
    }
}
```

### 3. Charge Balance
**POST** `/charge`

Deducts balance from a user account.

**Request Body:**
```json
{
    "user_id": 1,
    "amount": 25.75,
    "reference": "CHG001",
    "description": "Purchase payment"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Charge successful",
    "data": {
        "transaction_id": 2,
        "user_id": 1,
        "type": "charge",
        "amount": "25.75",
        "new_balance": "74.75",
        "reference": "CHG001"
    }
}
```

### 4. Get Balance
**GET** `/balance/{userId}`

Retrieves the current balance for a user.

**Response:**
```json
{
    "success": true,
    "data": {
        "user_id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "balance": "74.75"
    }
}
```

### 5. Get Transaction History
**GET** `/transactions/{userId}`

Retrieves the transaction history for a user.

**Response:**
```json
{
    "success": true,
    "data": {
        "user_id": 1,
        "transactions": [
            {
                "id": 2,
                "type": "charge",
                "amount": "25.75",
                "reference": "CHG001",
                "description": "Purchase payment",
                "created_at": "2025-09-10T16:05:00.000000Z"
            },
            {
                "id": 1,
                "type": "top_up",
                "amount": "100.50",
                "reference": "TOP001",
                "description": "Initial deposit",
                "created_at": "2025-09-10T16:04:00.000000Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 10,
            "total": 2,
            "last_page": 1
        }
    }
}
```

## Example cURL Commands

**Note:** The API automatically returns JSON responses. The `Accept: application/json` header is optional but recommended for clarity.

### Create Account
```bash
curl -X POST http://127.0.0.1:8000/api/v1/wallet/account \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com"
  }'
```

### Top-Up Balance
```bash
curl -X POST http://127.0.0.1:8000/api/v1/wallet/top-up \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "amount": 100.50,
    "reference": "TOP001",
    "description": "Initial deposit"
  }'
```

### Charge Balance
```bash
curl -X POST http://127.0.0.1:8000/api/v1/wallet/charge \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "amount": 25.75,
    "reference": "CHG001",
    "description": "Purchase payment"
  }'
```

### Get Balance
```bash
curl -X GET http://127.0.0.1:8000/api/v1/wallet/balance/1
```

### Get Transactions
```bash
curl -X GET http://127.0.0.1:8000/api/v1/wallet/transactions/1
```

## Error Handling

The API handles various error scenarios:

### Validation Errors (400)
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "amount": ["The amount must be at least 0.01"]
    }
}
```

### Insufficient Balance (400)
```json
{
    "success": false,
    "message": "Insufficient balance",
    "data": {
        "current_balance": "10.00",
        "requested_amount": "25.00"
    }
}
```

### Duplicate Reference (400)
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "reference": ["The reference has already been taken."]
    }
}
```

### User Not Found (404)
```json
{
    "success": false,
    "message": "User not found"
}
```

## Database Schema

### Users Table
```sql
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    balance DECIMAL(15,2) DEFAULT 0.00,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Transactions Table
```sql
CREATE TABLE transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    type VARCHAR(255) NOT NULL, -- 'top_up' or 'charge'
    amount DECIMAL(15,2) NOT NULL,
    reference VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## Key Features & Edge Case Handling

1. **Decimal Precision**: All monetary values use `DECIMAL(15,2)` for precise currency calculations
2. **Atomic Transactions**: Database transactions with `lockForUpdate()` ensure data consistency
3. **Duplicate Prevention**: Unique reference field prevents duplicate transactions
4. **Negative Balance Prevention**: Validates sufficient balance before charges
5. **Concurrency Safety**: Database locking prevents race conditions in high-traffic scenarios
6. **Input Validation**: Comprehensive Form Request validation for all endpoints
7. **Automatic JSON Responses**: Custom middleware forces JSON responses for all API routes
8. **Error Handling**: Proper HTTP status codes and descriptive error messages
9. **Transaction History**: Complete audit trail with pagination support
10. **Centralized Constants**: All message constants defined in Transaction model for consistency

## Testing

You can test the API using the provided cURL examples or the Postman collection:

### Option 1: cURL Commands
Use the cURL examples provided above in the "Example cURL Commands" section.

### Option 2: Postman Collection
Import the `Wallet_API.postman_collection.json` file into Postman:
1. Open Postman
2. Click "Import" 
3. Select the `Wallet_API.postman_collection.json` file
4. The collection includes:
   - All API endpoints with proper request examples
   - Automated tests for response validation
   - Environment variables for dynamic user IDs
   - Edge case testing scenarios

The Postman collection automatically:
- Sets the base URL to `http://127.0.0.1:8000`
- Captures user ID from account creation for subsequent requests
- Uses random references to prevent duplicate errors
- Includes test assertions for response validation

The SQLite database file will be created at `database/database.sqlite` after running migrations.

## Architecture

- **Laravel 11 Framework**: Modern PHP web application framework
- **API-Only Configuration**: Optimized for JSON API responses only
- **SQLite Database**: Lightweight, file-based database perfect for development
- **Service Layer Pattern**: Business logic separated in WalletService class
- **Form Request Validation**: Dedicated request classes for input validation
- **RESTful API**: Following REST principles with proper HTTP status codes
- **Middleware Optimization**: Custom middleware stack for API-only responses
- **Transaction Safety**: Database locking and atomic transactions for concurrency

## Security Considerations

- Input validation on all endpoints
- SQL injection prevention through Eloquent ORM
- Transaction atomicity for data consistency
- Unique constraints to prevent duplicates
- Proper error handling without exposing sensitive information
- API-only configuration reduces attack surface
- No session or CSRF vulnerabilities for API routes

## Troubleshooting

### API Returns HTML Instead of JSON
If you're getting HTML responses instead of JSON:
- Make sure you're calling `/api/v1/wallet/...` endpoints
- The API has built-in JSON response middleware, so `Accept: application/json` header is optional
- Check that the Laravel server is running with `php artisan serve`

### Database Connection Issues
- Ensure `database/database.sqlite` file exists
- Run `touch database/database.sqlite` if the file is missing
- Check that migrations have been executed: `php artisan migrate`

### Permission Issues
- Ensure the `storage` and `bootstrap/cache` directories are writable
- Run `chmod -R 775 storage bootstrap/cache` if needed

### Validation Errors
- Check that all required fields are included in requests
- Ensure `user_id` exists when making top-up/charge requests
- Use unique references to avoid duplicate transaction errors
