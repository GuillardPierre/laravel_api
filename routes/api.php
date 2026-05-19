<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

Route::post('/register', [UserController::class, 'register']);
Route::middleware('throttle:10,1,')->group(function () {
    Route::post('/login', [UserController::class, 'login']);
});

Route::name('books.')->group(function () {
    Route::get('/books', [BookController::class, 'index'])->name('index');
    Route::get('/books/{book}', [BookController::class, 'show'])->name('show');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [UserController::class, 'logout']);
        Route::post('/books', [BookController::class, 'store'])->name('store');
        Route::put('/books/{book}', [BookController::class, 'update'])->name(
            'update',
        );
        Route::patch('/books/{book}', [BookController::class, 'update'])->name(
            'update',
        );
        Route::delete('/books/{book}', [
            BookController::class,
            'destroy',
        ])->name('destroy');
    });
});
