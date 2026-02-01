<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RepairController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\UserController;

// Authentication Routes
// Public Tracking (no auth)
Route::get('/track/{repair_number}', [RepairController::class, 'track'])->name('repairs.track');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Profile
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');

    // Repairs (Kanban)
    Route::get('/repairs', [RepairController::class, 'index'])->name('repairs.index');
    Route::get('/repairs/create', [RepairController::class, 'create'])->name('repairs.create');
    Route::post('/repairs', [RepairController::class, 'store'])->name('repairs.store');
    Route::get('/repairs/{repair}', [RepairController::class, 'show'])->name('repairs.show');
    Route::get('/repairs/{repair}/receipt', [RepairController::class, 'receipt'])->name('repairs.receipt');
    Route::get('/repairs/{repair}/edit', [RepairController::class, 'edit'])->name('repairs.edit');
    Route::put('/repairs/{repair}', [RepairController::class, 'update'])->name('repairs.update');
    Route::patch('/repairs/{repair}/status', [RepairController::class, 'updateStatus'])->name('repairs.status');
    Route::post('/repairs/{repair}/technician', [RepairController::class, 'assignTechnician'])->name('repairs.assign');
    Route::post('/repairs/{repair}/parts', [RepairController::class, 'addPart'])->name('repairs.add-part');
    Route::post('/repairs/{repair}/payment', [RepairController::class, 'payment'])->name('repairs.payment');
    Route::post('/repairs/kanban', [RepairController::class, 'kanbanUpdate'])->name('repairs.kanban');

    // Products
    Route::resource('products', ProductController::class);
    Route::post('/products/{product}/adjust-stock', [ProductController::class, 'adjustStock'])->name('products.adjust-stock');
    Route::get('/api/products/barcode', [ProductController::class, 'findByBarcode'])->name('api.products.barcode');

    // Parts
    Route::resource('parts', PartController::class);

    // Categories
    Route::resource('categories', CategoryController::class);

    // Customers
    Route::resource('customers', CustomerController::class);

    // Sales / POS
    Route::get('/pos', [SaleController::class, 'pos'])->name('pos');
    Route::resource('sales', SaleController::class);
    Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');

    // Admin Routes
    Route::middleware('permission:users.*')->group(function () {
        Route::resource('users', UserController::class);
    });

    Route::middleware('permission:branches.*')->group(function () {
        Route::resource('branches', BranchController::class);
    });

    // Warehouse / Stocks Management (Web view)
    Route::get('/stocks', function () {
        return view('stocks.index');
    })->name('stocks.index');
});
