<?php

namespace App\Filament\Resources;

use App\Filament\Exports\JobOrderExporter;
use App\Filament\Resources\JobOrderResource\Pages;
use App\Models\Equipment;
use App\Models\JobOrder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Spatie\Browsershot\Browsershot;
use Spatie\Permission\Models\Role; // Assuming Spatie Roles

class JobOrderResource extends Resource
{
    protected static ?string $model = JobOrder::class;

    protected static ?string $navigationLabel = 'Job Orders';

    protected static ?string $modelLabel = 'Job Order';

    // protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Job Order Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Job Order Details')
                    ->description('Complete the details for the job order. This form captures all necessary information for processing the job order efficiently.')
                    ->schema([
                    TextInput::make('job_order_title')
                        ->label('Title')
                        ->columnSpan(['default'=>7, 'sm'=>12, 'md'=>6,'lg'=>3, 'xl'=>4, '2xl'=>4])
                        ->required()
                        ->maxLength(255)
                        ->required(),
                    TextInput::make('unit_name')
                        ->label('Unit Name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(['default'=>7, 'sm'=>12, 'md'=>6,'lg'=>3, 'xl'=>4, '2xl'=>4]),
                    DateTimePicker::make('date_needed')
                        ->label('Date Needed')
                        ->seconds(false)
                        ->time(false)
                        ->displayFormat('M j, Y')
                        ->minDate(today())
                        ->timezone('Asia/Manila')
                        ->required()
                        ->native(false)
                        ->columnSpan(['default'=>7, 'sm'=>6, 'md'=>6,'lg'=>3, 'xl'=>2, '2xl'=>2]),
                    DateTimePicker::make('date_requested')
                        ->label('Date Requested')
                        ->placeholder(function (callable $get) {
                            $dateRequested = $get('date_requested');
                            if ($dateRequested) {
                              return $dateRequested; // Use existing value if present
                            } else {
                              return now('Asia/Manila')->format('M d, Y'); // Current date and time if null
                            }
                          })
                        ->displayFormat('M j, Y')
                        ->timezone('Asia/Manila')
                        ->seconds(false)
                        ->disabled()
                        ->native(false)
                        ->columnSpan(['default'=>7, 'sm'=>6, 'md'=>6,'lg'=>3, 'xl'=>2, '2xl'=>2]),
                    Textarea::make('particulars')
                        ->required()
                        ->rows(10)
                        ->columnSpan(['default'=>7, 'sm'=>12, 'md'=>6, 'lg'=>6, 'xl'=>4, '2xl'=>4]),
                    Textarea::make('materials')
                        ->label('Materials Needed')
                        ->placeholder('(Where materials are needed, a Requisition Form should be submitted)')
                        ->maxLength(255)
                        ->rows(10)
                        ->columnSpan(['default'=>7, 'sm'=>12, 'md'=>6, 'lg'=>6, 'xl'=>4, '2xl'=>4]),
                    Fieldset::make('Assigned to')
                        ->schema([
                            Radio::make('assigned_role')
                                ->label('')
                                ->inline()
                                ->inlineLabel(false)
                                ->columnSpan(2)
                                ->options([
                                    'Maintenance' => 'In-house Maintenance',  // 'staff' is the internal name for the Staff role
                                    'Contractor' => 'Outside Contractor'  // 'contractor' is the internal name for the Contractor role
                                ])
                                ->reactive(),
                            Select::make('assigned_to')
                                ->label('')
                                ->columnSpan(2)
                                ->options(function (callable $get) {
                                    $roleName = $get('assigned_role');
                                    return User::role($roleName)->pluck('full_name', 'id');
                                })
                                ->reactive()
                                ->placeholder(function (callable $get) {
                                    $userId = $get('assigned_to');
                                    if (is_array($userId)) { // Check if it's a collection of IDs
                                        return 'Assign to User';
                                    } else {
                                        $user = User::find($userId);
                                        return $user ? ($user->first() ? $user->first()->full_name : 'Assign to User') : 'Assign to User';
                                    }
                                }),
                            DateTimePicker::make('date_begun')
                                ->label('Begun')
                                ->disabled()
                                ->seconds(false)
                                ->native(false)
                                ->placeholder(function ($record) {
                                    if (!empty($record->date_begun)) { // explicitly define $data argument
                                        return 'Date: ' . $record->date_begun;
                                    }
                                    return null;
                                })
                                ->displayFormat('M j, Y')
                                ->columnSpan(['default'=>2, 'md'=>1, 'lg'=>1, 'xl'=>2, '2xl'=>1]),
                            DateTimePicker::make('date_completed')
                                ->label('Completed')
                                ->disabled()
                                ->seconds(false)
                                ->native(false)
                                ->displayFormat('M j, Y')
                                ->columnSpan(['default'=>2, 'md'=>1, 'lg'=>1, 'xl'=>2, '2xl'=>1]),
                        ])->columns(['default'=>1,'md'=>2,'lg'=>2, 'xl'=>1, '2xl'=>2]) ->columnSpan(['default'=>7,'md'=>12, 'lg'=>'full', 'xl'=>4, '2xl'=>4])
                        ->disabled(),
                    ])->columnSpan(12) ->columns(12)
                    ->disabled(function (callable $get) {
                        $status = $get('status');
                        $accomplishedBy = $get('accomplished_by');
                        // Disable if the status is 'Canceled', 'Rejected', 'Completed',
                        // or if 'assigned_to' is not empty.
                        return in_array($status, ['Canceled', 'Rejected', 'Completed']) || !empty($accomplishedBy);
                    }),
                Section::make('Equipments to Repair')
                    ->description('Select equipment that requires repair. This section is only needed for job orders involving equipment maintenance or repair tasks.')
                    ->schema([
                        Repeater::make('jobOrderEquipments')
                            ->relationship()
                            ->label('')
                            ->schema([
                                Select::make('equipment_id')
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->native(false)
                                    ->searchable()
                                    ->label('Property Code')
                                    ->options(Equipment::where('status', '!=', 'Disposed') // Filter available equipment
                                        ->pluck('code', 'id'))
                                    ->required()
                                    ->reactive(),
                                Toggle::make('is_repaired')
                                    ->default(false)
                                    ->label('Is equipment repaired?')
                                    ->disabled()
                                    ->dehydrated(true),
                                Hidden::make('date_repaired')
                                    ->reactive()
                                    ->afterStateUpdated(function ($set, $state) {
                                        $set('date_repaired', $state['is_repaired'] ? now() : null);
                                    }),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Add Equipment')
                            ->itemLabel(function (array $state): ?string {
                                $equipment = Equipment::find($state['equipment_id']);
                                if ($equipment) {
                                    return "{$equipment->equipmentBrand->name} {$equipment->equipmentType->name} of {$equipment->unit->name}";
                                }
                                return null;
                            })
                            ->grid(2),
                    ])->columnSpan(12),
                Section::make('Approval Flow')
                    ->description('The job order will be reviewed by the relevant authorities to ensure compliance and accuracy.')
                    ->schema([
                        Group::make([
                            Placeholder::make('requested_by')
                                ->label(new HtmlString('<span style="font-weight: lighter; color: gray;">Requested by: </span>'))
                                ->hint(function ($record) {
                                    if (!$record) {
                                        return ''; // If the record does not exist
                                    }
                                    if (!empty($record->created_at)) {
                                        return $record ? $record->created_at->format('M d, Y - h:i a') : '';
                                    }
                                })
                                ->content(function ($record) {
                                    if (!empty($record->requested_by)) {
                                        $user = User::find($record->requested_by); // explicitly define $data argument
                                        return $user->full_name . ' (Unit Head)';
                                    }
                                    return auth()->user()->full_name . ' (Unit Head)';
                                }),
                            Placeholder::make('recommended_by')
                                ->label(new HtmlString('<span style="font-weight: lighter; color: gray;">Noted/Recommended by: </span>'))
                                ->hint(function ($record) {
                                    if (!$record) {
                                        return ''; // If the record does not exist
                                    }
                                    if (!empty($record->recommended_at)) {
                                        return $record ? $record->recommended_at->format('M d, Y - h:i a') : '';
                                    }
                                })
                                ->content(function ($record) {
                                    if (!$record) {
                                        return ''; // If the record does not exist
                                    }
                                    if (!empty($record->recommended_by)) {
                                        $user = User::find($record->recommended_by);
                                        return $user ? $user->full_name . ' (Physical Plant/General Services, Head)' : '';
                                    }
                                }),
                            Placeholder::make('approved_by')
                                ->label(new HtmlString('<span style="font-weight: lighter; color: gray;">Approved by:</span>'))
                                ->hint(function ($record) {
                                    if (!$record) {
                                        return ''; // If the record does not exist
                                    }
                                    if (!empty($record->approved_at)) {
                                        return $record ? $record->approved_at->format('M d, Y - h:i a') : '';
                                    }
                                })
                                ->content(function ($record) {
                                    if (!$record) {
                                        return ''; // If the record does not exist
                                    }
                                    if (!empty($record->approved_by)) {
                                        $user = User::find($record->approved_by);
                                        return $user ?  $user->full_name : '';
                                    } else {
                                        return ''; // If the job order is created but not approved
                                    }
                                }),
                            ])->columnSpan(['default' => 6, 'sm' => 6, 'md' => 6, 'lg' => 6, 'xl' => 6, '2xl' => 6]),
                        Group::make([
                            Placeholder::make('accomplished_by')
                                ->label(new HtmlString('<span style="font-weight: lighter; color: gray;">Accomplished by:</span>'))
                                ->hint(function ($record) {
                                    if (!$record) {
                                        return ''; // If the record does not exist
                                    }
                                    if (!empty($record->accomplished_at)) {
                                        return $record ? $record->accomplished_at->format('M d, Y - h:i a') : '';
                                    }
                                })
                                ->columnSpan(['default'=>6, 'sm'=>6, 'md'=>6,'lg'=>6, 'xl'=>6, '2xl'=>6])
                                ->content(function ($record) {
                                    if (!$record) {
                                        return ''; // If the record does not exist
                                    }
                                    if (!empty($record->accomplished_by)) {
                                        $user = User::find($record->accomplished_by);
                                        return $user ? $user->full_name : '';
                                    }
                                }),
                            Placeholder::make('checked_by')
                                ->label(new HtmlString('<span style="font-weight: lighter; color: gray;">Checked by:</span>'))
                                ->hint(function ($record) {
                                    if (!$record) {
                                        return ''; // If the record does not exist
                                    }
                                    if (!empty($record->checked_at)) {
                                        return $record ? $record->checked_at->format('M d, Y - h:i a') : '';
                                    }
                                })
                                ->columnSpan(['default'=>6, 'sm'=>6, 'md'=>6,'lg'=>6, 'xl'=>6, '2xl'=>6])
                                ->content(function ($record) {
                                    if (!$record) {
                                        return ''; // If the record does not exist
                                    }
                                    if (!empty($record->checked_by)) {
                                        $user = User::find($record->checked_by);
                                        return $user ? $user->full_name . ' (Physical Plant/General Services Head)' : '';
                                    }
                                }),
                            Placeholder::make('confirmed_by')
                                ->label(new HtmlString('<span style="font-weight: lighter; color: gray;">Confirmation: Job finished as requested</span>'))
                                ->hint(function ($record) {
                                    if (!$record) {
                                        return ''; // If the record does not exist
                                    }
                                    if (!empty($record->confirmed_at)) {
                                        return $record ? $record->confirmed_at->format('M d, Y - h:i a') : '';
                                    }
                                })
                                ->columnSpan(['default'=>6, 'sm'=>6, 'md'=>6,'lg'=>6, 'xl'=>6, '2xl'=>6])
                                ->content(function ($record) {
                                    if (!$record) {
                                        return ''; // If the record does not exist
                                    }
                                    if (!empty($record->confirmed_by)) {
                                        $user = User::find($record->confirmed_by);
                                        return $user ? $user->full_name : '';
                                    }
                                }),
                            ])->columnSpan(['default' => 6, 'sm' => 6, 'md' => 6, 'lg' => 6, 'xl' => 6, '2xl' => 6]),
                    ])->columnSpan(12) ->columns(12),
                ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->hasPendingAction()) {
                            return $state . ' 🔸'; // Appending a simple red circle emoji as a notification dot
                        }
                        // Special handling for 'Assigned' status
                        if ($state === 'Assigned' && auth()->id() !== $record->assigned_to) {
                            return 'In Progress';  // Change the displayed value to 'In Progress'
                        }

                        // Return the state as is for other conditions
                        return match ($state) {
                            'Pending' => 'Pending',
                            'Rejected' => 'Rejected',
                            'Canceled' => 'Canceled',
                            'Available' => 'Available',
                            'Assigned' => 'Assigned',
                            'In Progress' => 'In Progress',
                            'Completed' => 'Completed',
                            default => $state,
                        };

                        return $state;
                    })
                    ->color(fn (string $state): string => match ($state) {
                            'Pending' => 'yellow',
                            'Canceled' => 'danger',
                            'Rejected' => 'danger',
                            'Assigned' => 'purple',
                            'In Progress' => 'purple',
                            'Completed' => 'primary',
                    })
                    ->description(function ($record) {
                        // Handle 'Pending' status
                        if ($record->status === 'Pending') {
                            return empty($record->recommended_by) ? 'In Review' : 'Recommended';
                        }

                        // Handle 'Assigned' status
                        if ($record->status === 'Assigned') {
                            return empty($record->accomplished_by) ? 'Approved' : 'Verification Pending';
                        }

                        // Handle 'Completed' status
                        if ($record->status === 'Completed') {
                            return empty($record->confirmed_by) ? 'Confirmation Pending' : 'Confirmed';
                        }

                        // Handle 'Rejected' status
                        if ($record->status === 'Rejected') {
                            return 'Reason: ' . ($record->rejection_reason ?? 'No specific reason provided');
                        }

                        // Handle 'Canceled' status
                        if ($record->status === 'Canceled') {
                            return 'Reason: ' . ($record->cancelation_reason ?? 'No specific reason provided');
                        }

                        // Default description when status doesn't fit any of the specific cases
                        return 'Status detail not available';
                    }),
                TextColumn::make('job_order_title')
                    ->label('Job Order Title'),
                TextColumn::make('unit_name')
                    ->label('Unit Name'),
                TextColumn::make('date_requested')
                    ->label('Date Requested')
                    ->sortable()
                    ->since(),
            ])
            ->defaultSort(fn ($query) => $query->orderByRaw("
                CASE
                    WHEN status = 'Pending' AND recommended_by IS NOT NULL THEN 1
                    WHEN status = 'Pending' AND recommended_by IS NULL THEN 2
                    WHEN status = 'Available' THEN 3
                    WHEN status = 'Assigned' AND accomplished_by IS NULL THEN 4
                    WHEN status = 'Assigned' AND accomplished_by IS NOT NULL THEN 5
                    WHEN status = 'Completed' AND confirmed_by IS NULL THEN 7
                    WHEN status = 'Completed' AND confirmed_by IS NOT NULL THEN 8
                    WHEN status = 'Rejected' THEN 9
                    WHEN status = 'Canceled' THEN 10
                    ELSE 11
                END, date_requested DESC
            "))
            ->actions([
                Action::make('Generate PDF')
                    ->button()
                    ->color('gray')
                    ->label('PDF')
                    ->icon('heroicon-s-document-arrow-down')
                    ->action(function (JobOrder $record) {
                        // Create HTML content using a template engine like Blade
                        $html = view('pdfs.job-order', ['jobOrder' => $record, 'title' => 'UNIV-025'])->render();

                        // Generate PDF
                        // Instantiate DOMPDF
                        $dompdf = new Dompdf();

                        // Set DOMPDF options if needed (for example, for custom margins, etc.)
                        $options = new Options();
                        $options->set('isHtml5ParserEnabled', true); // Enable HTML5 parsing
                        $options->set('isPhpEnabled', true); // Enable PHP functions like include()
                        $dompdf->setOptions($options);

                        // Load HTML content
                        $dompdf->loadHtml($html);

                        // (Optional) Set paper size and orientation (A4, Portrait/Landscape)
                        $dompdf->setPaper('A4', 'landscape');

                        // Render PDF (first pass to parse HTML and CSS)
                        $dompdf->render();

                        // Save the generated PDF to a file
                        $output = $dompdf->output();
                        $filePath = public_path('job-order-' . $record->id . '.pdf');
                        file_put_contents($filePath, $output);

                        // Return the generated PDF for download
                        return response()->download($filePath)->deleteFileAfterSend(true);
                }),
            ])
            ->filters([
                DateRangeFilter::make('created_at')->label('Date Range'),
                SelectFilter::make('unit_name')
                    ->searchable()
                    ->native(false)
                    ->options(function () {
                        return JobOrder::query()
                            ->select('unit_name')
                            ->distinct()
                            ->pluck('unit_name', 'unit_name')
                            ->toArray();
                    }),
                SelectFilter::make('status')
                    ->options([
                        'Pending' => 'Pending',
                        'Available' => 'Available',
                        'Assigned' => 'Assigned',
                        'Completed' => 'Completed',
                        'Canceled' => 'Canceled',
                        'Rejected' => 'Rejected',
                    ])
                    ->multiple()
            ])
            ->headerActions([
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
                ]);
            // ->recordAction(Tables\Actions\ViewAction::class);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobOrders::route('/'),
            'create' => Pages\CreateJobOrder::route('/create'),
            'edit' => Pages\EditJobOrder::route('/{record}/edit'),
            'view' => Pages\ViewJobOrder::route('/{record}'),
        ];
    }
}
