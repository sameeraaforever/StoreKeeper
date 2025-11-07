<?php

use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplyRecordController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductPriceController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('admin')->get('/admin-test', function () {
    return 'Admin Access Confirmed';
});

Auth::routes();

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::post('users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });

// Dashboard/Home
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Protected Routes (only authenticated users can access)
Route::middleware(['auth'])->group(function () {

    Route::get('supply-records/locations/{company}', [SupplyRecordController::class, 'getLocations']);
    Route::get('/product-prices/by-product/{productId}', [ProductPriceController::class, 'getByProduct'])
    ->name('product-prices.by-product');
    Route::post('/products/stock-in', [ProductController::class, 'stockIn'])->name('products.stock.in');
    Route::post('/products/stock-out', [ProductController::class, 'stockOut'])->name('products.stock.out');
    Route::get('/product-prices/get-by-product/{productId}', [ProductPriceController::class, 'getByProduct'])
    ->name('product-prices.get-by-product');
    Route::get('/products/{product}/stock', [ProductController::class, 'getStock'])
    ->name('products.stock');

    // ✅ Companies CRUD
    Route::resource('companies', CompanyController::class);

    // ✅ Locations CRUD
    Route::resource('categories', CategoryController::class);

    // ✅ Locations CRUD
    Route::resource('locations', LocationController::class);

    // ✅ Products CRUD
    Route::resource('products', ProductController::class);

    // ✅ Product Prices CRUD
    Route::resource('product-prices', ProductPriceController::class);

    // ✅ Supply Records CRUD
    Route::resource('supply-records', SupplyRecordController::class);

    // ✅ Audit Logs
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    

});
