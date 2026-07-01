<x-filament-panels::page>

  <div class="flex items-center justify-between">

    <div class="flex items-center gap-3">
      <x-filament::icon-button icon="heroicon-o-arrow-right" tag="a"
        href="{{ route('filament.admin.pages.users') }}" tooltip="بازگشت" color="gray" />
      <div>
        <p class="text-xs text-gray-500 dark:text-gray-400">مدیریت کتاب‌های کاربر</p>
        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $this->getUser()->name }}</p>
        <p class="text-xs text-gray-400">{{ $this->getUser()->email }}</p>
      </div>
    </div>
  </div>

  <x-filament::section>
    <div class="flex items-center justify-between mb-3">
      <div class="flex items-center gap-2">
        <x-heroicon-o-book-open class="w-4 h-4 text-primary-500" />
        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">کتاب‌های متصل به کاربر</p>
      </div>
      <x-filament::badge color="primary">
        {{ $this->getUser()->books->count() }} کتاب
      </x-filament::badge>
    </div>

    <div class="flex flex-wrap gap-2">
      @forelse($this->getUser()->books as $book)
        <div wire:key="attached-{{ $book->id }}"
          class="flex items-center gap-2 rounded-lg border border-primary-200 dark:border-primary-800 bg-primary-50 dark:bg-primary-900/20 px-3 py-1.5">
          <span class="text-sm text-primary-700 dark:text-primary-300">{{ $book->title }}</span>
          <button wire:click="detach({{ $book->id }})"
            class="text-primary-400 hover:text-red-500 transition-colors">
            <x-heroicon-o-x-mark class="w-4 h-4" />
          </button>
        </div>
      @empty
        <div class="flex items-center gap-2 text-gray-400 text-sm">
          <x-heroicon-o-inbox class="w-4 h-4" />
          <span>هیچ کتابی متصل نشده است.</span>
        </div>
      @endforelse
    </div>
  </x-filament::section>

  {{-- جدول --}}
  <x-filament::section>
    @php $books = $this->getBooks(); @endphp

    <div class="overflow-x-auto">
      <table class="w-full text-right text-sm">
        <thead>
          <tr class="border-b border-gray-200 dark:border-gray-700">
            <th class="px-3 py-2 text-gray-600 dark:text-gray-400 font-medium">#</th>
            <th class="px-3 py-2 text-gray-600 dark:text-gray-400 font-medium">عنوان کتاب</th>
            <th class="px-3 py-2 text-gray-600 dark:text-gray-400 font-medium">وضعیت</th>
            <th class="px-3 py-2 text-left">
              <div class="flex justify-end">
                <x-filament::input.wrapper class="w-72">
                  <x-filament::input type="text" wire:model.live="search" placeholder="جستجو در کتاب‌ها..." />
                </x-filament::input.wrapper>
              </div>
            </th>
          </tr>
        </thead>
        <tbody>
          @forelse($books as $book)
            <tr wire:key="book-{{ $book->id }}"
              class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">

              <td class="px-3 py-2 text-gray-500 dark:text-gray-400">
                {{ ($books->currentPage() - 1) * $books->perPage() + $loop->iteration }}
              </td>

              <td class="px-3 py-2 text-gray-900 dark:text-white font-medium">
                {{ $book->title }}
              </td>

              <td class="px-3 py-2">
                @if ($this->isAttached($book->id))
                  <x-filament::badge color="success" icon="heroicon-o-check-circle">
                    متصل شده
                  </x-filament::badge>
                @else
                  <x-filament::badge color="gray" icon="heroicon-o-x-circle">
                    متصل نشده
                  </x-filament::badge>
                @endif
              </td>

              <td class="px-3 py-2">
                <div class="flex items-center gap-2 justify-end">
                  @if ($this->isAttached($book->id))
                    <x-filament::button size="sm" color="danger" icon="heroicon-o-minus-circle"
                      wire:click="detach({{ $book->id }})" wire:loading.attr="disabled"
                      wire:target="detach({{ $book->id }})">
                      حذف ارتباط
                    </x-filament::button>
                  @else
                    <x-filament::button size="sm" color="success" icon="heroicon-o-plus-circle"
                      wire:click="attach({{ $book->id }})" wire:loading.attr="disabled"
                      wire:target="attach({{ $book->id }})">
                      اتصال به کاربر
                    </x-filament::button>
                  @endif
                </div>
              </td>

            </tr>
          @empty
            <tr>
              <td colspan="4" class="px-3 py-8 text-center text-gray-400">
                کتابی یافت نشد.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">{{ $books->links() }}</div>

  </x-filament::section>

</x-filament-panels::page>
