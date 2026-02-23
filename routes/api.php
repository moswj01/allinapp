<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\CustomerController as WebCustomerController;
use App\Http\Controllers\Api\RepairController;
use App\Http\Controllers\Api\DashboardController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// LINE OA Webhook (public, no auth — must be outside auth group)
Route::post('/line/webhook', [\App\Http\Controllers\LineWebhookController::class, 'webhook']);

// ═══════════════════════════════════════════════════════════════
// All API routes below require authentication + tenant scoping
// ═══════════════════════════════════════════════════════════════
Route::middleware(['auth:sanctum', 'tenant'])->group(function () {

    // Redirect browser requests to web routes
    Route::get('/repairs', function (Request $request) {
        if ($request->wantsJson()) {
            return app(RepairController::class)->index($request);
        }
        return redirect('/repairs');
    });

    Route::get('/products', function (Request $request) {
        if ($request->wantsJson()) {
            return app(ProductController::class)->index($request);
        }
        return redirect('/products');
    });

    Route::get('/customers', function (Request $request) {
        if ($request->wantsJson()) {
            return app(CustomerController::class)->index($request);
        }
        return redirect('/customers');
    });

    // Customer search
    Route::get('/customers/search', [WebCustomerController::class, 'search'])->name('api.customers.search');
    Route::get('/customer-search', [WebCustomerController::class, 'search'])->name('api.customer-search');

    Route::get('/branches', function (Request $request) {
        if ($request->wantsJson()) {
            return app(BranchController::class)->index($request);
        }
        return redirect('/branches');
    });

    Route::get('/categories', function (Request $request) {
        if ($request->wantsJson()) {
            return app(CategoryController::class)->index($request);
        }
        return redirect('/categories');
    });

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // API Resources
    Route::apiResource('categories', CategoryController::class)->except(['index'])->names([
        'store' => 'api.categories.store',
        'show' => 'api.categories.show',
        'update' => 'api.categories.update',
        'destroy' => 'api.categories.destroy',
    ]);
    Route::apiResource('products', ProductController::class)->except(['index'])->names([
        'store' => 'api.products.store',
        'show' => 'api.products.show',
        'update' => 'api.products.update',
        'destroy' => 'api.products.destroy',
    ]);
    Route::apiResource('branches', BranchController::class)->except(['index'])->names([
        'store' => 'api.branches.store',
        'show' => 'api.branches.show',
        'update' => 'api.branches.update',
        'destroy' => 'api.branches.destroy',
    ]);
    Route::apiResource('customers', CustomerController::class)->except(['index'])->names([
        'store' => 'api.customers.store',
        'show' => 'api.customers.show',
        'update' => 'api.customers.update',
        'destroy' => 'api.customers.destroy',
    ]);
    Route::apiResource('repairs', RepairController::class)->except(['index'])->names([
        'store' => 'api.repairs.store',
        'show' => 'api.repairs.show',
        'update' => 'api.repairs.update',
        'destroy' => 'api.repairs.destroy',
    ]);
    Route::patch('/repairs/{repair}/status', [RepairController::class, 'updateStatus']);

    // Stocks
    Route::apiResource('stocks', StockController::class)->names([
        'index' => 'api.stocks.index',
        'store' => 'api.stocks.store',
        'show' => 'api.stocks.show',
        'update' => 'api.stocks.update',
        'destroy' => 'api.stocks.destroy',
    ]);
    Route::get('/stock-movements', [StockController::class, 'movements']);
    Route::post('/stock/in', [StockController::class, 'stockIn']);
    Route::post('/stock/out', [StockController::class, 'stockOut']);
}); // End auth+tenant group
