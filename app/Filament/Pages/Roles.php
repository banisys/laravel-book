<?php

namespace App\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use BackedEnum;
use Illuminate\Support\Facades\Auth;

class Roles extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'نقش‌ها';
    protected static ?string $title = 'مدیریت نقش‌ها';

    protected string $view = 'filament.pages.roles';

    public bool $showModal = false;
    public ?int $editingRoleId = null;
    public string $roleName = '';
    public array $selectedPermissions = [];

    public bool $openDeleteModal = false;
    public ?int $roleIdToDelete = null;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->hasRole('admin');
    }

    public function getRoles()
    {
        return Role::with('permissions')->withCount('users')->orderBy('id')->get();
    }

    public function getPermissions()
    {
        return Permission::all();
    }

    public function openCreateModal(): void
    {
        $this->reset(['editingRoleId', 'roleName', 'selectedPermissions']);
        $this->dispatch('open-modal', id: 'role-modal');
    }

    public function openEditModal(int $roleId): void
    {
        $role = Role::with('permissions')->findOrFail($roleId);

        $this->editingRoleId = $roleId;
        $this->roleName = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();
        $this->dispatch('open-modal', id: 'role-modal');
    }

    public function save(): void
    {
        $this->validate([
            'roleName' => ['required', 'string', 'max:255', 'unique:roles,name,' . $this->editingRoleId],
            'selectedPermissions' => ['array'],
        ], [
            'roleName.required' => 'نام نقش الزامی است.',
            'roleName.unique'   => 'این نقش قبلاً ثبت شده است.',
        ]);

        if ($this->editingRoleId) {
            $role = Role::findOrFail($this->editingRoleId);
            $role->update(['name' => $this->roleName]);
        } else {
            $role = Role::create(['name' => $this->roleName]);
        }
  
        $permissions = Permission::whereIn('id', $this->selectedPermissions)->get();
        $role->syncPermissions($permissions);

        $this->dispatch('close-modal', id: 'role-modal');
        $this->reset(['editingRoleId', 'roleName', 'selectedPermissions']);

        Notification::make()
            ->title($this->editingRoleId ? 'نقش ویرایش شد.' : 'نقش ایجاد شد.')
            ->success()
            ->send();
    }

    public function deleteRole(int $id): void
    {
        Role::findOrFail($id)->delete();

        Notification::make()
            ->title('نقش حذف شد.')
            ->success()
            ->send();
    }
}
