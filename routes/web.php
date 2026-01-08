<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

Route::get('/', function () {
    return view('welcome');
});

// Part 2: Read-only ERP view (Blade)
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');

// Alternative raw SQL implementation (for demonstration)
Route::get('/orders-raw', [OrderController::class, 'indexRaw'])->name('orders.index.raw');

// Part 3: Safe write operation (status update)
Route::post('/orders/{orderId}/status', [OrderController::class, 'updateStatus'])->name('orders.update.status');

