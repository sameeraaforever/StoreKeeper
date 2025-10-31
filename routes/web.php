<?php

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

Auth::routes();

// Dashboard/Home
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Protected Routes (only authenticated users can access)
Route::middleware(['auth'])->group(function () {

    Route::get('supply-records/locations/{company}', [SupplyRecordController::class, 'getLocations']);
    Route::get('/product-prices/by-product/{productId}', [ProductPriceController::class, 'getByProduct'])
    ->name('product-prices.by-product');


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
