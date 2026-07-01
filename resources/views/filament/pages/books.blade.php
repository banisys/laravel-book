<x-filament-panels::page>

  <x-filament::section heading="افزودن کتاب‌">
    <form wire:submit="store">
      <div class="grid grid-cols-12 gap-4">

        <div class="col-span-7">
          <x-filament::input.wrapper label="عنوان کتاب" :valid="!$errors->has('title')">
            <x-filament::input type="text" wire:model="bookTitle" placeholder="عنوان کتاب را وارد کنید"
              maxlength="255" />
          </x-filament::input.wrapper>
          @error('bookTitle')
            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
          @enderror
        </div>

        <div class="col-span-3">
          <x-filament::input.wrapper label="فایل PDF">
            <x-filament::input type="file" wire:model="pdf" accept="application/pdf" />
          </x-filament::input.wrapper>
          @error('pdf')
            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
          @enderror
        </div>

        <div class="col-span-2">
          <x-filament::button type="submit" icon="heroicon-o-check" icon-position="before" wire:loading.attr="disabled"
            wire:target="store">
            <span wire:loading.remove wire:target="store">ذخیره</span>
            <span wire:loading wire:target="store">در حال ذخیره...</span>
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

        <tbody>
          @php $books = $this->getBooks(); @endphp

          @forelse($books as $book)
            <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">

              <td class="px-3 py-4 text-gray-700 dark:text-gray-300 py-3 w-5">{{ $book->id }}</td>

              <td class="px-3 py-4 break-words w-75">
                {{ $book->title }}
              </td>

              <td class="px-3 py-4 w-20">
                <div class="flex items-center gap-3 justify-end">

                  <x-filament::icon-button icon="heroicon-o-list-bullet" tag="a" wire:navigate
                    href="{{ route('filament.admin.pages.books.{book}.pages', ['book' => $book->id]) }}"
                    tooltip="لیست صفحات" class="bg-success-600 hover:bg-success-700 [&_svg]:text-white ml-1" />
                  <x-filament::icon-button icon="heroicon-o-document" tag="a"
                    href="{{ Storage::url($book->pdf_path) }}" target="_blank" tooltip="فایل PDF"
                    class="bg-primary-600 hover:bg-primary-700 [&_svg]:text-white ml-1" />


                  <div x-data="{ openDeleteModal: @entangle('openDeleteModal').live, bookIdToDelete: @entangle('bookIdToDelete') }">
                    <x-filament::icon-button icon="heroicon-o-trash" tooltip="حذف"
                      class="bg-red-600 hover:bg-red-700 [&_svg]:text-white"
                      x-on:click="bookIdToDelete = {{ $book->id }}; openDeleteModal = true" />

                    <div x-show="openDeleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
                      style="display: none;">

                      <div class="fixed inset-0 bg-black/10 transition-opacity" x-on:click="openDeleteModal = false">
                      </div>

                      <div class="flex min-h-full items-center justify-center p-4">
                        <div class="relative w-full max-w-md rounded-xl bg-white shadow-xl dark:bg-gray-800"
                          x-on:click.outside="openDeleteModal = false">

                          <div class="border-b border-gray-200 dark:border-gray-700 p-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                              تایید حذف کتاب
                            </h3>
                          </div>
                          <div class="p-4">
                            <p class="text-gray-600 dark:text-gray-400">
                              آیا از حذف این کتاب مطمئن هستید؟ این عملیات غیرقابل بازگشت است.
                            </p>
                          </div>
                          <div class="border-t border-gray-200 dark:border-gray-700 p-4 flex justify-end gap-x-3">
                            <x-filament::button color="gray" x-on:click="openDeleteModal = false">
                              انصراف
                            </x-filament::button>

                            <x-filament::button color="danger"
                              x-on:click="$wire.deleteBook(bookIdToDelete); openDeleteModal = false">
                              بله، حذف شود
                            </x-filament::button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

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

</x-filament-panels::page>
