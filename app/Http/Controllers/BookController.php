<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Page;
use Illuminate\Http\Request;
use Spatie\PdfToImage\Pdf;


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

        $book = Book::create([
            'title' => $validated['title'],
            'pdf_path' => '',
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
            ->with('success', 'Book created successfully.');
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
}
