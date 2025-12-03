<?php

namespace App\Livewire;

use AbanoubNassem\FilamentGRecaptchaField\Forms\Components\GRecaptcha;
use Afatmustafa\FilamentTurnstile\Forms\Components\Turnstile;
use App\Filament\Enums\JobCandidateStatus;
use App\Models\ApplicantCompetency;
use App\Models\Candidates;
use App\Models\CompetencyType;
use App\Models\JobCandidates;
use App\Models\JobOpenings;
use App\Models\JobRole;
use App\Models\Referrals;
use App\Models\RoleCompetencyRequirement;
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
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Saade\FilamentAutograph\Forms\Components\Enums\DownloadableFormat;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

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
            'CriminalHistory' => $data['CriminalHistory'] ?? null,
            'CriminalDetails' => $data['CriminalDetails'] ?? null,
            'DeclarationAccepted' => ($data['DeclarationAccepted'] ?? false) ? 1 : 0,
            'SignatureName' => $data['SignatureName'] ?? null,
            'SignatureAt' => ($data['DeclarationAccepted'] ?? false) ? now() : null,
            'SignatureIP' => request()->ip(),
            'HealthDeclarationAccepted' => ($data['HealthDeclarationAccepted'] ?? false) ? 1 : 0,
            'HealthSignatureName' => $data['HealthSignatureName'] ?? null,
            'HealthSignatureAt' => ($data['HealthDeclarationAccepted'] ?? false) ? now() : null,
            'HealthSignatureIP' => request()->ip(),
            'HealthPreExistingCondition' => $data['HealthPreExistingCondition'] ?? null,
            'HealthPreExistingDetails' => $data['HealthPreExistingDetails'] ?? null,
            'HealthMedicationTreatment' => $data['HealthMedicationTreatment'] ?? null,
            'HealthMedicationDetails' => $data['HealthMedicationDetails'] ?? null,
            'HealthOtherCircumstances' => $data['HealthOtherCircumstances'] ?? null,
            'HealthOtherDetails' => $data['HealthOtherDetails'] ?? null,
            'DrugAlcoholConsent' => ($data['DrugAlcoholConsent'] ?? false) ? 1 : 0,
        ]);

        if ($candidate && $job_candidates) {
            // Persist signature image if provided
            if (($data['DeclarationAccepted'] ?? false) && ! empty($data['SignatureDataUrl'])) {
                try {
                    $path = 'JobCandidate-attachments/signature-'.$job_candidates->id.'.png';
                    $base64 = $data['SignatureDataUrl'];
                    $parts = explode(',', $base64, 2);
                    if (count($parts) === 2) {
                        $binary = base64_decode($parts[1]);
                        Storage::disk(config('filesystems.default'))
                            ->put($path, $binary, ['visibility' => 'private']);
                        \App\Models\Attachments::create([
                            'attachment' => $path,
                            'attachmentName' => 'Signature.png',
                            'category' => 'Signature',
                            'attachmentOwner' => $job_candidates->id,
                            'moduleName' => 'JobCandidates',
                        ]);
                    }
                } catch (\Throwable $e) {
                    // swallow errors to avoid blocking application submission
                }
            }
            // Persist health declaration signature if provided
            if (($data['HealthDeclarationAccepted'] ?? false) && ! empty($data['SignatureHealthDataUrl'])) {
                try {
                    $path = 'JobCandidate-attachments/signature-health-'.$job_candidates->id.'.png';
                    $base64 = $data['SignatureHealthDataUrl'];
                    $parts = explode(',', $base64, 2);
                    if (count($parts) === 2) {
                        $binary = base64_decode($parts[1]);
                        Storage::disk(config('filesystems.default'))
                            ->put($path, $binary, ['visibility' => 'private']);
                        \App\Models\Attachments::create([
                            'attachment' => $path,
                            'attachmentName' => 'Health-Declaration-Signature.png',
                            'category' => 'SignatureHealth',
                            'attachmentOwner' => $job_candidates->id,
                            'moduleName' => 'JobCandidates',
                        ]);
                    }
                } catch (\Throwable $e) {
                    // swallow errors to avoid blocking application submission
                }
            }
            // Persist uploads as attachments linked to this application
            if (! empty($data['ResumeUpload'])) {
                \App\Models\Attachments::create([
                    'attachment' => $data['ResumeUpload'],
                    'attachmentName' => basename($data['ResumeUpload']),
                    'category' => 'Resume',
                    'attachmentOwner' => $job_candidates->id,
                    'moduleName' => 'JobCandidates',
                ]);
            }
            if (! empty($data['CoverLetterUpload'])) {
                \App\Models\Attachments::create([
                    'attachment' => $data['CoverLetterUpload'],
                    'attachmentName' => basename($data['CoverLetterUpload']),
                    'category' => 'CoverLetter',
                    'attachmentOwner' => $job_candidates->id,
                    'moduleName' => 'JobCandidates',
                ]);
            }
            if (! empty($data['WrittenReferencesUpload']) && is_array($data['WrittenReferencesUpload'])) {
                foreach ($data['WrittenReferencesUpload'] as $file) {
                    \App\Models\Attachments::create([
                        'attachment' => $file,
                        'attachmentName' => basename($file),
                        'category' => 'ReferenceLetter',
                        'attachmentOwner' => $job_candidates->id,
                        'moduleName' => 'JobCandidates',
                    ]);
                }
            }
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
            // Role-driven competencies: ensure records exist
            $role = $this->findRoleForJobOpening();
            if ($role) {
                $requirements = RoleCompetencyRequirement::where('role_id', $role->id)->get();
                foreach ($requirements as $req) {
                    ApplicantCompetency::firstOrCreate(
                        [
                            'applicant_id' => $candidate->id,
                            'competency_type_id' => $req->competency_type_id,
                        ],
                        [
                            'source' => $req->is_required ? \App\Enums\ApplicantCompetencySource::ROLE_REQUIRED : \App\Enums\ApplicantCompetencySource::ROLE_OPTIONAL,
                            'sync_allowed' => true,
                            'status' => \App\Enums\ApplicantCompetencyStatus::PENDING_VERIFICATION,
                        ]
                    );
                }
            }

            // Persist all competencies from UI (role-driven prepopulated and applicant-added)
            if (! empty($data['ApplicantCompetencies']) && is_array($data['ApplicantCompetencies'])) {
                foreach ($data['ApplicantCompetencies'] as $comp) {
                    $source = $comp['source'] ?? 'APPLICANT_ADDED';
                    $syncAllowed = false;
                    $typeId = $comp['competency_type_id'] ?? null;
                    if ($typeId) {
                        $type = CompetencyType::find($typeId);
                        $syncAllowed = $type && $type->assignar_competency_id ? true : $syncAllowed;
                    }

                    ApplicantCompetency::updateOrCreate(
                        [
                            'applicant_id' => $candidate->id,
                            'competency_type_id' => $typeId,
                        ],
                        [
                            'source' => $source,
                            'sync_allowed' => $syncAllowed,
                            'status' => \App\Enums\ApplicantCompetencyStatus::PENDING_VERIFICATION,
                            'issue_date' => $comp['issue_date'] ?? null,
                            'completion_date' => $comp['completion_date'] ?? null,
                            'reference_number' => $comp['reference_number'] ?? null,
                            'front_image' => $comp['front_image'] ?? null,
                            'back_image' => $comp['back_image'] ?? null,
                            'notes' => $comp['notes'] ?? null,
                        ]
                    );
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
                Forms\Components\Section::make('Uploads')
                    ->columns(2)
                    ->schema([
                        Forms\Components\FileUpload::make('ResumeUpload')
                            ->preserveFilenames()
                            ->directory('JobCandidate-attachments')
                            ->visibility('private')
                            ->openable()
                            ->downloadable()
                            ->previewable()
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            ])
                            ->required()
                            ->label('Resume'),
                        Forms\Components\FileUpload::make('CoverLetterUpload')
                            ->preserveFilenames()
                            ->directory('JobCandidate-attachments')
                            ->visibility('private')
                            ->openable()
                            ->downloadable()
                            ->previewable()
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            ])
                            ->label('Cover Letter'),
                        Forms\Components\FileUpload::make('WrittenReferencesUpload')
                            ->preserveFilenames()
                            ->multiple()
                            ->directory('JobCandidate-attachments')
                            ->visibility('private')
                            ->openable()
                            ->downloadable()
                            ->previewable()
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'image/jpeg',
                                'image/png',
                            ])
                            ->label('Written References (optional)'),
                    ]),
                Forms\Components\Section::make('Competencies')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Repeater::make('ApplicantCompetencies')
                            ->label('Add any licences, tickets or training (optional)')
                            ->default(fn () => $this->roleDrivenDefaultCompetencies())
                            ->schema([
                                Forms\Components\Select::make('competency_type_id')
                                    ->label('Competency')
                                    ->options(fn () => CompetencyType::query()->where('active', true)->pluck('name', 'id')->toArray())
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                                Forms\Components\DatePicker::make('issue_date')->label('Issue Date'),
                                Forms\Components\DatePicker::make('completion_date')->label('Completion Date'),
                                Forms\Components\TextInput::make('reference_number')
                                    ->label(fn (callable $get) => optional(CompetencyType::find($get('competency_type_id')))->licence_number_label ?? 'Reference Number'),
                                Forms\Components\FileUpload::make('front_image')
                                    ->preserveFilenames()
                                    ->directory('ApplicantCompetency-attachments')
                                    ->visibility('private')
                                    ->openable()
                                    ->downloadable()
                                    ->previewable(),
                                Forms\Components\FileUpload::make('back_image')
                                    ->preserveFilenames()
                                    ->directory('ApplicantCompetency-attachments')
                                    ->visibility('private')
                                    ->openable()
                                    ->downloadable()
                                    ->previewable(),
                                Forms\Components\Textarea::make('notes')->label('Notes'),
                                Forms\Components\Hidden::make('source')->default('APPLICANT_ADDED'),
                            ])
                            ->columns(2)
                            ->addActionLabel('Add Competency')
                            ->deletable(true),
                    ]),
                Forms\Components\Section::make('Section 7 - Criminal History')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Radio::make('CriminalHistory')
                            ->label('Have you ever been convicted of a criminal offence?')
                            ->options(['Yes' => 'Yes', 'No' => 'No'])
                            ->required(),
                        Forms\Components\Textarea::make('CriminalDetails')
                            ->label('If yes, please provide details')
                            ->visible(fn (callable $get) => $get('CriminalHistory') === 'Yes'),
                    ]),
                Forms\Components\Section::make('Section 8 - Declaration')
                    ->columns(1)
                    ->schema([
                        Forms\Components\MarkdownEditor::make('DeclarationText')
                            ->disableToolbarButtons(['attachFiles'])
                            ->disabled()
                            ->default(<<<'MD'
I confirm that:

- I am qualified to work in Australia, and if requested, can provide evidence of that fact (Birth Certificate, Citizenship Certificate, Photo ID and/or Working Visa, as appropriate)
- All the information I submit (including this form and any attached resume) is true and complete. I understand that any false or misleading information I provide may lead to rejection of my application, review of any employment I accept and potentially my dismissal from such employment.
- I have disclosed all relevant information in relation to my mental and physical ability to safely carry out the inherent requirements of the position(s) for which I have applied.
- I understand and (a) expressly consent to the use of an electronic signature as the method of execution and/or agreement; (b) warrant that the person affixing any electronic signature within RecruitIQ has the requisite authority to execute the agreement on my behalf; (c) agree that execution by electronic signature is legally valid and binding.

Data Collection

- I understand and agree that the Company will collect information from me via this application and from other persons such as past employers, referees (nominated by me or not) and other persons, personal information about me for the purpose of assessing my suitability for employment with the Company and, should I become employed by the Company, for the purpose of managing its employment relationship with me.
MD),
                        Forms\Components\Checkbox::make('DeclarationAccepted')
                            ->label('I have read and understood the above statement.')
                            ->required(),
                        Forms\Components\TextInput::make('SignatureName')
                            ->label('Type your full name as a digital signature')
                            ->visible(fn (callable $get) => $get('DeclarationAccepted') === true)
                            ->required(fn (callable $get) => $get('DeclarationAccepted') === true),
                        SignaturePad::make('SignatureDataUrl')
                            ->label('Signature')
                            ->confirmable()
                            ->downloadable()
                            ->downloadableFormats([DownloadableFormat::PNG])
                            ->backgroundColor('rgba(255,255,255,1)')
                            ->penColor('#000000')
                            ->visible(fn (callable $get) => $get('DeclarationAccepted') === true)
                            ->required(fn (callable $get) => $get('DeclarationAccepted') === true),
                    ]),
                Forms\Components\Section::make('Section 9 - Pre-Employment Health Declaration')
                    ->columns(1)
                    ->schema([
                        Forms\Components\MarkdownEditor::make('HealthDeclarationText')
                            ->disableToolbarButtons(['attachFiles'])
                            ->disabled()
                            ->default(<<<'MD'
Employment with our company is conditional on the applicant being a fit and proper person and fully able to perform the inherent requirements of the position.

The purpose of this Pre-Employment Health Declaration is to ensure no person is placed in a working environment that may result in physical or psychological harm. This declaration is not intended to deny employment based on disability or illness. Rather, it ensures safe placement, injury prevention, and compliance with national and state legislation.

Under Work Health and Safety (WHS) and Workers’ Compensation legislation across Australia, you are legally required to disclose any pre-existing injury, illness, medical condition, or health factor that could reasonably be affected by the nature of the proposed employment or may impact your ability to safely perform your duties.

Any information provided will be treated as confidential and handled in accordance with the Privacy Act 1988 (Cth) and the Australian Privacy Principles (APPs).

Failure to disclose relevant information—or providing false or misleading information—may place you or others at risk, impact your ability to perform the role safely, affect your eligibility for workers’ compensation if a recurrence, aggravation, or exacerbation occurs, or result in withdrawal of an offer of employment or disciplinary action.

This obligation applies under the following legislation: Work Health and Safety Act 2011 (QLD), Work Health and Safety Act 2011 (NSW), Work Health and Safety Act 2011 (ACT), Occupational Health and Safety Act 2004 (VIC), Workers’ Compensation and Rehabilitation Act 2003 (QLD), Workplace Injury Management and Workers Compensation Act 1998 (NSW), Workers Compensation Act 1951 (ACT), Workplace Injury Rehabilitation and Compensation Act 2013 (VIC).
MD),
                        Forms\Components\Section::make('Applicant Health Disclosure Questions')
                            ->columns(2)
                            ->schema([
                                Forms\Components\Radio::make('HealthPreExistingCondition')
                                    ->label('Do you have an existing or pre-existing injury, illness, or medical condition that could be affected by the nature of the proposed employment?')
                                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                                    ->required(),
                                Forms\Components\Textarea::make('HealthPreExistingDetails')
                                    ->label('If yes, please provide details')
                                    ->visible(fn (callable $get) => $get('HealthPreExistingCondition') === 'Yes'),

                                Forms\Components\Radio::make('HealthMedicationTreatment')
                                    ->label('Are you currently taking any medication or undergoing any ongoing medical treatment on a regular basis (daily, weekly, monthly)?')
                                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                                    ->required(),
                                Forms\Components\Textarea::make('HealthMedicationDetails')
                                    ->label('If yes, please provide details')
                                    ->visible(fn (callable $get) => $get('HealthMedicationTreatment') === 'Yes'),

                                Forms\Components\Radio::make('HealthOtherCircumstances')
                                    ->label('Are you aware of any other circumstances regarding your health or capacity to work that may interfere with your ability to safely perform the inherent requirements of the position?')
                                    ->options(['Yes' => 'Yes', 'No' => 'No'])
                                    ->required(),
                                Forms\Components\Textarea::make('HealthOtherDetails')
                                    ->label('If yes, please provide details')
                                    ->visible(fn (callable $get) => $get('HealthOtherCircumstances') === 'Yes'),
                            ]),
                        Forms\Components\Section::make('Drug & Alcohol Testing Consent')
                            ->columns(1)
                            ->schema([
                                Forms\Components\Checkbox::make('DrugAlcoholConsent')
                                    ->label('I consent to participating in drug and alcohol testing while employed by the company.')
                                    ->required(),
                            ]),
                        Forms\Components\Checkbox::make('HealthDeclarationAccepted')
                            ->label('I understand that any wilfully incorrect or misleading answer or material omission relating to any of the questions may make me ineligible for employment or liable to disciplinary action, which may include dismissal. I understand that this pre-employment health declaration may form part of my file.')
                            ->required(),
                        Forms\Components\TextInput::make('HealthSignatureName')
                            ->label('Type your full name as a digital signature (Health Declaration)')
                            ->visible(fn (callable $get) => $get('HealthDeclarationAccepted') === true)
                            ->required(fn (callable $get) => $get('HealthDeclarationAccepted') === true),
                        SignaturePad::make('SignatureHealthDataUrl')
                            ->label('Health Declaration Signature')
                            ->confirmable()
                            ->downloadable()
                            ->downloadableFormats([DownloadableFormat::PNG])
                            ->backgroundColor('rgba(255,255,255,1)')
                            ->penColor('#000000')
                            ->visible(fn (callable $get) => $get('HealthDeclarationAccepted') === true)
                            ->required(fn (callable $get) => $get('HealthDeclarationAccepted') === true),
                    ]),
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

    private function findRoleForJobOpening(): ?JobRole
    {
        $state = $this->record->State ?? $this->record->state ?? null;
        $names = array_filter([
            self::$jobDetails?->JobTitle ?? null,
            self::$jobDetails?->postingTitle ?? null,
            $this->record->JobTitle ?? null,
            $this->record->postingTitle ?? null,
        ]);

        return JobRole::query()
            ->when($state, fn ($q) => $q->where('state', $state))
            ->whereIn('name', $names)
            ->first();
    }

    private function roleDrivenDefaultCompetencies(): array
    {
        $role = $this->findRoleForJobOpening();
        if (! $role) {
            return [];
        }
        $requirements = RoleCompetencyRequirement::where('role_id', $role->id)->get();

        return $requirements->map(function ($req) {
            return [
                'competency_type_id' => $req->competency_type_id,
                'source' => $req->is_required ? 'ROLE_REQUIRED' : 'ROLE_OPTIONAL',
                'notes' => null,
            ];
        })->values()->toArray();
    }

    #[Title('Apply Job ')]
    public function render()
    {
        return view('livewire.career-apply-job', [
            'jobDetail' => $this->record,
        ]);
    }
}
