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

                <thead class="thead-light">
                  <tr>
                    <th width="80">ID</th>
                    <th>عنوان</th>
                    <th width="140">PDF</th>
                    <th width="140">صفحات</th>
                  </tr>
                </thead>

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

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
