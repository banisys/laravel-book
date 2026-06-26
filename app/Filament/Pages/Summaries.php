<?php

namespace App\Filament\Pages;

use App\Models\Book;
use App\Models\Summary;
use Filament\Pages\Page as FilamentPage;

class Summaries extends FilamentPage
{
    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.summaries';
    protected static ?string $slug = 'books/{book}/summaries';

    public ?Book $book = null;

    public function mount(Book $book): void
    {
        $this->book = $book;
    }

    public function getTitle(): string
    {
        return "خلاصه‌های کتاب: {$this->book->title}";
    }

    public function getSummaries()
    {
        return Summary::where('book_id', $this->book->id)
            ->orderByDesc('created_at')
            ->get();
    }

    public function deleteSummary(int $summaryId): void
    {
        Summary::where('book_id', $this->book->id)
            ->findOrFail($summaryId)
            ->delete();

        \Filament\Notifications\Notification::make()
            ->title('خلاصه حذف شد')
            ->success()
            ->send();
    }
}
