<?php

namespace App\Filament\Pages;

use App\Models\Prompt;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use BackedEnum;

class Prompts extends Page
{

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'پرامپت‌های من';

    protected static ?string $title = 'پرامپت‌های من';
    protected string $view = 'filament.pages.prompts';

    public string $text = '';
    public string $type = '';
    public string $promptTitle = '';

    public bool $openDeleteModal = false;
    public ?int $promptIdToDelete = null;

    protected array $rules = [
        'promptTitle' => ['required', 'string', 'max:255'],
        'text' => ['required', 'string'],
        'type' => ['required', 'in:read,summary,quiz'],
    ];

    public function getPrompts()
    {
        return Prompt::where('user_id', Auth::id())
            ->orderBy('id')
            ->paginate(10);
    }

    public function store(): void
    {
        $this->validate();

        Prompt::create([
            'user_id' => Auth::id(),
            'title'    => $this->promptTitle,
            'type'    => $this->type,
            'text'    => $this->text,
        ]);

        $this->text = '';
        $this->type = 'read';

        Notification::make()
            ->title('پرامپت با موفقیت ذخیره شد.')
            ->success()
            ->send();
    }

    public function deletePrompt(int $id): void
    {
        Prompt::findOrFail($id)->delete();

        Notification::make()
            ->title('پرامپت حذف شد.')
            ->success()
            ->send();
    }
}
