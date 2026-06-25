<x-filament-panels::page>

  <div class="flex justify-end mb-2">
    <x-filament::button color="gray" size="sm" icon="heroicon-o-arrow-left" icon-position="after" tag="a"
      href="{{ route('books.pages', $book) }}">
      بازگشت
    </x-filament::button>
  </div>

  <x-filament::section>
    @forelse($this->getSummaries() as $summary)
      <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 mb-4">

        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center gap-2">
            <x-filament::badge color="info">
              صفحات {{ $summary->from_page }} تا {{ $summary->to_page }}
            </x-filament::badge>
            <span class="text-xs text-gray-400 dark:text-gray-500">
              {{ $summary->created_at->diffForHumans() }}
            </span>
          </div>

          <x-filament::icon-button icon="heroicon-o-trash" color="danger" size="sm" tooltip="حذف"
            wire:click="deleteSummary({{ $summary->id }})" wire:confirm="آیا مطمئن هستید؟" />
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
  </x-filament::section>

</x-filament-panels::page>
