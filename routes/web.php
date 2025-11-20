<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Inventory\InventoryAdjustmentController;
use App\Http\Controllers\Inventory\PurchaseOrderController;
use App\Http\Controllers\Inventory\QuickMovementController;
use App\Http\Controllers\Inventory\RestockTemplateController;
use App\Http\Controllers\Inventory\StockCountController;
use App\Http\Controllers\Inventory\StockRequestController;
use App\Http\Controllers\Inventory\StockTransferController;
use App\Http\Controllers\MasterData\BranchController;
use App\Http\Controllers\MasterData\BranchSettingController;
use App\Http\Controllers\MasterData\ProductController;
use App\Http\Controllers\MasterData\ProductUnitController;
use App\Http\Controllers\MasterData\PromotionController;
use App\Http\Controllers\MasterData\SupplierController;
use App\Http\Controllers\POS\CashierShiftController;
use App\Http\Controllers\POS\PosController;
use App\Http\Controllers\Login\UserController;
use App\Http\Controllers\Login\AuthController;
use App\Http\Controllers\Login\UserManagementController;
use App\Http\Controllers\AutomationController;
use App\Http\Controllers\SyncController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\Login\AuditLogController;
use App\Http\Controllers\Web\AuthSessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthSessionController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::view('/', 'dashboard.selector');
    Route::view('/dashboard/main', 'dashboard.main');
    Route::view('/dashboard/branch', 'dashboard.branch');
    Route::view('/audit', 'audit.index');
    Route::view('/integrations/logs', 'integrations.logs');
    Route::view('/inventory/restock', 'inventory.restock');
    Route::view('/pos/console', 'pos.console');
    Route::view('/pos/sales/quick', 'pos.quick-sale');
    Route::view('/pos/sales', 'pos.history');
});

Route::middleware(['auth', 'super-admin'])->group(function () {
    Route::get('/login/users', [UserManagementController::class, 'index'])->name('login.users.index');
        Route::post('/login/users', [UserManagementController::class, 'store'])->name('login.users.store');
        Route::put('/login/users/{user}', [UserManagementController::class, 'update'])->name('login.users.update');
        Route::delete('/login/users/{user}', [UserManagementController::class, 'destroy'])->name('login.users.destroy');
    Route::view('/inventory/restock-template', 'inventory.restock-template');
});

Route::prefix('api')->group(function () {
    Route::middleware(['auth', 'super-admin'])->group(function () {
        Route::get('users', [UserController::class, 'index']);
        Route::get('users/{user}', [UserController::class, 'show']);
        Route::put('users/{user}', [UserController::class, 'update']);
        Route::delete('users/{user}', [UserController::class, 'destroy']);
        Route::post('users/{user}/force-password-reset', [UserController::class, 'forcePasswordReset']);
        Route::get('audit/logs', [AuditLogController::class, 'index']);
        Route::post('integrations/erp/configure', [IntegrationController::class, 'configure']);
        Route::post('integrations/erp/sales', [IntegrationController::class, 'pushSales']);
        Route::post('integrations/erp/inventory', [IntegrationController::class, 'pushInventory']);
        Route::get('integrations/logs', [IntegrationController::class, 'logs']);
        Route::get('restock/templates', [RestockTemplateController::class, 'index']);
        Route::post('restock/templates', [RestockTemplateController::class, 'store']);
        Route::patch('restock/templates/{restockTemplateItem}', [RestockTemplateController::class, 'update']);
        Route::delete('restock/templates/{restockTemplateItem}', [RestockTemplateController::class, 'destroy']);
        Route::get('restock/products', [RestockTemplateController::class, 'products']);
    });

    Route::middleware(['auth'])->group(function () {
        Route::get('sync/export', [SyncController::class, 'export']);
        Route::post('sync/import', [SyncController::class, 'import']);
        Route::get('restock/options', [RestockTemplateController::class, 'options']);
        Route::get('restock/bootstrap', [RestockTemplateController::class, 'bootstrap']);
        Route::get('restock/suppliers', [SupplierController::class, 'dropdown']);
        Route::get('branches', [BranchController::class, 'index']);
    });


    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('request-reset', [AuthController::class, 'requestPasswordReset']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);

        Route::middleware(['auth'])->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
        });

        Route::post('register', [AuthController::class, 'register']);
    });

    Route::middleware(['auth', 'super-admin'])->group(function () {
        Route::apiResource('branches', BranchController::class)->except(['index']);
        Route::get('branches/{branch}/settings', [BranchSettingController::class, 'index']);
        Route::post('branches/{branch}/settings', [BranchSettingController::class, 'store']);

        Route::apiResource('suppliers', SupplierController::class);
        Route::apiResource('product-units', ProductUnitController::class)->only(['index', 'store']);
        Route::apiResource('products', ProductController::class)->except(['destroy', 'index', 'show']);
        Route::apiResource('promotions', PromotionController::class)->only(['index', 'store', 'update']);
    });

    Route::middleware(['auth', 'branch.context'])->group(function () {
        Route::get('products', [ProductController::class, 'index']);
        Route::get('products/{product}', [ProductController::class, 'show']);

        Route::get('purchase-orders', [PurchaseOrderController::class, 'index']);
        Route::post('purchase-orders', [PurchaseOrderController::class, 'store']);
        Route::post('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive']);

        Route::post('inventory/restock/quick', [QuickMovementController::class, 'restock']);

        Route::post('automation/reorder', [AutomationController::class, 'reorder']);

        Route::get('stock-requests', [StockRequestController::class, 'index']);
        Route::post('stock-requests', [StockRequestController::class, 'store']);
        Route::post('stock-requests/{stockRequest}/approve', [StockRequestController::class, 'approve']);

        Route::get('stock-transfers', [StockTransferController::class, 'index']);
        Route::post('stock-transfers', [StockTransferController::class, 'store']);
        Route::post('stock-transfers/{stockTransfer}/dispatch', [StockTransferController::class, 'dispatch']);
        Route::post('stock-transfers/{stockTransfer}/receive', [StockTransferController::class, 'receive']);

        Route::get('inventory-adjustments', [InventoryAdjustmentController::class, 'index']);
        Route::post('inventory-adjustments', [InventoryAdjustmentController::class, 'store']);

        Route::get('stock-counts', [StockCountController::class, 'index']);
        Route::post('stock-counts', [StockCountController::class, 'store']);
        Route::post('stock-counts/{stockCount}/close', [StockCountController::class, 'close']);
    });

    Route::middleware(['auth', 'branch.context'])->group(function () {
        Route::get('pos/sales', [PosController::class, 'index']);
        Route::post('pos/sales', [PosController::class, 'sale']);
        Route::post('pos/sales/quick', [PosController::class, 'quickSale']);
        Route::get('pos/products', [PosController::class, 'products']);
        Route::get('pos/overview', [PosController::class, 'overview']);
        Route::get('pos/shifts', [CashierShiftController::class, 'index']);
        Route::post('pos/shifts/open', [CashierShiftController::class, 'open']);
        Route::post('pos/shifts/{cashierShift}/close', [CashierShiftController::class, 'close']);
    });

    Route::middleware(['auth', 'branch.context'])->group(function () {
        Route::get('analytics/sales-mix', [AnalyticsController::class, 'salesMix']);
        Route::get('analytics/sales-mix/export', [AnalyticsController::class, 'salesMixExport']);
        Route::get('analytics/stock-aging', [AnalyticsController::class, 'stockAging']);
        Route::get('analytics/stock-aging/export', [AnalyticsController::class, 'stockAgingExport']);
        Route::get('analytics/demand-forecast', [AnalyticsController::class, 'demandForecast']);
        Route::get('analytics/demand-forecast/export', [AnalyticsController::class, 'demandForecastExport']);
        Route::get('analytics/alerts', [AnalyticsController::class, 'alerts']);
    });

    Route::post('context/switch-branch', [\App\Http\Controllers\ContextController::class, 'switchBranch'])
        ->middleware('auth');
});
