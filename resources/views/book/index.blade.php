<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Books</title>
</head>

<body>

  <h1>Create Book</h1>

  @if (session('success'))
    <div style="color: green">
      {{ session('success') }}
    </div>
  @endif

  @if ($errors->any())
    <div style="color: red">
      <ul>
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('books.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div>
      <label>Title</label>
      <br>
      <input type="text" name="title" value="{{ old('title') }}" required>
    </div>

    <br>

    <div>
      <label>PDF File</label>
      <br>
      <input type="file" name="pdf" accept="application/pdf" required>
    </div>

    <br>

    <button type="submit">
      Save
    </button>
  </form>

  <hr>

  <h2>Books</h2>

  <table border="1" cellpadding="10">
    <thead>
      <tr>
        <th>ID</th>
        <th>Title</th>
        <th>PDF</th>
        <th>Pages</th>
      </tr>
    </thead>
    <tbody>
      @forelse($books as $book)
        <tr>
          <td>{{ $book->id }}</td>
          <td>{{ $book->title }}</td>
          <td>
            <a href="{{ Storage::url($book->pdf_path) }}" target="_blank">
              View PDF
            </a>
          </td>
          <td>
            <a href="{{ route('books.pages', $book) }}">
              View Pages
            </a>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="3">
            No books found.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>

</body>

</html>
