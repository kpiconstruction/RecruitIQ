<?php

namespace App\Filament\Resources;

use App\Models\JobOpeningTemplate;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

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
                        ])->required(),
                ])->columns(2),
            Section::make('Role')
                ->schema([
                    TextInput::make('postingTitle'),
                    TextInput::make('JobTitle'),
                    TextInput::make('payBand'),
                    TextInput::make('Salary'),
                    Select::make('WorkExperience')
                        ->options(config('recruit.work_experience_options')),
                    Select::make('RequiredSkill')
                        ->multiple()
                        ->options(config('recruit.required_skill_options')),
                ])->columns(3),
            Section::make('Content')
                ->schema([
                    RichEditor::make('JobDescription'),
                    RichEditor::make('JobRequirement'),
                    RichEditor::make('JobBenefits'),
                    RichEditor::make('AdditionalNotes'),
                    Textarea::make('coreDuties'),
                    Select::make('tickets')->multiple()->options(config('recruit.tickets_options', [])),
                    Select::make('licences')->multiple()->options(config('recruit.licences_options', [])),
                    Select::make('qualifications')->multiple()->options(config('recruit.qualifications_options', [])),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('state')->sortable(),
            TextColumn::make('postingTitle')->label('Title')->limit(30),
            TextColumn::make('updated_at')->dateTime()->sortable(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => JobOpeningTemplateResource\Pages\ListJobOpeningTemplates::route('/'),
            'create' => JobOpeningTemplateResource\Pages\CreateJobOpeningTemplate::route('/create'),
            'edit' => JobOpeningTemplateResource\Pages\EditJobOpeningTemplate::route('/{record}/edit'),
        ];
    }
}
