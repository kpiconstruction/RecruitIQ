<?php

namespace App\Livewire;

use AbanoubNassem\FilamentGRecaptchaField\Forms\Components\GRecaptcha;
use Afatmustafa\FilamentTurnstile\Forms\Components\Turnstile;
use App\Filament\Enums\JobCandidateStatus;
use App\Models\Candidates;
use App\Models\JobCandidates;
use App\Models\JobOpenings;
use App\Models\Referrals;
use DominionSolutions\FilamentCaptcha\Forms\Components\Captcha;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Attributes\Title;
use Livewire\Component;

class CareerApplyJob extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public ?array $data = ['attachment' => null];

    public ?string $captcha = '';

    public string|null|JobOpenings $record = '';

    public static ?JobOpenings $jobDetails = null;

    public ?string $referenceNumber;

    public function mount($jobReferenceNumber)
    {
        // search for the job reference number, if not valid, redirect to all job
        $this->jobOpeningDetails($jobReferenceNumber);
        $this->referenceNumber = $jobReferenceNumber;

    }

    public function updated()
    {
        $this->jobOpeningDetails($this->referenceNumber);
    }

    private function jobOpeningDetails($reference): void
    {
        $this->record = JobOpenings::jobStillOpen()->where('JobOpeningSystemID', '=', $reference)->first();
        if (empty($this->record)) {
            // redirect back as the job opening is closed or tampered id or not existing
            Notification::make()
                ->title('Job Opening is already closed or doesn\'t exist.')
                ->icon('heroicon-o-x-circle')
                ->iconColor('warning')
                ->send();
            $this->redirectRoute('career.landing_page');
        }
    }

    public function create(): void
    {
        $data = $this->form->getState();

        // Create Candidate
        $candidate = Candidates::create([
            'FirstName' => $data['FirstName'],
            'LastName' => $data['LastName'],
            'Mobile' => $data['mobile'],
            'email' => $data['Email'],
            'ExperienceInYears' => $data['experience'],
            'Street' => $data['Street'],
            'City' => $data['City'],
            'Country' => $data['Country'],
            'ZipCode' => $data['ZipCode'],
            'State' => $data['State'],
            'CurrentEmployer' => $data['CurrentEmployer'],
            'CurrentJobTitle' => $data['CurrentJobTitle'],
            'School' => $data['School'],
            'ExperienceDetails' => $data['ExperienceDetails'],
            'GovReportingTags' => $data['GovReportingTags'] ?? [],
            'EmergencyContacts' => $data['EmergencyContacts'] ?? [],
            'EmploymentHistory' => $data['EmploymentHistory'] ?? [],
            'RightToWorkAUNZ' => $data['RightToWorkAUNZ'] ?? null,
            'OtherLanguages' => $data['OtherLanguages'] ?? null,
        ]);

        // Job Candidates
        $job_candidates = JobCandidates::create([
            'JobId' => $this->record->id,
            'CandidateSource' => 'Career Page',
            'CandidateStatus' => (($data['RightToWorkAUNZ'] ?? 'Yes') === 'No') ? JobCandidateStatus::Rejected : JobCandidateStatus::New,
            'candidate' => $candidate->id,
            'mobile' => $data['mobile'],
            'Email' => $data['Email'],
            'ExperienceInYears' => $data['experience'],
            'CurrentJobTitle' => $data['CurrentJobTitle'],
            'CurrentEmployer' => $data['CurrentEmployer'],
            'Street' => $data['Street'],
            'City' => $data['City'],
            'Country' => $data['Country'],
            'ZipCode' => $data['ZipCode'],
            'State' => $data['State'],
        ]);

        if ($candidate && $job_candidates) {
            if (! empty($data['Referees']) && is_array($data['Referees'])) {
                foreach ($data['Referees'] as $ref) {
                    Referrals::create([
                        'ReferringJob' => $this->record->id,
                        'JobCandidate' => $job_candidates->id,
                        'Candidate' => $candidate->id,
                        'ReferredBy' => $ref['referee_name'] ?? null,
                        'Relationship' => $ref['known_relation'] ?? null,
                        'KnownPeriod' => $ref['known_period'] ?? null,
                        'Notes' => trim(((($ref['organisation'] ?? '') !== '' ? ($ref['organisation'].'; ') : '')).(($ref['position'] ?? '') !== '' ? ('Pos: '.$ref['position'].'; ') : '').(($ref['phone_type'] ?? '') !== '' ? ('Type: '.$ref['phone_type'].'; ') : '').(($ref['phone'] ?? '') !== '' ? ('Phone: '.$ref['phone'].'; ') : '').(($ref['email'] ?? '') !== '' ? ('Email: '.$ref['email']) : '')),
                    ]);
                }
            }
            Notification::make()
                ->title('Application submitted!')
                ->success()
                ->body('Thank you for submitting your application details.')
                ->send();
            Notification::make()
                ->title('Reminder!')
                ->success()
                ->body('Please always check your communication for our hiring party response.')
                ->send();
            $this->redirectRoute('career.landing_page');
        }

    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Application')
                        ->icon('heroicon-o-user')
                        ->columns(2)
                        ->schema(array_merge($this->applicationStepWizard(),
                            [Forms\Components\Grid::make(1)
                                ->columns(1)
                                ->schema($this->captchaField())]
                        )),
                    Wizard\Step::make('Assessment')
                        ->visible(false)
                        ->icon('heroicon-o-user')
                        ->columns(2)
                        ->schema(array_merge([], $this->assessmentStepWizard())),
                ])
                    ->nextAction(
                        fn (Action $action) => $action->view('career-form.apply-job-components.NextActionButton'),
                    )
                    ->submitAction(view('career-form.apply-job-components.SubmitApplicationButton')),
            ]);
    }

    private function assessmentStepWizard(): Wizard\Step|array
    {
        return [];
    }

    private function applicationStepWizard(): array
    {
        return
            [
                Forms\Components\Section::make('Basic Information')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('FirstName')
                            ->required()
                            ->label('First Name'),
                        Forms\Components\TextInput::make('LastName')
                            ->required()
                            ->label('Last Name'),
                        Forms\Components\TextInput::make('mobile')
                            ->required(),
                        Forms\Components\TextInput::make('Email')
                            ->required()
                            ->email(),
                    ]),
                Forms\Components\Section::make('Address Information')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('Street'),
                        Forms\Components\TextInput::make('City'),
                        Forms\Components\TextInput::make('Country'),
                        Forms\Components\TextInput::make('ZipCode'),
                        Forms\Components\TextInput::make('State'),
                    ]),
                Forms\Components\Section::make('Professional Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('CurrentEmployer')
                            ->label('Current Employer (Company Name)'),
                        Forms\Components\TextInput::make('CurrentJobTitle')
                            ->label('Current Job Title'),
                        Forms\Components\Select::make('experience')
                            ->options([
                                '1year' => '1year',
                                '2year' => '2 Years',
                                '3year' => '3 Years',
                                '4year' => '4 Years',
                                '5year' => '5 Years',
                                '6year' => '6 Years',
                                '7year' => '7 Years',
                                '8year' => '8 Years',
                                '9year' => '9 Years',
                                '10year+' => '10 Years & Above',
                            ])
                            ->label('Experience'),
                    ]),
                Forms\Components\Section::make('Employment History Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('ApplyingForPosition')
                            ->label('What position are you applying for?')
                            ->default(fn () => self::$jobDetails?->JobTitle ?? self::$jobDetails?->postingTitle ?? '')
                            ->disabled(),
                        Forms\Components\Repeater::make('EmploymentHistory')
                            ->label('Previous Employment History')
                            ->minItems(1)
                            ->schema([
                                Forms\Components\TextInput::make('employer')->required()->label('Employer'),
                                Forms\Components\TextInput::make('position')->required()->label('Position'),
                                Forms\Components\DatePicker::make('date_start')->required()->label('Dates Employed Start'),
                                Forms\Components\DatePicker::make('date_end')->required()->label('Dates Employed End'),
                                Forms\Components\TextInput::make('reason_leaving')->label('Reason for Leaving'),
                                Forms\Components\Textarea::make('key_duties')->required()->label('Key duties performed'),
                            ])
                            ->columns(2)
                            ->addActionLabel('Add Employment')
                            ->deletable(true),
                    ]),
                Forms\Components\Section::make('Educational Details')
                    ->schema([
                        Forms\Components\Repeater::make('School')
                            ->label('')
                            ->addActionLabel('+ Add Degree Information')
                            ->schema([
                                Forms\Components\TextInput::make('school_name')
                                    ->required(),
                                Forms\Components\TextInput::make('major')
                                    ->required(),
                                Forms\Components\Select::make('duration')
                                    ->options([
                                        '4years' => '4 Years',
                                        '5years' => '5 Years',
                                    ])
                                    ->required(),
                                Forms\Components\Checkbox::make('pursuing')
                                    ->inline(false),
                            ])
                            ->deletable(true)
                            ->columns(4),
                    ]),
                Forms\Components\Section::make('Experience Details')
                    ->schema([
                        Forms\Components\Repeater::make('ExperienceDetails')
                            ->label('')
                            ->addActionLabel('Add Experience Details')
                            ->schema([
                                Forms\Components\Checkbox::make('current')
                                    ->label('Current?')
                                    ->inline(false),
                                Forms\Components\TextInput::make('company_name'),
                                Forms\Components\TextInput::make('duration'),
                                Forms\Components\TextInput::make('role'),
                                Forms\Components\Textarea::make('company_address'),
                            ])
                            ->deletable(true)
                            ->columns(5),
                    ]),
                Forms\Components\Section::make('Emergency Contact Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Repeater::make('EmergencyContacts')
                            ->label('Please list the details of at least one emergency contact who can be contacted in case of emergency.')
                            ->minItems(1)
                            ->schema([
                                Forms\Components\TextInput::make('first_name')->required()->label('First Name'),
                                Forms\Components\TextInput::make('last_name')->required()->label('Last Name'),
                                Forms\Components\TextInput::make('relationship')->required()->label('Relationship'),
                                Forms\Components\TextInput::make('mobile')->required()->label('Mobile Number'),
                                Forms\Components\TextInput::make('home')->label('Home Number'),
                                Forms\Components\Fieldset::make('Address')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('street')->label('Street'),
                                        Forms\Components\TextInput::make('city')->label('City'),
                                        Forms\Components\TextInput::make('state')->label('State'),
                                        Forms\Components\TextInput::make('postcode')->label('Postcode'),
                                        Forms\Components\TextInput::make('country')->label('Country'),
                                    ]),
                            ])
                            ->columns(2)
                            ->addActionLabel('Add Emergency Contact')
                            ->deletable(true),
                    ]),
                Forms\Components\Section::make('Right to Work Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Radio::make('RightToWorkAUNZ')
                            ->label('Are you an Australian or New Zealand Permanent Resident or Citizen?')
                            ->options(['Yes' => 'Yes', 'No' => 'No'])
                            ->required(),
                    ]),
                Forms\Components\Section::make('Language')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Radio::make('SpeakMoreThanOneLanguage')
                            ->label('Do you speak more than one language')
                            ->options(['Yes' => 'Yes', 'No' => 'No'])
                            ->required(),
                        Forms\Components\Textarea::make('OtherLanguages')
                            ->label('Languages spoken other than English')
                            ->visible(fn (callable $get) => $get('SpeakMoreThanOneLanguage') === 'Yes'),
                    ]),
                Forms\Components\Section::make('Referee Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Repeater::make('Referees')
                            ->label('Please list the details of at least two referees who can be contacted to provide either employment or character references.')
                            ->minItems(2)
                            ->schema([
                                Forms\Components\TextInput::make('referee_name')->required()->label('Referee Name'),
                                Forms\Components\TextInput::make('organisation')->required()->label('Organisation'),
                                Forms\Components\TextInput::make('position')->label('Referee Position'),
                                Forms\Components\TextInput::make('phone')->required()->label('Referee Phone'),
                                Forms\Components\Select::make('phone_type')->options(['Mobile' => 'Mobile', 'Landline' => 'Landline'])->required()->label('Phone Type'),
                                Forms\Components\TextInput::make('email')->label('Referee Email')->email(),
                                Forms\Components\TextInput::make('known_relation')->required()->label('How is this person known to you?'),
                                Forms\Components\TextInput::make('known_period')->label('Known period'),
                            ])
                            ->columns(2)
                            ->addActionLabel('Add Referee')
                            ->deletable(true),
                    ]),
                Forms\Components\Section::make('Compliance & Reporting')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('GovReportingTags')
                            ->label('To assist with client and Government reporting, please advise if any of the following apply to you. (Select all that apply).')
                            ->multiple()
                            ->preload()
                            ->options([
                                'None' => 'None',
                                'Asylum seeker' => 'Asylum seeker',
                                'Culturally and Linguistically Diverse' => 'Culturally and Linguistically Diverse',
                                'Department of justice client or criminal record' => 'Department of justice client or criminal record',
                                'Disadvantaged postcode' => 'Disadvantaged postcode',
                                'Disadvantaged youth' => 'Disadvantaged youth',
                                'Disengaged youth for 6 months' => 'Disengaged youth for 6 months',
                                'Indigenous Person' => 'Indigenous Person',
                                'International student' => 'International student',
                                'LGBTQI+' => 'LGBTQI+',
                                'Long-term unemployed' => 'Long-term unemployed',
                                'Migrant without employment within 6 months' => 'Migrant without employment within 6 months',
                                'New migrant' => 'New migrant',
                                'Refugee' => 'Refugee',
                                'Retrenched due to COVID-19' => 'Retrenched due to COVID-19',
                                'Retrenched/Ex-automotive worker' => 'Retrenched/Ex-automotive worker',
                                'Single parent' => 'Single parent',
                                'Social or Economic Person at Risk' => 'Social or Economic Person at Risk',
                                'Veteran' => 'Veteran',
                                'Working with a disability' => 'Working with a disability',
                            ])
                            ->required(),
                    ]),
                Forms\Components\FileUpload::make('attachment')
                    ->preserveFilenames()
                    ->directory('JobCandidate-attachments')
                    ->visibility('private')
                    ->openable()
                    ->downloadable()
                    ->previewable()
                    ->acceptedFileTypes([
                        'application/pdf',
                    ])
                    ->required()
                    ->label('Resume'),
            ];
    }

    private function captchaField(): array
    {
        if (! config('recruit.enable_captcha')) {
            return [];
        }
        if (config('recruit.enable_captcha')) {
            if (config('recruit.captcha_provider.default') === 'Google') {
                return [GRecaptcha::make('captcha')];
            }
            if (config('recruit.captcha_provider.default') === 'Cloudflare') {
                return [
                    Turnstile::make('turnstile')
                        ->theme('light')
                        ->size('normal')
                        ->language('en-US'),
                ];
            }

            // default
            if (config('recruit.captcha_provider.default') === 'Recruit_Captcha') {
                return [
                    Captcha::make('captcha')
                        ->rules(['captcha'])
                        ->required()
                        ->validationMessages([
                            'captcha' => __('Captcha does not match the image'),
                        ]),
                ];
            }

        }

        return [];

    }

    #[Title('Apply Job ')]
    public function render()
    {
        return view('livewire.career-apply-job', [
            'jobDetail' => $this->record,
        ]);
    }
}
