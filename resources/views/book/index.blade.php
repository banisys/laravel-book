<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Books</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

  <style>
    .rtl {
      direction: rtl !important
    }
  </style>
</head>

<body class="rtl">

  <div class="container py-5">

    <div class="row text-right">

      <div class="col-lg-4">

        <div class="card mb-4">

          <div class="card-header">
            <h4 class="mb-0">افزودن کتاب</h4>
          </div>

          <div class="card-body">

            @if (session('success'))
              <div class="alert alert-success">
                {{ session('success') }}
              </div>
            @endif

            @if ($errors->any())
              <div class="alert alert-danger">
                <ul class="mb-0 pl-3">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <form action="{{ route('books.store') }}" method="POST" enctype="multipart/form-data">

              @csrf

              <div class="form-group">
                <label for="title">
                  عنوان کتاب
                </label>

                <input type="text" id="title" name="title" value="{{ old('title') }}" class="form-control"
                  required>
              </div>

              <div class="form-group">
                <label for="pdf">
                  PDF فایل
                </label>

                <input type="file" id="pdf" name="pdf" accept="application/pdf" class="form-control-file"
                  required>
              </div>

              <button type="submit" class="btn btn-primary btn-block">
                ذخیره
              </button>

            </form>

          </div>

        </div>

      </div>

      <div class="col-lg-8">

        <div class="card">

          <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">لیست کتاب ها</h4>

            <span class="badge badge-primary">
              {{ $books->count() }}
            </span>
          </div>

          <div class="card-body p-0">

            <div class="table-responsive">

              <table class="table table-striped table-hover mb-0">

                <tbody>

                  @forelse($books as $book)
                    <tr>

                      <td>
                        {{ $book->id }}
                      </td>

                      <td>
                        {{ $book->title }}
                      </td>

                      <td>
                        <a href="{{ Storage::url($book->pdf_path) }}" target="_blank"
                          class="btn btn-sm btn-outline-primary">
                          نمایش PDF
                        </a>
                      </td>

                      <td>
                        <a href="{{ route('books.pages', $book) }}" class="btn btn-sm btn-outline-success">
                          لیست صفحات
                        </a>

                        <button type="button" class="btn btn-sm btn-outline-info summary-btn"
                          data-book-id="{{ $book->id }}" data-book-title="{{ $book->title }}">
                          خلاصه
                        </button>

                      </td>

                      <td>
                        <form action="{{ route('books.destroy', $book) }}" method="POST"
                          onsubmit="return confirm('Delete this book?')">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-danger">
                            حذف
                          </button>
                        </form>
                      </td>

                    </tr>
                  @empty
                    <tr>
                      <td colspan="4" class="text-center text-muted py-4">
                        کتابی یافت نشد.
                      </td>
                    </tr>
                  @endforelse

                </tbody>

              </table>

            </div>

          </div>

        </div>

      </div>

    </div>

  </div>

  <div class="modal fade text-right" id="summaryModal" tabindex="-1">

    <div class="modal-dialog modal-lg">

      <div class="modal-content">
        <div class="modal-body">

          <input type="hidden" id="summary-book-id">

          <div class="form-row">

            <div class="form-group col-md-6">
              <label>از صفحه</label>

              <input type="number" id="from-page" class="form-control" min="1">
            </div>

            <div class="form-group col-md-6">
              <label>تا صفحه</label>

              <input type="number" id="to-page" class="form-control" min="1">
            </div>

          </div>

          <button id="summary-submit" class="btn btn-primary">
            دریافت خلاصه
          </button>

          <hr>

          <div class="form-group">

            <label>پاسخ</label>

            <textarea id="summary-result" rows="12" class="form-control"></textarea>

          </div>

        </div>

      </div>

    </div>

  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    $('.summary-btn').on('click', function() {

      $('#summary-book-id').val(
        $(this).data('book-id')
      );

      $('#summary-result').val('');

      $('#summaryModal').modal('show');
    });
  </script>

  <script>
    $('#summary-submit').on('click', async function() {

      const bookId = $('#summary-book-id').val();

      const btn = $(this);

      btn.prop('disabled', true);

      try {

        const response = await fetch(
          `/books/${bookId}/summary`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
              from_page: $('#from-page').val(),
              to_page: $('#to-page').val(),
              question: $('#question').val()
            })
          }
        );

        const data = await response.json();

        $('#summary-result').val(
          data.answer ??
          data.response ??
          JSON.stringify(data, null, 2)
        );

      } catch (e) {

        alert('خطا در دریافت پاسخ');

        console.error(e);

      } finally {

        btn.prop('disabled', false);
      }

    });
  </script>

</body>

</html>
