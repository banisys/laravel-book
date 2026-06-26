<!DOCTYPE html>
<html lang="fa">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $book->title }}</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

  <style>
    textarea {
      direction: rtl;
      text-align: right;
    }

    .page-image {
      max-width: 200px;
      height: auto;
    }

    .rtl {
      direction: rtl !important
    }

    #modalImage {
      max-height: 80vh;
    }

    #modalContent {
      direction: rtl;
      text-align: right;
      height: 80vh;
      resize: vertical;
    }
  </style>
</head>

<body class="rtl">

  <div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="mb-0">{{ $book->title }}</h1>

      <a href="{{ route('books.index') }}" class="btn btn-secondary">
        لیست کتاب ها
      </a>
    </div>

    <div class="card">

      <div class="card-body p-0">

        <div class="table-responsive">
          <table class="table table-bordered table-hover mb-0">

            <tbody>

              @foreach ($pages as $page)
                <tr>
                  <td class="align-middle">
                    {{ $page->page_number }}
                  </td>

                  <td class="align-middle text-center">
                    <img src="{{ Storage::url($page->image_path) }}"
                      class="img-fluid img-thumbnail page-image preview-image" data-toggle="modal"
                      data-target="#pageModal" data-image="{{ Storage::url($page->image_path) }}"
                      data-content="{{ e($page->content) }}" style="cursor:pointer">
                  </td>

                  <td>
                    <textarea id="content-{{ $page->id }}" rows="10" class="form-control">{{ $page->content }}</textarea>
                  </td>
                  <td class="align-middle text-center">

                    <button class="btn btn-outline-primary btn-sm ocr-btn" data-page-id="{{ $page->id }}">
                      استخراج متن
                    </button>

                    @if (!$page->is_synced_to_rag)
                      <button class="btn btn-outline-success btn-sm rag-add-btn" data-page-id="{{ $page->id }}">
                        افزودن به RAG
                      </button>
                    @else
                      <button class="btn btn-outline-danger btn-sm rag-delete-btn" data-page-id="{{ $page->id }}">
                        حذف از RAG
                      </button>
                    @endif

                  </td>
                </tr>
              @endforeach

            </tbody>
          </table>
        </div>

      </div>
    </div>

  </div>

  <div class="modal fade" id="pageModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-body">

          <div class="row">

            <div class="col-md-6 text-center">
              <img id="modalImage" src="" class="img-fluid border" alt="">
            </div>

            <div class="col-md-6">
              <textarea id="modalContent" class="form-control" rows="25"></textarea>
            </div>

          </div>

        </div>

      </div>
    </div>
  </div>



  <script>
    document.querySelectorAll('.preview-image').forEach(image => {

      image.addEventListener('click', function() {

        document.getElementById('modalImage').src =
          this.dataset.image;

        document.getElementById('modalContent').value =
          this.dataset.content;

      });

    });
  </script>

  <script>
    document.querySelectorAll('.rag-add-btn').forEach(button => {

      button.addEventListener('click', async function() {

        const pageId = this.dataset.pageId;

        this.disabled = true;

        try {

          const response = await fetch(`/pages/${pageId}/rag`, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}',
              'Accept': 'application/json'
            }
          });

          const data = await response.json();

          if (data.success) {
            location.reload();
          }

        } finally {

          this.disabled = false;
        }

      });

    });
  </script>

  <script>
    document.querySelectorAll('.rag-delete-btn').forEach(button => {

      button.addEventListener('click', async function() {

        const pageId = this.dataset.pageId;

        if (!confirm('آیا مطمئن هستید؟')) {
          return;
        }

        this.disabled = true;

        try {

          const response = await fetch(`/pages/${pageId}/rag`, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}',
              'Accept': 'application/json'
            }
          });

          const data = await response.json();

          if (data.success) {
            location.reload();
          }

        } finally {

          this.disabled = false;
        }

      });

    });
  </script>


  <script>
    document.querySelectorAll('.ocr-btn').forEach(button => {

      button.addEventListener('click', async function() {

        const pageId = this.dataset.pageId;

        const originalText = this.innerText;

        this.disabled = true;

        this.innerHTML =
          '<span class="spinner-border spinner-border-sm"></span> در حال پردازش';

        try {

          const response = await fetch(
            `/pages/${pageId}/process`, {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
              }
            }
          );

          if (!response.ok) {
            throw new Error('Request failed');
          }

          const data = await response.json();

          document.getElementById(
            `content-${pageId}`
          ).value = data.content ?? '';

        } catch (error) {

          console.error(error);

          alert('خطا در استخراج متن');

        } finally {

          this.disabled = false;
          this.innerHTML = originalText;
        }

      });

    });
  </script>

</body>

</html>
