<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use BackedEnum;
use Illuminate\Support\Facades\Auth;

class Users extends Page
{
    protected static ?string $navigationLabel = 'کاربران';
    protected static ?string $title = 'مدیریت کاربران';
    protected string $view = 'filament.pages.users';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    public string $search = '';
    public bool $showModal = true;
    public ?int $editingUserId = null;
    public string $name = '';
    public string $national_code = '';
    public string $password = '';
    public array $selectedRoles = [];

    public bool $openDeleteModal = false;
    public ?int $userIdToDelete = null;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->hasRole('admin');
    }

    public function getUsers()
    {
        return User::with('roles')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('national_code', 'like', "%{$this->search}%"))
            ->orderBy('id')
            ->paginate(30);
    }

    public function getRoles()
    {
        return Role::all();
    }

    public function openCreateModal(): void
    {
        $this->reset(['editingUserId', 'name', 'national_code', 'password', 'selectedRoles']);
        $this->dispatch('open-modal', id: 'user-modal');
    }

    public function openEditModal(int $userId): void
    {
        $user = User::with('roles')->findOrFail($userId);
        $this->editingUserId = $userId;
        $this->name = $user->name;
        $this->national_code = $user->national_code;
        $this->password = '';
        $this->selectedRoles = $user->roles->pluck('id')->toArray();
        $this->dispatch('open-modal', id: 'user-modal');
    }

    public function save(): void
    {
        $rules = [
            'name'  => ['required', 'string', 'max:255'],
            'national_code' => ['required', 'max:255', 'unique:users,national_code,' . $this->editingUserId],
            'selectedRoles' => ['array'],
        ];

        if (! $this->editingUserId) {
            $rules['password'] = ['required', 'string', 'min:8'];
        }

        $this->validate($rules, [
            'name.required'     => 'نام الزامی است.',
            'national_code.required'    => 'کدملی الزامی است.',
            'national_code.unique'      => 'این کدملی قبلاً ثبت شده است.',
            'password.required' => 'رمز عبور الزامی است.',
            'password.min'      => 'رمز عبور حداقل ۸ کاراکتر باشد.',
        ]);

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);
            $user->update([
                'name'  => $this->name,
                'national_code' => $this->national_code,
                ...(filled($this->password) ? ['password' => Hash::make($this->password)] : []),
            ]);
        } else {
            $user = User::create([
                'name'     => $this->name,
                'national_code'    => $this->national_code,
                'password' => Hash::make($this->password),
            ]);
        }

        $roles = Role::whereIn('id', $this->selectedRoles)->get();
        $user->syncRoles($roles);
        $this->reset(['editingUserId', 'name', 'national_code', 'password', 'selectedRoles']);

        $this->dispatch('close-modal', id: 'user-modal');

        Notification::make()
            ->title($this->editingUserId ? 'کاربر ویرایش شد.' : 'کاربر ایجاد شد.')
            ->success()
            ->send();
    }

    public function deleteUser(int $id): void
    {
        User::findOrFail($id)->delete();

        Notification::make()
            ->title('کاربر حذف شد.')
            ->success()
            ->send();
    }
}
