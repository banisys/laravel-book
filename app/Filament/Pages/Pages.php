<?php

namespace App\Filament\Pages;

use App\Models\Book;
use Filament\Pages\Page;

class Pages extends Page
{

    protected static bool $shouldRegisterNavigation = false;
    
    // ⚠️ اینجا static نباید باشد - فقط protected string
    protected string $view = 'filament.pages.pages';
    
    public ?Book $book = null;
    
    public function mount(Book $book): void
    {
        $this->book = $book;
    }
    
    public function getTitle(): string
    {
        return $this->book ? "صفحات کتاب: {$this->book->title}" : 'صفحات کتاب';
    }
}