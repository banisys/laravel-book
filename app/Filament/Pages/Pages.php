<?php

namespace App\Filament\Pages;

use App\Models\Book;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Summary;


class Pages extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.pages';

    public ?Book $book = null;
    public int $currentPage = 1;
    public int $perPage = 30;
    public string $searchPage = '';
    public int $fromPage = 1;
    public int $toPage = 1;
    public string $summaryResult = '';

    public function mount(Book $book): void
    {
        $this->book = $book->load('pages');
    }

    public function getTitle(): string
    {
        return $this->book ? "صفحات کتاب: {$this->book->title}" : 'صفحات کتاب';
    }

    public function processPage(int $pageId): void
    {
        $page = $this->book->pages->find($pageId);

        if (! $page) {
            return;
        }

        $image = Storage::disk('public')->get($page->image_path);
        $base64 = base64_encode($image);

        $response = Http::timeout(160)->withToken(env('GAPGPT_API_KEY'))
            ->post('https://api.gapgpt.app/v1/responses', [
                'model' => 'gapgpt-qwen-3.5',
                'input' => [
                    [
                        'role' => 'user',
                        'content' => [
                            // [
                            //     'type' => 'input_image',
                            //     'image_url' => "data:image/jpeg;base64,{$base64}",
                            // ],
                            [
                                'type' => 'input_image',
                                'image_url' => [
                                    'url' => "data:image/jpeg;base64,{$base64}",
                                ],
                            ],
                            [
                                'type' => 'input_text',
                                'text' => '
                                    متن موجود در این تصویر را با بالاترین دقت استخراج کن.
                                    قوانین:
                                    - فقط متن داخل تصویر را خروجی بده و هیچ توضیح اضافه‌ای ننویس.
                                    - متن را به زبان اصلی تصویر (فارسی) حفظ کن.
                                    - ترتیب خطوط، پاراگراف‌ها و ساختار متن را تا حد ممکن حفظ کن.
                                    - اگر تیتر، جدول یا لیست وجود دارد، ساختار آن را حفظ کن.
                                    - هیچ کلمه‌ای را خلاصه، اصلاح یا بازنویسی نکن.
                                    - اگر بخشی از متن خوانا نیست، آن را با [نامشخص] مشخص کن.
                                    - علائم نگارشی فارسی را حفظ کن.
                                    - اعداد فارسی و انگلیسی را همان‌طور که در تصویر هستند نگه دار.
                                    خروجی فقط متن استخراج‌شده باشد.
                                ',
                            ],
                        ],
                    ],
                ],
            ]);

        // dd($response->json());

        $text = data_get($response->json(), 'output.0.content.0.text', '');

        $page->update(['content' => $text]);

        $this->book->load('pages');

        Notification::make()
            ->title('متن صفحه دریافت شد')
            ->success()
            ->send();
    }

    public function addToRag(int $pageId): void
    {
        $page = $this->book->pages->find($pageId);

        if (! $page) {
            return;
        }

        $response = Http::timeout(60)
            ->post(config('services.rag.url') . '/books/add-text', [
                'book_id' => $page->book->rag_book_id,
                'page'    => $page->page_number,
                'text'    => $page->content,
            ]);

        $response->throw();

        $page->update(['is_synced_to_rag' => true]);

        $this->book->load('pages');

        Notification::make()
            ->title('صفحه به RAG افزوده شد')
            ->success()
            ->send();
    }

    public function deleteFromRag(int $pageId): void
    {
        $page = $this->book->pages->find($pageId);

        if (! $page) {
            return;
        }

        Http::delete(config('services.rag.url') . '/books/delete-page', [
            'book_id' => $page->book->rag_book_id,
            'page'    => $page->page_number,
        ])->throw();

        $page->update(['is_synced_to_rag' => false]);

        $this->book->load('pages');

        Notification::make()
            ->title('صفحه از RAG حذف شد')
            ->success()
            ->send();
    }

    public function updateContent(int $pageId, string $content): void
    {
        $page = $this->book->pages->find($pageId);

        if (! $page) {
            return;
        }

        $page->update(['content' => $content]);

        $this->book->load('pages');

        Notification::make()
            ->title('متن با موفقیت ذخیره شد')
            ->success()
            ->send();
    }

    public function getFilteredPages()
    {
        return $this->book->pages
            ->when($this->searchPage !== '', fn($pages) => $pages->where('page_number', (int) $this->searchPage))
            ->forPage($this->currentPage, $this->perPage);
    }

    public function getTotalPages(): int
    {
        return (int) ceil(
            $this->book->pages
                ->when($this->searchPage !== '', fn($pages) => $pages->where('page_number', (int) $this->searchPage))
                ->count() / $this->perPage
        );
    }

    public function nextPage(): void
    {
        if ($this->currentPage < $this->getTotalPages()) {
            $this->currentPage++;
        }
    }

    public function previousPage(): void
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
        }
    }

    public function goToPage(int $page): void
    {
        $this->currentPage = $page;
    }

    public function updatedSearchPage(): void
    {
        $this->currentPage = 1;
    }

    public function openSummaryModal(): void
    {
        $this->fromPage = 1;
        $this->toPage = 1;
        $this->summaryResult = '';

        $this->dispatch('open-modal', id: 'summary-modal');
    }

    public function closeSummaryModal(): void
    {
        $this->dispatch('close-modal', id: 'summary-modal');
    }

    public function getSummary(): void
    {
        // منطق دریافت خلاصه اینجا
    }

    public function storeSummary(): void
    {
        $this->validate([
            'fromPage'      => ['required', 'integer', 'min:1'],
            'toPage'        => ['required', 'integer', 'gte:fromPage'],
            'summaryResult' => ['required', 'string'],
        ]);

        Summary::updateOrCreate(
            [
                'book_id'   => $this->book->id,
                'from_page' => $this->fromPage,
                'to_page'   => $this->toPage,
            ],
            [
                'content' => $this->summaryResult,
            ]
        );

        Notification::make()
            ->title('خلاصه ذخیره شد')
            ->success()
            ->send();
    }
}
