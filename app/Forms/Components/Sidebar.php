<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Component;

class Sidebar extends Component
{
    protected string $view = 'forms.components.sidebar';

    public static function make(): static
    {
        return app(static::class);
    }
}
