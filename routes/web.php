<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RepairController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\BranchOrderController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\StockTakeController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\AccountsReceivableController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReceiptTemplateController;
use App\Http\Controllers\LineWebhookController;
use App\Http\Controllers\TenantRegistrationController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\TenantController;
use App\Http\Controllers\SuperAdmin\PlanController;

// =====================================================
// SaaS Landing & Registration (Public)
// =====================================================
Route::get('/', [TenantRegistrationController::class, 'landing'])->name('landing');
Route::get('/register', [TenantRegistrationController::class, 'showRegistration'])->name('register');
Route::post('/register', [TenantRegistrationController::class, 'register']);
Route::post('/check-slug', [TenantRegistrationController::class, 'checkSlug'])->name('check-slug');

// Public Tracking (no auth)
Route::get('/track/{repair_number}', [RepairController::class, 'track'])->name('repairs.track');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// =====================================================
// Super Admin Routes
// =====================================================
Route::middleware(['auth', 'superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('tenants', TenantController::class);
    Route::patch('/tenants/{tenant}/suspend', [TenantController::class, 'suspend'])->name('tenants.suspend');
    Route::patch('/tenants/{tenant}/activate', [TenantController::class, 'activate'])->name('tenants.activate');
    Route::post('/tenants/{tenant}/login-as', [TenantController::class, 'loginAs'])->name('tenants.login-as');
    Route::get('/switch-back', [TenantController::class, 'switchBack'])->name('switch-back');
    Route::resource('plans', PlanController::class);
});

// =====================================================
// Tenant Protected Routes
// =====================================================
Route::middleware(['auth', 'tenant'])->group(function () {
    // Billing / Subscription
    Route::get('/billing', [TenantRegistrationController::class, 'billing'])->name('billing');
    Route::post('/billing/change-plan', [TenantRegistrationController::class, 'changePlan'])->name('billing.change-plan');
});

// Protected Routes
Route::middleware(['auth', 'tenant'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');

    // Repairs
    Route::get('/repairs', [RepairController::class, 'index'])->name('repairs.index');
    Route::get('/repairs/create', [RepairController::class, 'create'])->name('repairs.create');
    Route::get('/repairs/part-approvals', [RepairController::class, 'partApprovals'])->name('repairs.part-approvals');
    Route::get('/repairs/part-report', [RepairController::class, 'partReport'])->name('repairs.part-report');
    Route::get('/repairs-export-csv', [RepairController::class, 'exportCsv'])->name('repairs.export-csv');
    Route::post('/repairs-import-csv', [RepairController::class, 'importCsv'])->name('repairs.import-csv');
    Route::get('/repairs-import-template', [RepairController::class, 'downloadTemplate'])->name('repairs.import-template');

    // Reports
    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/repairs', [ReportController::class, 'repairs'])->name('reports.repairs');
    Route::post('/repairs', [RepairController::class, 'store'])->name('repairs.store');
    Route::get('/repairs/{repair}', [RepairController::class, 'show'])->name('repairs.show');
    Route::get('/repairs/{repair}/receipt', [RepairController::class, 'receipt'])->name('repairs.receipt');
    Route::get('/repairs/{repair}/invoice', [RepairController::class, 'invoice'])->name('repairs.invoice');
    Route::get('/repairs/{repair}/edit', [RepairController::class, 'edit'])->name('repairs.edit');
    Route::put('/repairs/{repair}', [RepairController::class, 'update'])->name('repairs.update');
    Route::delete('/repairs/{repair}', [RepairController::class, 'destroy'])->name('repairs.destroy');
    Route::patch('/repairs/{repair}/status', [RepairController::class, 'updateStatus'])->name('repairs.status');
    Route::post('/repairs/{repair}/technician', [RepairController::class, 'assignTechnician'])->name('repairs.assign');
    Route::post('/repairs/{repair}/parts', [RepairController::class, 'addPart'])->name('repairs.add-part');
    Route::delete('/repairs/{repair}/parts/{part}', [RepairController::class, 'cancelPart'])->name('repairs.cancel-part');
    Route::post('/repairs/{repair}/payment', [RepairController::class, 'payment'])->name('repairs.payment');
    Route::post('/repairs/kanban', [RepairController::class, 'kanbanUpdate'])->name('repairs.kanban');
    Route::post('/repairs/parts/{repairPart}/approve', [RepairController::class, 'approvePart'])->name('repairs.approve-part');
    Route::post('/repairs/parts/{repairPart}/reject', [RepairController::class, 'rejectPart'])->name('repairs.reject-part');

    // Products
    Route::resource('products', ProductController::class);
    Route::post('/products/{product}/adjust-stock', [ProductController::class, 'adjustStock'])->name('products.adjust-stock');
    Route::get('/api/products/barcode', [ProductController::class, 'findByBarcode'])->name('api.products.barcode');
    Route::post('/products-import-csv', [ProductController::class, 'importCsv'])->name('products.import-csv');
    Route::get('/products-import-template', [ProductController::class, 'downloadTemplate'])->name('products.import-template');
    Route::get('/products-export-csv', [ProductController::class, 'exportCsv'])->name('products.export-csv');

    // Categories
    Route::resource('categories', CategoryController::class);
    Route::get('/categories-export-csv', [CategoryController::class, 'exportCsv'])->name('categories.export-csv');
    Route::post('/categories-import-csv', [CategoryController::class, 'importCsv'])->name('categories.import-csv');
    Route::get('/categories-import-template', [CategoryController::class, 'downloadTemplate'])->name('categories.import-template');

    // Suppliers
    Route::resource('suppliers', SupplierController::class);

    // Customers
    Route::resource('customers', CustomerController::class);
    Route::get('/customers-export-csv', [CustomerController::class, 'exportCsv'])->name('customers.export-csv');
    Route::post('/customers-import-csv', [CustomerController::class, 'importCsv'])->name('customers.import-csv');
    Route::get('/customers-import-template', [CustomerController::class, 'downloadTemplate'])->name('customers.import-template');

    // Sales / POS
    Route::get('/pos', [SaleController::class, 'pos'])->name('pos');
    Route::resource('sales', SaleController::class);
    Route::patch('/sales/{sale}/status', [SaleController::class, 'updateStatus'])->name('sales.updateStatus');
    Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');

    // Admin Routes
    Route::middleware(\App\Http\Middleware\CheckPermission::class . ':users.*')->group(function () {
        Route::resource('users', UserController::class);
    });

    Route::middleware(\App\Http\Middleware\CheckPermission::class . ':branches.*')->group(function () {
        Route::resource('branches', BranchController::class);
    });

    // Settings
    Route::middleware(\App\Http\Middleware\CheckPermission::class . ':settings.*')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

        // Receipt Templates
        Route::get('/settings/receipt-templates', [ReceiptTemplateController::class, 'index'])->name('receipt-templates.index');
        Route::put('/settings/receipt-templates', [ReceiptTemplateController::class, 'update'])->name('receipt-templates.update');
        Route::get('/settings/receipt-templates/preview-repair', [ReceiptTemplateController::class, 'previewRepair'])->name('receipt-templates.preview-repair');
        Route::get('/settings/receipt-templates/preview-sales', [ReceiptTemplateController::class, 'previewSales'])->name('receipt-templates.preview-sales');

        // LINE OA Chatbot
        Route::get('/settings/line-oa', [LineWebhookController::class, 'settings'])->name('line-oa.index');
        Route::put('/settings/line-oa', [LineWebhookController::class, 'updateSettings'])->name('line-oa.update');
        Route::post('/settings/line-oa/test', [LineWebhookController::class, 'testConnection'])->name('line-oa.test');
    });

    // Roles & Permissions
    Route::middleware(\App\Http\Middleware\CheckPermission::class . ':roles.*')->group(function () {
        Route::get('/roles', [RolePermissionController::class, 'index'])->name('roles.index');
        Route::get('/roles/create', [RolePermissionController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RolePermissionController::class, 'store'])->name('roles.store');
        Route::get('/roles/{role}/edit', [RolePermissionController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RolePermissionController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RolePermissionController::class, 'destroy'])->name('roles.destroy');
    });

    // Branch Orders (สั่งซื้อจากสาขาใหญ่)
    Route::get('/branch-orders', [BranchOrderController::class, 'index'])->name('branch-orders.index');
    Route::get('/branch-orders/create', [BranchOrderController::class, 'create'])->name('branch-orders.create');
    Route::post('/branch-orders', [BranchOrderController::class, 'store'])->name('branch-orders.store');
    Route::get('/branch-orders/{branchOrder}', [BranchOrderController::class, 'show'])->name('branch-orders.show');
    Route::post('/branch-orders/{branchOrder}/approve', [BranchOrderController::class, 'approve'])->name('branch-orders.approve');
    Route::post('/branch-orders/{branchOrder}/ship', [BranchOrderController::class, 'ship'])->name('branch-orders.ship');
    Route::post('/branch-orders/{branchOrder}/receive', [BranchOrderController::class, 'receive'])->name('branch-orders.receive');
    Route::post('/branch-orders/{branchOrder}/cancel', [BranchOrderController::class, 'cancel'])->name('branch-orders.cancel');
    Route::get('/branch-orders/{branchOrder}/print', [BranchOrderController::class, 'print'])->name('branch-orders.print');

    // Warehouse / Stocks Management (Web view)
    Route::get('/stocks', function () {
        $user = \Illuminate\Support\Facades\Auth::user();
        $isAdmin = $user->isOwner() || $user->isAdmin();
        $userBranchId = $user->branch_id;
        $userBranchName = $user->branch?->name ?? '';
        return view('stocks.index', compact('isAdmin', 'userBranchId', 'userBranchName'));
    })->name('stocks.index');

    // Quotations (ใบเสนอราคา)
    Route::resource('quotations', QuotationController::class);
    Route::patch('/quotations/{quotation}/status', [QuotationController::class, 'updateStatus'])->name('quotations.update-status');
    Route::get('/quotations/{quotation}/print', [QuotationController::class, 'print'])->name('quotations.print');

    // Purchase Orders (ใบสั่งซื้อ)
    Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
    Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
    Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
    Route::post('/purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
    Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
    Route::get('/purchase-orders/{purchaseOrder}/print', [PurchaseOrderController::class, 'print'])->name('purchase-orders.print');

    // Goods Receipts (รับสินค้า)
    Route::get('/goods-receipts', [GoodsReceiptController::class, 'index'])->name('goods-receipts.index');
    Route::get('/goods-receipts/create', [GoodsReceiptController::class, 'create'])->name('goods-receipts.create');
    Route::post('/goods-receipts', [GoodsReceiptController::class, 'store'])->name('goods-receipts.store');
    Route::get('/goods-receipts/{goodsReceipt}', [GoodsReceiptController::class, 'show'])->name('goods-receipts.show');

    // Stock Takes (ตรวจนับสต๊อก)
    Route::get('/stock-takes', [StockTakeController::class, 'index'])->name('stock-takes.index');
    Route::get('/stock-takes/create', [StockTakeController::class, 'create'])->name('stock-takes.create');
    Route::post('/stock-takes', [StockTakeController::class, 'store'])->name('stock-takes.store');
    Route::get('/stock-takes/{stockTake}', [StockTakeController::class, 'show'])->name('stock-takes.show');
    Route::post('/stock-takes/{stockTake}/start', [StockTakeController::class, 'start'])->name('stock-takes.start');
    Route::post('/stock-takes/{stockTake}/update-counts', [StockTakeController::class, 'updateCounts'])->name('stock-takes.update-counts');
    Route::post('/stock-takes/{stockTake}/complete', [StockTakeController::class, 'complete'])->name('stock-takes.complete');
    Route::post('/stock-takes/{stockTake}/approve', [StockTakeController::class, 'approve'])->name('stock-takes.approve');

    // Stock Transfers (โอนสต๊อก)
    Route::get('/stock-transfers', [StockTransferController::class, 'index'])->name('stock-transfers.index');
    Route::get('/stock-transfers/create', [StockTransferController::class, 'create'])->name('stock-transfers.create');
    Route::post('/stock-transfers', [StockTransferController::class, 'store'])->name('stock-transfers.store');
    Route::get('/stock-transfers/{stockTransfer}', [StockTransferController::class, 'show'])->name('stock-transfers.show');
    Route::post('/stock-transfers/{stockTransfer}/approve', [StockTransferController::class, 'approve'])->name('stock-transfers.approve');
    Route::post('/stock-transfers/{stockTransfer}/ship', [StockTransferController::class, 'ship'])->name('stock-transfers.ship');
    Route::post('/stock-transfers/{stockTransfer}/receive', [StockTransferController::class, 'receive'])->name('stock-transfers.receive');
    Route::post('/stock-transfers/{stockTransfer}/cancel', [StockTransferController::class, 'cancel'])->name('stock-transfers.cancel');

    // Accounts Receivable (บัญชีลูกหนี้)
    Route::get('/accounts-receivable', [AccountsReceivableController::class, 'index'])->name('accounts-receivable.index');
    Route::get('/accounts-receivable/{accountsReceivable}', [AccountsReceivableController::class, 'show'])->name('accounts-receivable.show');
    Route::post('/accounts-receivable/{accountsReceivable}/payment', [AccountsReceivableController::class, 'addPayment'])->name('accounts-receivable.add-payment');

    // Finance - Petty Cash (เงินสดย่อย)
    Route::get('/finance/petty-cash', [FinanceController::class, 'pettyCashIndex'])->name('finance.petty-cash.index');
    Route::get('/finance/petty-cash/create', [FinanceController::class, 'pettyCashCreate'])->name('finance.petty-cash.create');
    Route::post('/finance/petty-cash', [FinanceController::class, 'pettyCashStore'])->name('finance.petty-cash.store');

    // Finance - Daily Settlement (ปิดยอดประจำวัน)
    Route::get('/finance/daily-settlement', [FinanceController::class, 'dailySettlementIndex'])->name('finance.daily-settlement.index');
    Route::get('/finance/daily-settlement/create', [FinanceController::class, 'dailySettlementCreate'])->name('finance.daily-settlement.create');
    Route::post('/finance/daily-settlement', [FinanceController::class, 'dailySettlementStore'])->name('finance.daily-settlement.store');
    Route::get('/finance/daily-settlement/{dailySettlement}', [FinanceController::class, 'dailySettlementShow'])->name('finance.daily-settlement.show');
    Route::post('/finance/daily-settlement/{dailySettlement}/approve', [FinanceController::class, 'dailySettlementApprove'])->name('finance.daily-settlement.approve');

    // Audit Logs (ประวัติการใช้งาน)
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');

    // Notifications (การแจ้งเตือน)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Reports (Extended)
    Route::get('/reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
    Route::get('/reports/finance', [ReportController::class, 'finance'])->name('reports.finance');
    Route::get('/reports/purchasing', [ReportController::class, 'purchasing'])->name('reports.purchasing');
});