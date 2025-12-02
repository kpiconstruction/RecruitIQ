<?php

namespace App\Filament\Resources;

use App\Models\JobOpeningTemplate;
use Filament\Forms\Form;                      // ✅ this one
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JobOpeningTemplateResource extends Resource
{
    protected static ?string $model = JobOpeningTemplate::class;

    public static function form(Form $form): Form   // ✅ matches base class
    {
        return $form->schema([
            Section::make('Template')
                ->schema([
                    // fields...
                ]),
        ]);
    }

    // ...
}
