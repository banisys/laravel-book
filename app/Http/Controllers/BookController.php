<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Page;
use Illuminate\Http\Request;
use Spatie\PdfToImage\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;


class BookController extends Controller
{
    public function index()
    {
        $books = Book::latest()->get();

        return view('book.index', compact('books'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'pdf'   => ['required', 'file', 'mimes:pdf'],
        ]);

        $response = Http::timeout(30)->post(
            config('services.rag.url') . '/books/create'
        );

        if (! $response->successful()) {
            return back()->withErrors([
                'api' => 'Failed to create book in RAG service.',
            ]);
        }

        $ragBookId = $response->json('book_id');

        if (! $ragBookId) {
            return back()->withErrors([
                'api' => 'Invalid response from RAG service.',
            ]);
        }

        $book = Book::create([
            'title' => $validated['title'],
            'pdf_path' => '',
            'rag_book_id' => $ragBookId,
        ]);

        $directory = "books/{$book->id}";

        $pdfPath = $request->file('pdf')->storeAs(
            $directory,
            'document.pdf',
            'public'
        );

        $book->update([
            'pdf_path' => $pdfPath,
        ]);

        $pdfFullPath = storage_path(
            'app/public/' . $pdfPath
        );

        $pdf = new Pdf($pdfFullPath);

        $pageCount = $pdf->pageCount();

        for ($page = 1; $page <= $pageCount; $page++) {

            $relativeImagePath =
                "books/{$book->id}/page-{$page}.jpg";

            $absoluteImagePath = storage_path(
                "app/public/{$relativeImagePath}"
            );

            $pdf->selectPage($page)->save($absoluteImagePath);

            Page::create([
                'book_id' => $book->id,
                'page_number' => $page,
                'image_path' => $relativeImagePath,
            ]);
        }

        return redirect()
            ->route('books.index')
            ->with('success', 'کتاب با موفقیت ذخیره شد.');
    }

    public function pages(Book $book)
    {
        $pages = $book->pages()
            ->orderBy('page_number')
            ->get();

        return view('book.pages', compact(
            'book',
            'pages'
        ));
    }

    public function destroy(Book $book)
    {
        Storage::disk('public')->deleteDirectory(
            "books/{$book->id}"
        );

        $book->pages()->delete();

        $book->delete();

        return redirect()
            ->route('books.index')
            ->with('success', 'کتاب با موفقیت حذف شد.');
    }

    public function summary(Request $request, Book $book)
    {
        $request->validate([
            'from_page' => ['required', 'integer'],
            'to_page' => ['required', 'integer'],
        ]);
       

        $prompt = "
            شما یک دستیار آموزشی هستید.

            متن ورودی فقط شامل محتوای صفحات {$request->from_page} تا {$request->to_page} کتاب است.

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

        $response = Http::timeout(60)->post(
            config('services.rag.url')  . '/query/ask',
            [
                'book_id' => $book->rag_book_id,
                'question' => $prompt,
                'max_chunks' => 5,
            ]
        );

        $response->throw();

        return response()->json(
            $response->json()
        );
    }
}
