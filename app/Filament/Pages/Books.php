<?php

namespace App\Filament\Pages;

use App\Models\Book;
use App\Models\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page as FilamentPage;
use Illuminate\Support\Facades\Http;
use Spatie\PdfToImage\Pdf;
use Illuminate\Support\Facades\Storage;
use BackedEnum;

class Books extends FilamentPage implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'مدیریت کتاب';

    protected static ?string $title = '';

    protected string $view = 'filament.pages.books';

    public bool $showSummaryModal = false;
    public ?int $summaryBookId = null;
    public string $summaryBookTitle = '';
    public int $fromPage = 1;
    public int $toPage = 1;
    public string $summaryResult = '';

    public string $bookTitle = '';
    public $pdf = null;

    public bool $openDeleteModal = false;
    public ?int $bookIdToDelete = null;


    public function store(): void
    {
        $this->validate([
            'bookTitle' => ['required', 'string', 'max:255'],
            'pdf'       => ['required', 'file', 'mimes:pdf', 'max:51200'],
        ]);

        $response = Http::timeout(30)->post(
            config('services.rag.url') . '/books/create'
        );

        if (! $response->successful()) {
            Notification::make()
                ->title('خطا')
                ->body('Failed to create book in RAG service.')
                ->danger()
                ->send();
            return;
        }

        $ragBookId = $response->json('book_id');

        if (! $ragBookId) {
            Notification::make()
                ->title('خطا')
                ->body('Invalid response from RAG service.')
                ->danger()
                ->send();
            return;
        }

        $book = Book::create([
            'title'       => $this->bookTitle,
            'pdf_path'    => '',
            'rag_book_id' => $ragBookId,
        ]);

        $directory = "books/{$book->id}";
        $pdfPath = "{$directory}/document.pdf";

        Storage::disk('public')->makeDirectory($directory);

        $pdfFullPath = storage_path('app/public/' . $pdfPath);

        $this->pdf->storeAs($directory, 'document.pdf', 'public');

        $book->update([
            'pdf_path' => $pdfPath,
        ]);

        $pdf = new Pdf($pdfFullPath);
        $pageCount = $pdf->pageCount();

        for ($page = 1; $page <= $pageCount; $page++) {

            $relativeImagePath = "books/{$book->id}/page-{$page}.jpg";
            $absoluteImagePath = storage_path("app/public/{$relativeImagePath}");

            $pdf->selectPage($page)->save($absoluteImagePath);

            Page::create([
                'book_id'     => $book->id,
                'page_number' => $page,
                'image_path'  => $relativeImagePath,
            ]);
        }

        Notification::make()
            ->title('موفق')
            ->body('کتاب با موفقیت ذخیره شد.')
            ->success()
            ->send();

        $this->redirect($this->getUrl());
    }

    public function getBooks()
    {
        $books = Book::latest()->get();
        return $books;
    }

    public function deleteBook(int $id): void
    {
        $book = Book::find($id);

        Storage::disk('public')->deleteDirectory(
            "books/{$book->id}"
        );

        $book->pages()->delete();

        $book->delete();

        Notification::make()
            ->title('حذف شد')
            ->success()
            ->send();
    }

    public function openSummaryModal(int $bookId, string $title): void
    {
        $this->summaryBookId = $bookId;
        $this->summaryBookTitle = $title;
        $this->fromPage = 1;
        $this->toPage = 1;
        $this->summaryResult = '';
        $this->showSummaryModal = true;


        $this->dispatch('open-modal', id: 'summary-modal');
    }

    public function closeSummaryModal()
    {
        $this->dispatch('close-modal', id: 'summary-modal');
    }

    public function getSummary(): void
    {
        // منطق دریافت خلاصه اینجا
    }
}
