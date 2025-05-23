<?php

use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InvoiceItemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('invoices/create', [InvoiceController::class, 'create']);
Route::get('invoices/{id}', [InvoiceController::class, 'show']);
Route::get('invoices', [InvoiceController::class, 'index']);
Route::post('invoices', [InvoiceController::class, 'store']);
Route::get('search-invoice', [InvoiceController::class, 'search']);
Route::get('invoices/{id}/edit', [InvoiceController::class, 'edit']);
Route::put('invoices/{id}', [InvoiceController::class, 'update']);
Route::delete('invoices/{id}', [InvoiceController::class, 'destroy']);
Route::post('send-ai', [InvoiceController::class, 'sendAi']);
Route::post('chat-ai', [InvoiceController::class, 'chatAi']);

Route::resource('invoice-items', InvoiceItemController::class);
Route::resource('customers', CustomerController::class);
Route::resource('products', ProductController::class);
