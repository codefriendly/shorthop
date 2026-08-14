<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class UiLab extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'lucide-flask-conical';

    protected static ?string $navigationLabel = 'UI Lab';

    protected static string|UnitEnum|null $navigationGroup = 'Local Testing';

    protected static ?int $navigationSort = 1000;

    protected static ?string $slug = 'ui-lab';

    protected static ?string $title = 'UI Lab';

    protected string $view = 'filament.pages.ui-lab';

    public static function canAccess(): bool
    {
        return config('app.env') === 'local';
    }
}
