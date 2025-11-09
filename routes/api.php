<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\LoanController;
use App\Http\Controllers\Api\SubscriptionController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

    //Login
    Route::post('register', [TokenController::class, 'register']);
    Route::post('login', [TokenController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [TokenController::class, 'logout']);

        //Books
        Route::get('books', [BookController::class, 'index']);
        Route::post('books', [BookController::class, 'store']);
        Route::get('books/{id}', [BookController::class, 'show']);
        Route::put('books/{id}', [BookController::class, 'update']);
        Route::delete('books/{id}', [BookController::class, 'destroy']);

        //Loans
        Route::get('loans', [LoanController::class, 'index']);
        Route::post('loans', [LoanController::class, 'store']);
        Route::put('loans/{id}/return', [LoanController::class, 'returnBook']);

        //Subscriptions
        Route::get('subscriptions', [SubscriptionController::class, 'index']);
        Route::post('subscriptions/subscribe', [SubscriptionController::class, 'subscribe']);
        Route::post('subscriptions/cancel', [SubscriptionController::class, 'cancel']);
        Route::post('subscriptions/stripe', [SubscriptionController::class, 'handleStripeWebhook']);
    });


