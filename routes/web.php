<?php

use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::post('/books', [BookController::class, 'store'])->name('books.store');
Route::get('/books/{book}/pages', [BookController::class, 'pages'])->name('books.pages');
