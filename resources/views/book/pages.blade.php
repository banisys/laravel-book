<!DOCTYPE html>
<html>

<head>
  <title>{{ $book->title }}</title>
</head>

<body>

  <h1>{{ $book->title }}</h1>

  <a href="{{ route('books.index') }}">
    Back
  </a>

  <hr>

  <table border="1" cellpadding="10">
    <thead>
      <tr>
        <th>Page</th>
        <th>Image</th>
        <th>Text</th>
        <th>Action</th>
      </tr>
    </thead>

    <tbody>

      @foreach ($pages as $page)
        <tr>

          <td>
            {{ $page->page_number }}
          </td>

          <td>
            <img src="{{ Storage::url($page->image_path) }}" width="200">
          </td>

          <td>
            <textarea rows="8" cols="60">{{ $page->content }}</textarea>
          </td>

          <td>
            <button>
              Process
            </button>
          </td>

        </tr>
      @endforeach

    </tbody>
  </table>

</body>

</html>
