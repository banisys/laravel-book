<?php

namespace App\Filament\Pages;

use App\Models\Book;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use BackedEnum;
use Livewire\WithPagination;

class UserBooks extends Page
{
    use WithPagination;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'کتاب‌های کاربر';
    protected static ?string $title = 'مدیریت کتاب‌های کاربر';
    protected static bool $shouldRegisterNavigation = false;
    protected string $view = 'filament.pages.user-books';

    protected static ?string $slug = 'user-books';

    public int $userId;
    public string $search = '';

    public function mount(): void
    {
        $this->userId = request('user');
    }

    public function getUser(): User
    {
        return User::with('books')->findOrFail($this->userId);
    }

    public function getBooks()
    {
        return Book::when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->orderBy('id')
            ->paginate(10);
    }

    public function isAttached(int $bookId): bool
    {
        return $this->getUser()->books()->where('book_id', $bookId)->exists();
    }

    public function attach(int $bookId): void
    {
        $this->getUser()->books()->syncWithoutDetaching([$bookId]);

        Notification::make()
            ->title('کتاب اضافه شد.')
            ->success()
            ->send();
    }

    public function detach(int $bookId): void
    {
        $this->getUser()->books()->detach($bookId);

        Notification::make()
            ->title('ارتباط حذف شد.')
            ->success()
            ->send();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }
}
