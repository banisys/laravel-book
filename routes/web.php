<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;
use App\Filament\Pages\Pages;
use App\Filament\Pages\Summaries;

Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::post('/books', [BookController::class, 'store'])->name('books.store');
Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');

// Route::get('/books/{book}/pages', [BookController::class, 'pages'])->name('books.pages');
Route::post('/pages/{page}/process', [PageController::class, 'process'])->name('pages.process');

Route::post('/pages/{page}/rag', [PageController::class, 'addToRag'])->name('pages.rag.add');

Route::delete('/pages/{page}/rag', [PageController::class, 'deleteFromRag'])->name('pages.rag.delete');

Route::post('/books/{book}/summary', [BookController::class, 'summary'])->name('books.summary');


Route::get('/admin/books/{book}/pages', Pages::class)->name('books.pages');



Route::get('/admin/books/{book}/summaries', Summaries::class)->name('books.summaries');
