<?php

namespace App\Filament\Pages;

use App\Models\Book;
use App\Models\Summary;
use Filament\Pages\Page as FilamentPage;
use Livewire\WithPagination;

class Summaries extends FilamentPage
{
    use WithPagination;

    protected static bool $shouldRegisterNavigation = false;
    protected string $view = 'filament.pages.summaries';
    protected static ?string $slug = 'books/{book}/summaries';

    public ?Book $book = null;
    public ?int $editingSummaryId = null;
    public string $editingContent = '';

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
            ->paginate(10);
    }

    public function openEditModal(int $summaryId): void
    {
        $summary = Summary::where('book_id', $this->book->id)
            ->findOrFail($summaryId);

        $this->editingSummaryId = $summary->id;
        $this->editingContent = $summary->content;

        $this->dispatch('open-modal', id: 'edit-summary');
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editingContent' => 'required|string|min:1',
        ]);

        Summary::where('book_id', $this->book->id)
            ->findOrFail($this->editingSummaryId)
            ->update(['content' => $this->editingContent]);

        $this->dispatch('close-modal', id: 'edit-summary');
        $this->editingSummaryId = null;
        $this->editingContent = '';

        \Filament\Notifications\Notification::make()
            ->title('خلاصه ویرایش شد')
            ->success()
            ->send();
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