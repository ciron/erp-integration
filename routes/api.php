<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

/**
 * API Routes - Legacy ERP Integration
 * 
 * Part 2: JSON API endpoint for orders
 * Provides RESTful API access to order data
 */

// GET /api/orders - List orders with filtering (JSON response)
Route::get('/orders', [OrderController::class, 'apiIndex'])->name('api.orders.index');

// POST /api/orders/{orderId}/status - Update order status
Route::post('/orders/{orderId}/status', [OrderController::class, 'updateStatus'])->name('api.orders.update.status');
