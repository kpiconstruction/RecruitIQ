<?php

namespace App\Filament\Resources;

use App\Models\JobOpeningTemplate;
use Filament\Forms\Form;                      // ✅ this one
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\RichEditor;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JobOpeningTemplateResource extends Resource
{
    protected static ?string $model = JobOpeningTemplate::class;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Template')
                ->schema([
                    TextInput::make('name')->required()->maxLength(255),
                    Select::make('state')
                        ->options([
                            'VIC' => 'Victoria',
                            'NSW' => 'New South Wales',
                            'QLD' => 'Queensland',
                        ])
                        ->native(false)
                        ->required(),
                ])->columns(2),
            Section::make('Role')
                ->schema([
                    TextInput::make('postingTitle'),
                    TextInput::make('JobTitle'),
                    TextInput::make('payBand'),
                    TextInput::make('Salary'),
                    Select::make('WorkExperience')
                        ->options(config('recruit.job_opening.work_experience') ?? [])
                        ->native(false),
                    Select::make('RequiredSkill')
                        ->multiple()
                        ->options(config('recruit.job_opening.required_skill_options') ?? [])
                        ->native(false),
                ])->columns(3),
            Section::make('Content')
                ->schema([
                    RichEditor::make('JobDescription'),
                    RichEditor::make('JobRequirement'),
                    RichEditor::make('JobBenefits'),
                    RichEditor::make('AdditionalNotes'),
                    Textarea::make('coreDuties'),
                    Select::make('tickets')->multiple()->options(config('recruit.tickets_options', []))->native(false),
                    Select::make('licences')->multiple()->options(config('recruit.licences_options', []))->native(false),
                    Select::make('qualifications')->multiple()->options(config('recruit.qualifications_options', []))->native(false),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            \Filament\Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            \Filament\Tables\Columns\TextColumn::make('state')->sortable(),
            \Filament\Tables\Columns\TextColumn::make('postingTitle')->label('Title')->limit(30),
            \Filament\Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\JobOpeningTemplateResource\Pages\ListJobOpeningTemplates::route('/'),
            'create' => \App\Filament\Resources\JobOpeningTemplateResource\Pages\CreateJobOpeningTemplate::route('/create'),
            'edit' => \App\Filament\Resources\JobOpeningTemplateResource\Pages\EditJobOpeningTemplate::route('/{record}/edit'),
        ];
    }
}
