<x-filament-panels::page>

  {{-- Delete Modal — خارج از loop --}}
  <div x-data="{ openDeleteModal: @entangle('openDeleteModal').live, summaryIdToDelete: @entangle('summaryIdToDelete') }">

    <div x-show="openDeleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
      <div class="fixed inset-0 bg-black/50 transition-opacity" x-on:click="openDeleteModal = false"></div>
      <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-md rounded-xl bg-white shadow-xl dark:bg-gray-800"
          x-on:click.outside="openDeleteModal = false">

          <div class="border-b border-gray-200 dark:border-gray-700 p-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">تایید حذف خلاصه</h3>
          </div>
          <div class="p-4">
            <p class="text-gray-600 dark:text-gray-400">
              آیا از حذف این خلاصه مطمئن هستید؟ این عملیات غیرقابل بازگشت است.
            </p>
          </div>
          <div class="border-t border-gray-200 dark:border-gray-700 p-4 flex justify-end gap-x-3">
            <x-filament::button color="gray" x-on:click="openDeleteModal = false">
              انصراف
            </x-filament::button>
            <x-filament::button color="danger" x-on:click="$wire.deleteSummary(); openDeleteModal = false">
              بله، حذف شود
            </x-filament::button>
          </div>

        </div>
      </div>
    </div>

    {{-- Back Button --}}
    <div class="flex justify-end mb-2">
      <x-filament::button color="gray" size="sm" icon="heroicon-o-arrow-left" icon-position="after"
        tag="a" href="{{ route('filament.admin.pages.books.{book}.pages', ['book' => $book->id]) }}">
        بازگشت
      </x-filament::button>
    </div>

    {{-- Edit Modal --}}
    <x-filament::modal id="edit-summary" width="2xl">
      <x-slot name="heading">ویرایش خلاصه</x-slot>
      <div class="space-y-4">
        <textarea wire:model="editingContent" rows="12"
          class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-gray-100 p-3 leading-7 focus:outline-none focus:ring-2 focus:ring-primary-500 resize-y"
          dir="rtl"></textarea>
        @error('editingContent')
          <p class="text-sm text-danger-500">{{ $message }}</p>
        @enderror
      </div>
      <x-slot name="footerActions">
        <x-filament::button wire:click="saveEdit">ذخیره</x-filament::button>
        <x-filament::button color="gray" x-on:click="$dispatch('close-modal', { id: 'edit-summary' })">
          انصراف
        </x-filament::button>
      </x-slot>
    </x-filament::modal>

    <x-filament::section>
      @php $summaries = $this->getSummaries() @endphp

      @forelse($summaries as $summary)
        <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 mb-4"
          wire:key="summary-{{ $summary->id }}">

          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              <x-filament::badge color="info">
                صفحات {{ $summary->from_page }} تا {{ $summary->to_page }}
              </x-filament::badge>
              <span class="text-xs text-gray-400 dark:text-gray-500" dir="ltr">
                {{ \Morilog\Jalali\Jalalian::fromDateTime($summary->created_at)->format('Y/m/d H:i') }}
              </span>
            </div>

            <div class="flex items-center gap-1">
              <x-filament::icon-button icon="heroicon-o-pencil-square" color="warning" size="sm" tooltip="ویرایش"
                wire:click="openEditModal({{ $summary->id }})" />

              <x-filament::icon-button icon="heroicon-o-trash" color="danger" size="sm" tooltip="حذف"
                x-on:click="summaryIdToDelete = {{ $summary->id }}; openDeleteModal = true" />
            </div>
          </div>

          <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-7">
            {{ $summary->content }}
          </p>

        </div>
      @empty
        <div class="py-12 text-center text-gray-400">
          هیچ خلاصه‌ای یافت نشد.
        </div>
      @endforelse

      @if ($summaries->hasPages())
        <div class="mt-4">{{ $summaries->links() }}</div>
      @endif
    </x-filament::section>

  </div>

</x-filament-panels::page>
