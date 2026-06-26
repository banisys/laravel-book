<x-filament-panels::page>

  <x-filament::section heading="افزودن پرامپت">
    <form wire:submit="store">
      <div class="grid grid-cols-12 gap-4">

        <div class="col-span-2">
          <x-filament::input.wrapper label="نوع پرامپت">
            <x-filament::input.select wire:model="type" class="[direction:rtl] bg-[left_0.75rem_center] pr-3 pl-10">
              <option value="" disabled>نوع پرامپت را انتخاب کنید</option>
              <option value="read">خواندن متن کتاب</option>
              <option value="summary">خلاصه سازی</option>
              <option value="quiz">ساخت آزمون</option>
            </x-filament::input.select>
          </x-filament::input.wrapper>

          @error('type')
            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
          @enderror
        </div>

        <div class="col-span-4">
          <x-filament::input.wrapper label="عنوان">
            <x-filament::input type="text" wire:model="promptTitle" placeholder="عنوان را وارد کنید" />
          </x-filament::input.wrapper>

          @error('title')
            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
          @enderror
        </div>

        <div class="col-span-5">
          <x-filament::input.wrapper label="متن پرامپت">
            <textarea wire:model="text" rows="1" placeholder="متن پرامپت را وارد کنید"
              class="fi-input block w-full border-0 bg-transparent resize-y focus:ring-0 py-2 px-3 text-sm text-gray-950 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500"></textarea>
          </x-filament::input.wrapper>

          @error('text')
            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
          @enderror
        </div>

        <div class="col-span-1 flex items-end">
          <x-filament::button type="submit" icon="heroicon-o-check" icon-position="before" wire:loading.attr="disabled"
            wire:target="store">
            <span wire:loading.remove wire:target="store">ذخیره</span>
            <span wire:loading wire:target="store">در حال ذخیره...</span>
          </x-filament::button>
        </div>

      </div>
    </form>
  </x-filament::section>

  <x-filament::section heading="لیست پرامپت‌ها">

    <div class="flex justify-end mb-3">
      <x-filament::badge>
        {{ $this->getPrompts()->count() }} پرامپت
      </x-filament::badge>
    </div>

    <div class="overflow-x-auto">
      <table class="table-fixed text-right text-sm w-full">
        <tbody>
          @php $prompts = $this->getPrompts(); @endphp

          @forelse($prompts as $prompt)
            <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">

              <td class="px-3 py-1.5 text-gray-700 dark:text-gray-300">
                {{ ($prompts->currentPage() - 1) * $prompts->perPage() + $loop->iteration }}
              </td>

              <td class="px-3 py-1.5">
                <x-filament::badge :color="match ($prompt->type) {
                    'read' => 'primary',
                    'summary' => 'success',
                    'quiz' => 'warning',
                }">
                  {{ match ($prompt->type) {
                      'read' => 'خواندن متن کتاب',
                      'summary' => 'خلاصه سازی',
                      'quiz' => 'ساختن آزمون',
                  } }}
                </x-filament::badge>
              </td>

              <td class="px-3 py-1.5 break-words text-gray-700 dark:text-gray-300">
                {{ $prompt->title }}
              </td>

              <td class="px-3 py-1.5 break-words text-gray-700 dark:text-gray-300">
                {{ $prompt->text }}
              </td>

              <td class="px-3 py-1.5">
                <div class="flex items-center gap-2 justify-end" x-data="{ openDeleteModal: @entangle('openDeleteModal').live, promptIdToDelete: @entangle('promptIdToDelete') }">

                  <x-filament::icon-button icon="heroicon-o-trash" size="sm" tooltip="حذف"
                    class="bg-red-600 hover:bg-red-700 [&_svg]:text-white"
                    x-on:click="promptIdToDelete = {{ $prompt->id }}; openDeleteModal = true" />

                  <div x-show="openDeleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
                    style="display: none;">
                    <div class="fixed inset-0 bg-black/10 transition-opacity" x-on:click="openDeleteModal = false">
                    </div>
                    <div class="flex min-h-full items-center justify-center p-4">
                      <div class="relative w-full max-w-md rounded-xl bg-white shadow-xl dark:bg-gray-800"
                        x-on:click.outside="openDeleteModal = false">
                        <div class="border-b border-gray-200 dark:border-gray-700 p-4">
                          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">تایید حذف پرامپت</h3>
                        </div>
                        <div class="p-4">
                          <p class="text-gray-600 dark:text-gray-400">آیا از حذف این پرامپت مطمئن هستید؟ این عملیات
                            غیرقابل بازگشت است.</p>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 p-4 flex justify-end gap-x-3">
                          <x-filament::button color="gray"
                            x-on:click="openDeleteModal = false">انصراف</x-filament::button>
                          <x-filament::button color="danger"
                            x-on:click="$wire.deletePrompt(promptIdToDelete); openDeleteModal = false">بله، حذف
                            شود</x-filament::button>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
              </td>

            </tr>
          @empty
            <tr>
              <td colspan="4" class="px-3 py-6 text-center text-gray-400">
                پرامپتی یافت نشد.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @php $prompts = $this->getPrompts(); @endphp

    <div class="mt-4">
      {{ $prompts->links() }}
    </div>
  </x-filament::section>

</x-filament-panels::page>
