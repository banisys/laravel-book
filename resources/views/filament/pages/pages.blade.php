<x-filament-panels::page>

  <div class="grid grid-cols-5 gap-4">

    <div
      class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 flex items-center gap-3">
      <div class="rounded-full bg-blue-100 dark:bg-blue-400/10 p-2">
        <x-heroicon-o-document-text class="w-5 h-5 text-blue-600 dark:text-blue-400" />
      </div>
      <div>
        <p class="text-xs text-gray-500 dark:text-gray-400">کل صفحات</p>
        <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $book->pages->count() }}</p>
      </div>
    </div>

    <div
      class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 flex items-center gap-3">
      <div class="rounded-full bg-emerald-100 dark:bg-emerald-400/10 p-2">
        <x-heroicon-o-check-circle class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
      </div>
      <div>
        <p class="text-xs text-gray-500 dark:text-gray-400">دارای متن</p>
        <p class="text-xl font-semibold text-gray-900 dark:text-white">
          {{ $book->pages->whereNotNull('content')->where('content', '!=', '')->count() }}</p>
      </div>
    </div>

    <div
      class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 flex items-center gap-3">
      <div class="rounded-full bg-violet-100 dark:bg-violet-400/10 p-2">
        <x-heroicon-o-cpu-chip class="w-5 h-5 text-violet-600 dark:text-violet-400" />
      </div>
      <div>
        <p class="text-xs text-gray-500 dark:text-gray-400">افزوده شده به RAG</p>
        <p class="text-xl font-semibold text-gray-900 dark:text-white">
          {{ $book->pages->where('is_synced_to_rag', true)->count() }}</p>
      </div>
    </div>


    <div
      class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 flex items-center gap-3">
      <div class="rounded-full bg-gray-100 dark:bg-gray-700 p-2">
        <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-600 dark:text-gray-400" />
      </div>
      <div class="flex-1">
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">جستجو بر اساس شماره صفحه</p>
        <x-filament::input type="number" wire:model.live="searchPage" placeholder="شماره صفحه..." min="1" />
      </div>
    </div>

    <div
      class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 flex flex-col gap-3">
      <div class="flex flex-col gap-2">
        <x-filament::button color="info" size="sm" icon="heroicon-o-sparkles" wire:click="openSummaryModal">
          دریافت خلاصه جدید
        </x-filament::button>

        <x-filament::button color="gray" size="sm" icon="heroicon-o-list-bullet" tag="a"
          href="{{ route('books.summaries', $book) }}">
          خلاصه‌های قبلی
        </x-filament::button>
      </div>
    </div>

  </div>

  <x-filament::section>
    <div class="overflow-x-auto">
      <table class="w-full text-right text-sm">
        <tbody>
          @forelse($this->getFilteredPages() as $page)
            <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">

              <td class="px-3 py-2 w-12 text-gray-500 dark:text-gray-400">
                {{ $page->page_number }}
              </td>

              <td class="px-3 py-2 w-32">
                <img src="{{ Storage::url($page->image_path) }}"
                  class="rounded border border-gray-200 dark:border-gray-700 w-24 h-auto cursor-pointer" x-data
                  x-on:click="$dispatch('open-modal', { id: 'page-modal-{{ $page->id }}' })" />
              </td>

              <td class="px-3 py-2">
                <textarea id="content-{{ $page->id }}" rows="6"
                  x-on:click="$dispatch('open-modal', { id: 'page-modal-{{ $page->id }}' })"
                  class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 p-2 text-sm resize-y">{{ $page->content }}</textarea>
              </td>

              <td class="px-3 py-2 w-40">
                <div class="flex flex-col gap-2">

                  <x-filament::button size="sm" color="primary" wire:click="processPage({{ $page->id }})"
                    wire:loading.attr="disabled" wire:target="processPage({{ $page->id }})">
                    <span wire:loading.remove wire:target="processPage({{ $page->id }})">دریافت متن</span>
                    <span wire:loading wire:target="processPage({{ $page->id }})">در حال پردازش...</span>
                  </x-filament::button>

                  @if (!$page->is_synced_to_rag)
                    @if ($page->content)
                      <x-filament::button size="sm" color="success" wire:click="addToRag({{ $page->id }})">
                        افزودن به RAG
                      </x-filament::button>
                    @endif
                  @else
                    <x-filament::button size="sm" color="danger" wire:click="deleteFromRag({{ $page->id }})">
                      حذف از RAG
                    </x-filament::button>
                  @endif

                </div>
              </td>

            </tr>

            <x-filament::modal id="page-modal-{{ $page->id }}" width="5xl">
              <div class="grid grid-cols-2 gap-4">
                <div class="flex items-center justify-center">
                  <img src="{{ Storage::url($page->image_path) }}"
                    class="max-h-[80vh] w-auto rounded border border-gray-200 dark:border-gray-700" />
                </div>
                <div class="flex flex-col gap-3">
                  <textarea id="modal-content-{{ $page->id }}" rows="30"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 p-3 text-sm resize-y">{{ $page->content }}</textarea>

                  <x-filament::button size="sm" color="success" x-data
                    x-on:click="$wire.updateContent({{ $page->id }}, document.getElementById('modal-content-{{ $page->id }}').value)">
                    ذخیره متن
                  </x-filament::button>
                </div>
              </div>
            </x-filament::modal>
          @empty
            <tr>
              <td colspan="4" class="px-3 py-8 text-center text-gray-400">
                صفحه‌ای یافت نشد.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    @if ($this->getTotalPages() > 1)
      <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">

        <p class="text-sm text-gray-500 dark:text-gray-400">
          صفحه {{ $currentPage }} از {{ $this->getTotalPages() }}
        </p>

        <div class="flex items-center gap-1">

          <x-filament::icon-button icon="heroicon-o-chevron-right" size="sm" color="gray"
            wire:click="previousPage" :disabled="$currentPage === 1" />

          @for ($i = 1; $i <= $this->getTotalPages(); $i++)
            <x-filament::button size="sm" :color="$currentPage === $i ? 'primary' : 'gray'" wire:click="goToPage({{ $i }})">
              {{ $i }}
            </x-filament::button>
          @endfor

          <x-filament::icon-button icon="heroicon-o-chevron-left" size="sm" color="gray" wire:click="nextPage"
            :disabled="$currentPage === $this->getTotalPages()" />

        </div>

      </div>
    @endif

  </x-filament::section>

  <x-filament::modal id="summary-modal" width="5xl">

    <div class="space-y-4">

      <div class="grid grid-cols-6 gap-4 items-end">

        <div>
          <label class="text-sm font-medium">از صفحه</label>
          <x-filament::input.wrapper>
            <x-filament::input wire:model="fromPage" type="number" />
          </x-filament::input.wrapper>
        </div>

        <div>
          <label class="text-sm font-medium">تا صفحه</label>
          <x-filament::input.wrapper>
            <x-filament::input type="number" wire:model="toPage" min="1" />
          </x-filament::input.wrapper>
        </div>

        <div>
          <x-filament::button wire:click="getSummary" color="primary" >
            دریافت خلاصه
          </x-filament::button>
          
        </div>
      </div>

      <textarea rows="20" wire:model="summaryResult" class="w-full rounded-lg border p-3">
        </textarea>

    </div>

    <x-slot name="footerActions">

      <x-filament::button color="success" wire:click="storeSummary">
        ذخیره
      </x-filament::button>

      <x-filament::button color="gray" x-on:click="$dispatch('close-modal', { id: 'summary-modal' })">
        بستن
      </x-filament::button>

    </x-slot>

  </x-filament::modal>


</x-filament-panels::page>
