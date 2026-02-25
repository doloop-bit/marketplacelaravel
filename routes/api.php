<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\Vendor\VendorProductController;
use App\Http\Controllers\Api\Vendor\VendorOrderController;
use App\Http\Controllers\Api\Admin\AdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Public Routes
Route::prefix('v1')->group(function () {
    // Public product & category browsing
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{category}', [CategoryController::class, 'show']);
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{product}', [ProductController::class, 'show']);
    Route::get('products/featured', [ProductController::class, 'featured']);
    Route::get('products/search', [ProductController::class, 'search']);

    // Auth routes
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);
});

// Protected Routes (Require Authentication)
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    // Auth
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::put('auth/update-profile', [AuthController::class, 'updateProfile']);
    Route::post('auth/change-password', [AuthController::class, 'changePassword']);

    // Cart (Customer)
    Route::get('cart', [CartController::class, 'index']);
    Route::post('cart/add', [CartController::class, 'addToCart']);
    Route::patch('cart/update/{cartItem}', [CartController::class, 'updateCart']);
    Route::delete('cart/remove/{cartItem}', [CartController::class, 'removeFromCart']);
    Route::post('cart/clear', [CartController::class, 'clearCart']);

    // Orders (Customer)
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);

    // Vendor Routes
    Route::middleware(['role:vendor|admin'])->group(function () {
        // Vendor Products
        Route::get('vendor/products', [VendorProductController::class, 'index']);
        Route::post('vendor/products', [VendorProductController::class, 'store']);
        Route::get('vendor/products/{product}', [VendorProductController::class, 'show']);
        Route::put('vendor/products/{product}', [VendorProductController::class, 'update']);
        Route::delete('vendor/products/{product}', [VendorProductController::class, 'destroy']);
        Route::post('vendor/products/{product}/images', [VendorProductController::class, 'uploadImages']);
        Route::delete('vendor/products/images/{image}', [VendorProductController::class, 'deleteImage']);

        // Vendor Orders
        Route::get('vendor/orders', [VendorOrderController::class, 'index']);
        Route::get('vendor/orders/{order}', [VendorOrderController::class, 'show']);
        Route::patch('vendor/orders/{order}/status', [VendorOrderController::class, 'updateStatus']);
    });

    // Admin Routes
    Route::middleware(['role:admin'])->group(function () {
        Route::get('admin/dashboard', [AdminController::class, 'dashboard']);
        Route::get('admin/users', [AdminController::class, 'users']);
        Route::get('admin/vendors', [AdminController::class, 'vendors']);
        Route::get('admin/products', [AdminController::class, 'products']);
        Route::get('admin/orders', [AdminController::class, 'orders']);
        Route::post('admin/vendors/{user}/approve', [AdminController::class, 'approveVendor']);
        Route::post('admin/vendors/{user}/reject', [AdminController::class, 'rejectVendor']);
    });
});
