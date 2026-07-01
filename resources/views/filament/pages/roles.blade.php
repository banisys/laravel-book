<x-filament-panels::page>

  <div class="flex justify-end">
    <x-filament::button icon="heroicon-o-plus" wire:click="openCreateModal">
      نقش جدید
    </x-filament::button>
  </div>

  <x-filament::section>
    <div class="overflow-x-auto">
      <table class="w-full text-right text-sm">
        <thead>
          <tr class="border-b border-gray-200 dark:border-gray-700">
            <th class="px-3 py-2 text-gray-600 dark:text-gray-400 font-medium"></th>
            <th class="px-3 py-2 text-gray-600 dark:text-gray-400 font-medium">نام نقش</th>
            <th class="px-3 py-2 text-gray-600 dark:text-gray-400 font-medium">دسترسی‌ها</th>
            <th class="px-3 py-2 text-gray-600 dark:text-gray-400 font-medium">تعداد کاربران</th>
            <th class="px-3 py-2"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($this->getRoles() as $index => $role)
            <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">

              <td class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>

              <td class="px-3 py-2 text-gray-900 dark:text-white font-medium">
                {{ $role->name }}
              </td>

              <td class="px-3 py-2">
                <div class="flex flex-wrap gap-1">
                  @forelse($role->permissions as $permission)
                    <x-filament::badge color="success">{{ $permission->name }}</x-filament::badge>
                  @empty
                    <span class="text-gray-400 text-xs">بدون دسترسی</span>
                  @endforelse
                </div>
              </td>

              <td class="px-3 py-2">
                <x-filament::badge color="gray">{{ $role->users_count }} کاربر</x-filament::badge>
              </td>

              <td class="px-3 py-4">
                <div class="flex items-center gap-2 justify-end" x-data="{ openDeleteModal: @entangle('openDeleteModal').live, roleIdToDelete: @entangle('roleIdToDelete') }">

                  <x-filament::icon-button icon="heroicon-o-pencil-square" size="sm" tooltip="ویرایش"
                    wire:click="openEditModal({{ $role->id }})"
                    class="ml-2 !bg-green-500 hover:!bg-amber-600 [&_svg]:!text-white" />

                  <x-filament::icon-button icon="heroicon-o-trash" size="sm" tooltip="حذف"
                    class="bg-red-600 hover:bg-red-700 [&_svg]:text-white"
                    x-on:click="roleIdToDelete = {{ $role->id }}; openDeleteModal = true" />

                  <div x-show="openDeleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
                    style="display:none;">
                    <div class="fixed inset-0 bg-black/10" x-on:click="openDeleteModal = false"></div>
                    <div class="flex min-h-full items-center justify-center p-4">
                      <div class="relative w-full max-w-md rounded-xl bg-white shadow-xl dark:bg-gray-800"
                        x-on:click.outside="openDeleteModal = false">
                        <div class="border-b border-gray-200 dark:border-gray-700 p-4">
                          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">تایید حذف نقش</h3>
                        </div>
                        <div class="p-4">
                          <p class="text-gray-600 dark:text-gray-400">آیا از حذف این نقش مطمئن هستید؟</p>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 p-4 flex justify-end gap-x-3">
                          <x-filament::button color="gray"
                            x-on:click="openDeleteModal = false">انصراف</x-filament::button>
                          <x-filament::button color="danger"
                            x-on:click="$wire.deleteRole(roleIdToDelete); openDeleteModal = false">بله، حذف
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
              <td colspan="5" class="px-3 py-8 text-center text-gray-400">نقشی یافت نشد.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </x-filament::section>

  <x-filament::modal id="role-modal" wire:model="showModal" width="lg" :heading="$editingRoleId ? 'ویرایش نقش' : 'نقش جدید'">
    <div class="flex flex-col gap-4">

      <div>
        <x-filament::input.wrapper>
          <x-filament::input type="text" wire:model="roleName" placeholder="نام نقش" />
        </x-filament::input.wrapper>
        @error('roleName')
          <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
      </div>

      {{-- <div>
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 block mb-1">دسترسی‌ها</label>
        <div
          class="flex flex-col gap-2 rounded-lg border border-gray-200 dark:border-gray-700 p-3 max-h-60 overflow-y-auto">
          @foreach ($this->getPermissions() as $permission)
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" value="{{ $permission->id }}" wire:model="selectedPermissions"
                class="rounded border-gray-300 text-primary-600" />
              <span class="text-sm text-gray-700 dark:text-gray-300">{{ $permission->name }}</span>
            </label>
          @endforeach
        </div>
        @error('selectedPermissions')
          <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
      </div> --}}
    </div>

    <x-slot name="footerActions">
      <x-filament::button wire:click="save">ذخیره</x-filament::button>
      <x-filament::button color="gray"
        x-on:click="$dispatch('close-modal', { id: 'role-modal' })">انصراف</x-filament::button>
    </x-slot>

  </x-filament::modal>

</x-filament-panels::page>
