<x-filament-panels::page>

  <div class="flex items-center justify-end">

    <x-filament::button icon="heroicon-o-plus" wire:click="openCreateModal">
      کاربر جدید
    </x-filament::button>
  </div>

  <x-filament::section>
    @php $users = $this->getUsers(); @endphp

    <div class="overflow-x-auto">
      <table class="w-full text-right text-sm">
        <thead>
          <tr class="border-b border-gray-200 dark:border-gray-700">
            <th class="px-3 py-2 text-gray-600 dark:text-gray-400 font-medium"></th>
            <th class="px-3 py-2 text-gray-600 dark:text-gray-400 font-medium">نام</th>
            <th class="px-3 py-2 text-gray-600 dark:text-gray-400 font-medium">کد‌ملی</th>
            <th class="px-3 py-2 text-gray-600 dark:text-gray-400 font-medium">نقش‌ها</th>
            <th class="px-3 py-2 text-gray-600 dark:text-gray-400 font-medium">تاریخ عضویت</th>
            <th class="px-3 py-2">
              <div class="flex justify-end">
                <x-filament::input type="text" wire:model.live="search"
                  placeholder="جستجو بر اساس نام یا کدملی..." />
              </div>
            </th>
          </tr>
        </thead>
        <tbody>
          @forelse($users as $user)
            <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">

              <td class="px-3 py-2 text-gray-500 dark:text-gray-400">
                {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
              </td>

              <td class="px-3 py-2 text-gray-900 dark:text-white font-medium">
                {{ $user->name }}
              </td>

              <td class="px-3 py-2 text-gray-600 dark:text-gray-300">
                {{ $user->national_code }}
              </td>

              <td class="px-3 py-2">
                <div class="flex flex-wrap gap-1">
                  @forelse($user->roles as $role)
                    <x-filament::badge color="primary">{{ $role->name }}</x-filament::badge>
                  @empty
                    <span class="text-gray-400 text-xs">بدون نقش</span>
                  @endforelse
                </div>
              </td>

              <td class="px-3 py-2 text-gray-500 dark:text-gray-400 text-xs" dir="ltr">
                {{ \Morilog\Jalali\Jalalian::fromDateTime($user->created_at)->format('Y/m/d H:i') }}
              </td>

              <td class="px-3 py-4">
                <div class="flex items-center gap-2 justify-end" x-data="{ openDeleteModal: @entangle('openDeleteModal').live, userIdToDelete: @entangle('userIdToDelete') }">

                  <x-filament::icon-button icon="heroicon-o-book-open" size="sm" tooltip="مدیریت کتاب‌ها"
                    tag="a" href="{{ route('filament.admin.pages.user-books', ['user' => $user->id]) }}"
                    wire:key="books-{{ $user->id }}"
                    class="ml-2 !bg-sky-600 hover:!bg-sky-700 [&_svg]:!text-white" />

                  <x-filament::icon-button icon="heroicon-o-pencil-square" size="sm" tooltip="ویرایش"
                    color="success" wire:click="openEditModal({{ $user->id }})"
                    class="ml-2 !bg-green-600 hover:!bg-green-700 !text-white" />

                  <x-filament::icon-button icon="heroicon-o-trash" size="sm" tooltip="حذف"
                    class="bg-red-600 hover:bg-red-700 [&_svg]:text-white"
                    x-on:click="userIdToDelete = {{ $user->id }}; openDeleteModal = true" />

                  <div x-show="openDeleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
                    style="display:none;">
                    <div class="fixed inset-0 bg-black/10" x-on:click="openDeleteModal = false"></div>
                    <div class="flex min-h-full items-center justify-center p-4">
                      <div class="relative w-full max-w-md rounded-xl bg-white shadow-xl dark:bg-gray-800"
                        x-on:click.outside="openDeleteModal = false">
                        <div class="border-b border-gray-200 dark:border-gray-700 p-4">
                          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">تایید حذف کاربر</h3>
                        </div>
                        <div class="p-4">
                          <p class="text-gray-600 dark:text-gray-400">آیا از حذف این کاربر مطمئن هستید؟</p>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 p-4 flex justify-end gap-x-3">
                          <x-filament::button color="gray"
                            x-on:click="openDeleteModal = false">انصراف</x-filament::button>
                          <x-filament::button color="danger"
                            x-on:click="$wire.deleteUser(userIdToDelete); openDeleteModal = false">بله، حذف
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
              <td colspan="6" class="px-3 py-8 text-center text-gray-400">کاربری یافت نشد.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>

  </x-filament::section>

  {{-- create/edit modal --}}
  <x-filament::modal id="user-modal" width="lg" :heading="$editingUserId ? 'ویرایش کاربر' : 'کاربر جدید'">
    <div class="flex flex-col gap-4">

      <div>
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 block mb-1">نام و نام خانوادگی</label>
        <x-filament::input.wrapper>
          <x-filament::input type="text" wire:model="name" />
        </x-filament::input.wrapper>
        @error('name')
          <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 block mb-1">کد ملی</label>
        <x-filament::input.wrapper>
          <x-filament::input wire:model="national_code" />
        </x-filament::input.wrapper>
        @error('national_code')
          <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 block mb-1">
          رمز عبور
          @if ($editingUserId)
            <span class="text-xs text-gray-400">(خالی بگذارید تا تغییر نکند)</span>
          @endif
        </label>
        <x-filament::input.wrapper>
          <x-filament::input type="password" wire:model="password" placeholder="رمز عبور" />
        </x-filament::input.wrapper>
        @error('password')
          <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 block mb-1">نقش‌ها</label>
        <div class="flex flex-col gap-2 rounded-lg border border-gray-200 dark:border-gray-700 p-3">
          @foreach ($this->getRoles() as $role)
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" value="{{ $role->id }}" wire:model="selectedRoles"
                class="rounded border-gray-300 text-primary-600" />
              <span class="text-sm text-gray-700 dark:text-gray-300">{{ $role->name }}</span>
            </label>
          @endforeach
        </div>
        @error('selectedRoles')
          <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
      </div>

    </div>

    <x-slot name="footerActions">
      <x-filament::button wire:click="save">ذخیره</x-filament::button>
      <x-filament::button color="gray"
        x-on:click="$dispatch('close-modal', { id: 'user-modal' })">انصراف</x-filament::button>
    </x-slot>

  </x-filament::modal>

</x-filament-panels::page>
