<?php

namespace App\Filament\Pages;

use App\Models\Book;
use App\Models\Prompt;
use Filament\Pages\Page as FilamentPage;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Summary;
use Illuminate\Support\Facades\Auth;

class Pages extends FilamentPage
{
    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.pages';
    protected static ?string $slug = 'books/{book}/pages';

    public ?Book $book = null;
    public int $currentPage = 1;
    public int $perPage = 30;
    public string $searchPage = '';
    public ?int $searchFrom = null;
    public ?int $searchTo = null;
    public int $fromPage = 1;
    public int $toPage = 1;
    public string $summaryResult = '';
    public ?int $processingPageId = null;
    public array $selectedReadPrompts = [];
    public string $selectedModel = 'gapgpt-qwen-3.5';

    public array $selectedSummaryPrompts = [];

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

        $defaultPrompt = '
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
        ';

        $userPrompts = Prompt::whereIn('id', $this->selectedReadPrompts)
            ->where('user_id', Auth::id())
            ->where('type', 'read')
            ->pluck('text')
            ->implode("\n");

        $finalPrompt = $userPrompts
            ? $defaultPrompt . "\n" . $userPrompts
            : $defaultPrompt;

        $image = Storage::disk('public')->get($page->image_path);
        $base64 = base64_encode($image);

        $response = Http::timeout(160)->withToken(env('GAPGPT_API_KEY'))
            ->post('https://api.gapgpt.app/v1/responses', [
                'model' => $this->selectedModel,
                'input' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'input_image',
                                'image_url' => [
                                    'url' => "data:image/jpeg;base64,{$base64}",
                                ],
                            ],
                            [
                                'type' => 'input_text',
                                'text' => $finalPrompt,
                            ],
                        ],
                    ],
                ],
            ]);

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
            ->when($this->searchFrom, fn($pages) => $pages->where('page_number', '>=', $this->searchFrom))
            ->when($this->searchTo, fn($pages) => $pages->where('page_number', '<=', $this->searchTo))
            ->values();
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
        $this->validate([
            'fromPage' => ['required', 'integer'],
            'toPage'   => ['required', 'integer'],
        ]);

        $book = Book::findOrFail($this->summaryBookId);

        $defaultPrompt = "
            شما یک دستیار آموزشی هستید.

            متن ورودی فقط شامل محتوای صفحات {$this->fromPage} تا {$this->toPage} کتاب است.

            مهم:
            - فقط اطلاعات موجود در همین صفحات را در نظر بگیر.
            - از دانش عمومی یا اطلاعات خارج از این صفحات استفاده نکن.
            - اگر پاسخ یا توضیحی در این صفحات وجود ندارد، آن را حدس نزن.
            - به صفحات قبل یا بعد از این بازه ارجاع نده.

            در قالب فارسی روان:
            - نکات مهم را استخراج کن.
            - مفاهیم کلیدی را توضیح بده.
            - در پایان یک خلاصه کوتاه ارائه کن.
            ";

        $userPrompts = Prompt::whereIn('id', $this->selectedSummaryPrompts)
            ->where('user_id', Auth::id())
            ->where('type', 'summary')
            ->pluck('text')
            ->implode("\n");

        $finalPrompt = $userPrompts
            ? $defaultPrompt . "\n" . $userPrompts
            : $defaultPrompt;

        $response = Http::timeout(60)->post(
            config('services.rag.url') . '/query/ask',
            [
                'book_id'    => $book->rag_book_id,
                'question'   => $finalPrompt,
                'max_chunks' => 5,
            ]
        );

        $response->throw();

        $this->summaryResult = $response->json('answer') ?? $response->json('result') ?? '';

        Notification::make()
            ->title('خلاصه دریافت شد.')
            ->success()
            ->send();
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

    public function getReadPrompts()
    {
        return Prompt::where('user_id', Auth::id())
            ->where('type', 'read')
            ->get();
    }

    public function openProcessModal(int $pageId): void
    {
        $this->processingPageId = $pageId;
        $this->selectedReadPrompts = [];
        $this->selectedModel = 'gapgpt-qwen-3.5';
        $this->dispatch('open-modal', id: 'page-modal-' . $pageId);
    }

    public function getSummaryPrompts()
    {
        return Prompt::where('user_id', Auth::id())
            ->where('type', 'summary')
            ->get();
    }
}
