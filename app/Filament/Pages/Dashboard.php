<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BackedEnum;

class Dashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'داشبورد'; // عنوان دلخواه
    protected static ?string $title = 'داشبورد'; // عنوان صفحه
    protected static ?int $navigationSort = -2; // برای نمایش در بالای سایدبار

    protected string $view = 'filament.pages.dashboard';
}
