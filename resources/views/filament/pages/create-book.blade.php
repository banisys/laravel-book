<x-filament-panels::page>

  <x-filament::section heading="افزودن کتاب‌">
    <form wire:submit="store">
      <div class="grid grid-cols-12 gap-4">

        <div class="col-span-4">
          <x-filament::input.wrapper label="عنوان کتاب" :valid="!$errors->has('title')" required>
            <x-filament::input type="text" wire:model="bookTitle" placeholder="عنوان کتاب را وارد کنید" required
              maxlength="255" />
          </x-filament::input.wrapper>
          @error('title')
            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
          @enderror
        </div>

        <div class="col-span-4">
          <x-filament::input.wrapper label="فایل PDF" required>
            <x-filament::input type="file" wire:model="pdf" accept="application/pdf" required />
          </x-filament::input.wrapper>
          @error('pdf')
            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
          @enderror
        </div>

        <div class="col-span-2">
          <x-filament::button type="submit" icon="heroicon-o-check">
            ذخیره
          </x-filament::button>
        </div>

      </div>

    </form>
  </x-filament::section>
  <x-filament::section heading="لیست کتاب‌ها">

    <div class="flex justify-end mb-3">
      <x-filament::badge>
        {{ $this->getBooks()->count() }} کتاب
      </x-filament::badge>
    </div>

    <div class="overflow-x-auto">

      <table class="table-fixed text-right text-sm w-full">
        <thead>
          <tr class="border-b border-gray-200 dark:border-gray-700">
            <th class="px-3 py-2 font-medium text-gray-600 dark:text-gray-400 w-16">شناسه</th>
            <th class="px-3 py-2">عنوان</th>
            <th class="px-3 py-2 font-medium text-gray-600 dark:text-gray-400 w-24">PDF</th>
            <th class="px-3 py-2 font-medium text-gray-600 dark:text-gray-400 w-28">صفحات</th>
            <th class="px-3 py-2 font-medium text-gray-600 dark:text-gray-400 w-36">عملیات</th>
          </tr>
        </thead>
        <tbody>
          @forelse($this->getBooks() as $book)
            <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">

              <td class="px-3 py-1.5 text-gray-700 dark:text-gray-300">{{ $book->id }}</td>

              <td class="px-3 py-1.5 break-words">
                {{ $book->title }}
              </td>

              <td class="px-3 py-1.5">
                <x-filament::link href="{{ Storage::url($book->pdf_path) }}" target="_blank" color="primary">
                  نمایش PDF
                </x-filament::link>
              </td>

              <td class="px-3 py-1.5">
                <x-filament::button size="sm" color="success" tag="a"
                  href="{{ route('books.pages', $book) }}">
                  لیست صفحات
                </x-filament::button>
              </td>

              <td class="px-3 py-1.5">
                <div class="flex items-center gap-1 justify-end">
                  <x-filament::button size="sm" color="info"
                    wire:click="openSummaryModal({{ $book->id }}, '{{ $book->title }}')">
                    خلاصه
                  </x-filament::button>

                  <x-filament::icon-button icon="heroicon-o-trash" color="danger" size="sm"
                    wire:click="deleteBook({{ $book->id }})" wire:confirm="آیا مطمئن هستید؟" tooltip="حذف"
                    class="bg-red-600 hover:bg-red-700 [&_svg]:text-white" />
                </div>
              </td>

            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-3 py-6 text-center text-gray-400">
                کتابی یافت نشد.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </x-filament::section>

  <x-filament::modal id="summary-modal" width="2xl" heading="خلاصه کتاب">
    <div class="space-y-4">

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
            از صفحه
          </label>
          <x-filament::input.wrapper class="mt-1">
            <x-filament::input type="number" wire:model="fromPage" min="1" />
          </x-filament::input.wrapper>
        </div>

        <div>
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
            تا صفحه
          </label>
          <x-filament::input.wrapper class="mt-1">
            <x-filament::input type="number" wire:model="toPage" min="1" />
          </x-filament::input.wrapper>
        </div>
      </div>

      <x-filament::button wire:click="getSummary">
        دریافت خلاصه
      </x-filament::button>

      <hr class="border-gray-200 dark:border-gray-700">

      <div>
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
          پاسخ
        </label>
        <textarea wire:model="summaryResult" rows="12"
          class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 p-3 text-sm"></textarea>
      </div>

    </div>

    <x-slot name="footerActions">
      <x-filament::button color="gray" wire:click="closeSummaryModal">
        بستن
      </x-filament::button>
    </x-slot>

  </x-filament::modal>

</x-filament-panels::page>
