# PHP_Laravel12_Get_User_Wise_Data_Using_API

<p align="center">
    <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
    <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" />
    <img src="https://img.shields.io/badge/API-RESTful-0EA5E9?style=for-the-badge&logo=postman&logoColor=white" />
    <img src="https://img.shields.io/badge/User--Wise-Data-9333EA?style=for-the-badge&logo=databricks&logoColor=white" />
    <img src="https://img.shields.io/badge/Database-MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
    <img src="https://img.shields.io/badge/Auth-Demo%20API-16A34A?style=for-the-badge&logo=shield&logoColor=white" />
</p>


## Overview

This project demonstrates how to implement a **User-Wise Data Retrieval API** using **Laravel 12**. Each record is stored with a `created_by` field (customer ID), and the API fetches data based on the provided customer ID. This example is ideal for beginners who want to understand Laravel API basics, database relationships, and filtering data user-wise.

---

## Features

* Laravel 12 fresh installation
* MySQL database configuration
* Notes table with `created_by` (customer ID)
* Manual data insertion using Tinker
* REST API to fetch customer-wise data
* Clean JSON API responses
* Simple and beginner-friendly structure

---

## Folder Structure

```
user-wise-api/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/
│   │           └── NoteController.php
│   └── Models/
│       └── Note.php
├── database/
│   └── migrations/
│       └── xxxx_xx_xx_create_notes_table.php
├── routes/
│   ├── api.php
│   └── web.php
├── .env
├── composer.json
└── README.md
```

---

---

## 1. Laravel 12 Installation

```bash
# Create a new Laravel 12 project
composer create-project laravel/laravel user-wise-api

# Move into project directory
cd user-wise-api

# Start the development server
php artisan serve
```

---

## 2. Database Configuration

### 2.1 Create Database

Create a database manually in MySQL:

```sql
CREATE DATABASE userwise_db;
```

### 2.2 Update .env File

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=userwise_db
DB_USERNAME=root
DB_PASSWORD=
```

---

## 3. Notes Table Migration (created_by = customer id)

### 3.1 Create Migration

```bash
php artisan make:migration create_notes_table
```

### 3.2 Migration File

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('title'); // Note title
            $table->text('description')->nullable(); // Note description
            $table->unsignedBigInteger('created_by'); // Customer ID
            $table->timestamps(); // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes'); // Drop table on rollback
    }
};
```

### 3.3 Run Migration

```bash
php artisan migrate
```

---

## 4. Note Model

### 4.1 Create Model

```bash
php artisan make:model Note
```

### 4.2 Model File

**app/Models/Note.php**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    // Mass assignable fields
    protected $fillable = [
        'title',
        'description',
        'created_by'
    ];
}
```

---

## 5. Insert Data Manually (Using Tinker)

```bash
# Open tinker shell
php artisan tinker
```

```php
use App\Models\Note;

// Insert note for customer 1
Note::create([
    'title' => 'Customer 1 Note',
    'description' => 'First data',
    'created_by' => 1
]);

// Insert note for customer 2
Note::create([
    'title' => 'Customer 2 Note',
    'description' => 'Second data',
    'created_by' => 2
]);

// Insert note for customer 3
Note::create([
    'title' => 'Customer 3 Note',
    'description' => 'More data',
    'created_by' => 2
]);
```

Exit tinker:

```bash
exit
```
<img width="948" height="289" alt="Screenshot 2026-01-13 151809" src="https://github.com/user-attachments/assets/c75b4a94-508d-4327-ad73-4fa6a0740ecc" />

---

## 6. API Controller

### 6.1 Create Controller

```bash
php artisan make:controller Api/NoteController
```

### 6.2 Controller Code

**app/Http/Controllers/Api/NoteController.php**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Note;

class NoteController extends Controller
{
    public function listByCustomer(Request $request)
    {
        // Get customer_id from query string
        $customerId = $request->query('customer_id');

        // Validate customer_id
        if (!$customerId) {
            return response()->json([
                'status' => false,
                'message' => 'customer_id is required'
            ], 400);
        }

        // Fetch notes created by specific customer
        $notes = Note::where('created_by', $customerId)->get();

        // Return JSON response
        return response()->json([
            'status' => true,
            'customer_id' => $customerId,
            'data' => $notes
        ]);
    }
}
```

---

## 7. API Route File

**routes/api.php**

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NoteController;

// Customer-wise notes API
Route::get('/notes', [NoteController::class, 'listByCustomer']);
```

---

## 8. API Testing

### 8.1 API URL

```
GET http://127.0.0.1:8000/api/notes?customer_id=1
```

### 8.2 API Response

```json
{
    "status": true,
    "customer_id": "1",
    "data": [
        {
            "id": 1,
            "title": "Customer 1 Note",
            "description": "First data",
            "created_by": 1,
            "created_at": "2026-01-13T09:25:35.000000Z",
            "updated_at": "2026-01-13T09:25:35.000000Z"
        }
    ]
}
```
<img width="967" height="824" alt="Screenshot 2026-01-13 151733" src="https://github.com/user-attachments/assets/f3da3a55-a927-43b1-8ce9-8907bcb26bea" />

<img width="921" height="815" alt="Screenshot 2026-01-13 154212" src="https://github.com/user-attachments/assets/1f65ae7b-73c9-4a95-a5c4-6f29494a164c" />


---

## 9. Important Notes

* This is a **demo API** using `customer_id` as a request parameter.
* In real applications, use **Laravel Sanctum authentication**.
* Prefer `$request->user()->id` instead of passing customer_id from frontend.

---

