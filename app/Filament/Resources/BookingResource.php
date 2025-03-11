<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\BookingResource\RelationManagers;
use App\Models\Booking;
use App\Models\Unit;
use App\Models\User;
use App\Models\Venue;
use App\Rules\BookingDateConflict;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Spatie\Browsershot\Browsershot;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationLabel = 'Bookings';

    protected static ?string $modelLabel = 'Booking';

    protected static ?string $navigationGroup = 'Venue Bookings';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                ->columnSpan(['default'=>2, 'sm'=>2, 'md'=>1, 'lg'=>2, 'xl'=>1, '2xl'=>1])
                    ->columns(2)
                    ->schema([
                        Placeholder::make('person_responsible')
                            ->columnSpan(2)
                            ->label('Person Responsible: ')
                            ->content(function ($record) {
                                if (!empty($record->person_responsible)) {
                                    $user = User::find($record->person_responsible); // explicitly define $data argument
                                    return $user->full_name;
                                }
                                return auth()->user()->full_name;;
                            })
                            ->hint(function ($record) {
                                if (!empty($record->date_requested)) { // explicitly define $data argument
                                    return 'Date: ' . $record->date_requested->format('M d, Y');
                                }
                                return 'Date: ' . now()->format('M d, Y');
                            }),
                        Select::make('venue_id')
                            ->searchable()
                            ->native(false)
                            ->required()
                            ->columnSpan(['default'=>2, 'sm'=>2, 'md'=>2, 'lg'=>2, 'xl'=>2, '2xl'=>2])
                            ->placeholder('Select Venue')
                            ->label('Venue')
                            ->options(function () {
                                return Venue::select('id', 'name')->pluck('name', 'id');
                            })
                            ->reactive(),
                        Hidden::make('unit_id')
                            ->default(function () {
                                // Fetch the unit name for the authenticated user
                                $user = auth()->user();
                                return $user->unit ? $user->unit->id : 'No Unit Assigned'; // Adjust as necessary for your relationships
                            }),
                        TextInput::make('unit_name')
                            ->label('Unit/Department')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(['default'=>2, 'sm'=>1, 'md'=>1, 'lg'=>1, 'xl'=>1, '2xl'=>1])
                            ->default(function () {
                                // Fetch the unit name for the authenticated user
                                $user = auth()->user();
                                return $user->unit ? $user->unit->name : 'No Unit Assigned'; // Adjust as necessary for your relationships
                            })
                            ->placeholder(function () {
                                // Fetch the unit name for the authenticated user
                                $user = auth()->user();
                                return $user->unit ? $user->unit->name : 'No Unit Assigned'; // Adjust as necessary for your relationships
                            })
                            ->readOnly(),
                        Forms\Components\TextInput::make('participants')
                            ->columnSpan(['default'=>2, 'sm'=>1, 'md'=>1, 'lg'=>1, 'xl'=>1, '2xl'=>1])
                            ->label('No. of participants')
                            ->numeric()
                            ->step(10)
                            ->required()
                            ->maxLength(255)
                            ->rule(function (callable $get) {
                                $venueId = $get('venue_id'); // Get selected venue ID
                                $capacity = \App\Models\Venue::find($venueId)?->capacity ?? null;

                                return $capacity ? "max:$capacity" : null;
                            }, 'The number of participants cannot exceed the venue capacity.')
                            ->reactive(),
                        Forms\Components\TextInput::make('purpose')
                            ->columnSpan(['default'=>2, 'sm'=>1, 'md'=>1, 'lg'=>1, 'xl'=>1, '2xl'=>1])
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('fund_source')
                            ->columnSpan(['default'=>2, 'sm'=>1, 'md'=>1, 'lg'=>1, 'xl'=>1, '2xl'=>1])
                            ->label('Source of Fund')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->columnSpan(['default'=>2, 'sm'=>1, 'md'=>1, 'lg'=>1, 'xl'=>2, '2xl'=>1])
                            ->seconds(false)
                            ->minDate(today()->addDays(3)->startOfMinute())  // Disallow past dates
                            ->required()
                            ->reactive()
                            ->rule(function ($get) {
                                return new BookingDateConflict(
                                    $get('starts_at'),
                                    $get('ends_at'),
                                    $get('venue_id')
                                );
                            }),
                        Forms\Components\DateTimePicker::make('ends_at')
                        ->columnSpan(['default'=>2, 'sm'=>1, 'md'=>1, 'lg'=>1, 'xl'=>2, '2xl'=>1])
                            ->seconds(false)
                            ->minDate(fn ($get) => $get('starts_at') ? Carbon::parse($get('starts_at'))->addHour()->startOfMinute() : today()->addDays(3)->addHour()->startOfMinute())
                            ->required()
                            ->rule(function ($get) {
                                return new BookingDateConflict(
                                    $get('starts_at'),
                                    $get('ends_at'),
                                    $get('venue_id')
                                );
                            }),
                        ]),
                    Group::make([
                        Section::make('Specifications')
                            ->description('Arrangements/Things Needed')
                            ->icon('heroicon-m-information-circle')
                            ->columnSpan(['default'=>2, 'sm'=>2, 'md'=>1, 'lg'=>2, 'xl'=>1, '2xl'=>1])
                            ->schema([
                                Forms\Components\MarkdownEditor::make('specifics')
                                    ->maxLength(255)
                                    ->label(''),
                            ]),
                        Section::make()
                            ->columnSpan(['default'=>2, 'sm'=>2, 'md'=>1, 'lg'=>2, 'xl'=>1, '2xl'=>1])
                            ->columns(2)
                            ->schema([
                                Placeholder::make('noted_by')
                                    ->columnSpan('full')
                                    ->label(new HtmlString('<span style="font-weight: lighter; color: gray;">Noted by: </span>'))
                                    ->hint(function ($record) {
                                        if (!$record) {
                                            return ''; // If the record does not exist
                                        }
                                        if (!empty($record->noted_at)) {
                                            return $record ? $record->noted_at->format('M d, Y - h:i a') : '';
                                        }
                                    })
                                    ->content(function ($record) {
                                        if (!empty($record->noted_by)) {
                                            $user = User::find($record->noted_by); // Fetch user details based on approved_by field
                                            return $user ? $user->full_name : 'User not found';
                                        } else {
                                            return ' '; // If status is null, return an empty space
                                        }
                                    }),
                                Placeholder::make('approved_by')
                                    ->columnSpan('full')
                                    ->label(new HtmlString('<span style="font-weight: lighter; color: gray;">Approved by: </span>'))
                                    ->hint(function ($record) {
                                        $approvalDates = [];

                                        // Fetch Admin approval details if available
                                        if (!empty($record->approved_at)) {
                                            $approvalDates[] = $record ? $record->approved_at->format('M d, Y - h:i a') . ' (VPA)' : '';
                                        }

                                        // Fetch Finance approval details if available
                                        if (!empty($record->approved_by_finance)) {
                                            $approvalDates[] = $record ? $record->approved_by_finance_at->format('M d, Y - h:i a') . ' (VPF)' : '';
                                        }

                                        // Return formatted string of names or default message
                                        if (count($approvalDates) > 0) {
                                            return implode(' & ', $approvalDates);
                                        } else {
                                            return ' ';
                                        }
                                    })
                                    ->content(function ($record) {
                                        $approvalNames = [];

                                        // Fetch Admin approval details if available
                                        if (!empty($record->approved_by)) {
                                            $admin = User::find($record->approved_by);
                                            if ($admin) {
                                                $approvalNames[] = $admin->full_name . ' (VP Admin)';
                                            }
                                        }

                                        // Fetch Finance approval details if available
                                        if (!empty($record->approved_by_finance)) {
                                            $finance = User::find($record->approved_by_finance);
                                            if ($finance) {
                                                $approvalNames[] = $finance->full_name . ' (VP Finance)';
                                            }
                                        }

                                        // Return formatted string of names or default message
                                        if (count($approvalNames) > 0) {
                                            return implode(' and ', $approvalNames);
                                        } else {
                                            return ' ';
                                        }
                                    }),
                                Placeholder::make('received_by')
                                    ->columnSpan('full')
                                    ->label(new HtmlString('<span style="font-weight: lighter; color: gray;">Received by: </span>'))
                                    ->hint(function ($record) {
                                        if (!$record) {
                                            return ''; // If the record does not exist
                                        }
                                        if (!empty($record->received_at)) {
                                            return $record ? $record->received_at->format('M d, Y - h:i a') : '';
                                        }
                                    })
                                    ->content(function ($record) {
                                        if (!empty($record->received_by)) {
                                            $user = User::find($record->received_by); // Fetch user details based on approved_by field
                                            return $user ? $user->full_name : 'User not found';
                                        } else {
                                            return ' '; // If status is null, return an empty space
                                        }
                                    }),
                                ]),
                        Section::make()
                            ->columnSpan(['default'=>2, 'sm'=>2, 'md'=>1, 'lg'=>2, 'xl'=>1, '2xl'=>1])
                            ->columns(2)
                            ->schema([
                            Placeholder::make('actual_started_at')
                                ->columnSpan('1')
                                ->label('')
                                ->hint('Actual time booking started: ')
                                ->content(function ($record) {
                                    return $record && $record->actual_started_at
                                        ? $record->actual_started_at->format('M d, Y h:i a')
                                        : 'N/A';
                                }),
                            Placeholder::make('actual_ended_at')
                                ->columnSpan('1')
                                ->label('')
                                ->hint('Actual time booking ended: ')
                                ->content(function ($record) {
                                    return $record && $record->actual_started_at
                                        ? $record->actual_started_at->format('M d, Y h:i a')
                                        : 'N/A';
                                }),
                            ])->visible(fn ($record) => !empty($record)),
                    ])->columnSpan(['default'=>2, 'sm'=>2, 'md'=>1, 'lg'=>2, 'xl'=>1, '2xl'=>1])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->hasPendingAction()) {
                            return $state . ' 🔸'; // Appending a simple red circle emoji as a notification dot
                        }                        // Return the state as is for other conditions
                        return match ($state) {
                            'Pending' => 'Pending',
                            'Rejected' => 'Rejected',
                            'Canceled' => 'Canceled',
                            'Approved' => 'Approved',
                            'Unavailable' => 'Unavailable',
                            'Confirmed' => 'Confirmed',
                            'Ongoing' => 'Ongoing',
                            'Ended' => 'Ended',
                            default => $state,
                        };
                        return $state;
                    })
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending' => 'yellow',
                        'Canceled' => 'danger',
                        'Unavailable' => 'warning',
                        'Approved' => 'primary',
                        'Rejected' => 'danger',
                        'Confirmed' => 'primary',
                        'Ongoing' => 'blue',
                        'Ended' => 'gray'
                    })
                    ->description(function ($record) {
                        // Fetch user details
                        $person_responsible = $record->personResponsible ? $record->personResponsible->full_name : 'Unknown';
                        $noted_by = $record->notedBy ? $record->notedBy->full_name : null;
                        $approved_by = $record->approvedBy ? $record->approvedBy->full_name : null;
                        $received_by = $record->receivedBy ? $record->receivedBy->full_name : null;
                        $rejected_by = $record->rejectedBy ? $record->rejectedBy->full_name : null;
                        $canceled_by = $record->canceledBy ? $record->canceledBy->full_name : null;

                        switch ($record->status) {
                            case 'Pending':
                                if (!$noted_by) {
                                    return 'Submitted by ' . $person_responsible;
                                } elseif ($noted_by && !$approved_by) {
                                    return 'Pending for VP Admin\'s Approval';
                                } elseif ($approved_by) {
                                    return 'Pending for VP Finance\'s Approval';
                                }
                                break;
                            case 'Approved':
                                return 'Pending for Facilitator\'s Reception';
                            case 'Unavailable':
                                return 'Booking Date Conflict';
                            case 'Confirmed':
                                return 'Received by ' . $received_by;
                            case 'Rejected':
                                return 'Reason: ' . $record->rejection_reason;
                            case 'Canceled':
                                return 'Reason: ' . $record->cancelation_reason;
                            case 'Ongoing':
                                return 'Ends ' . $record->ends_at->diffForHumans();
                            case 'Ended':
                                return 'Ended ' . $record->ends_at->diffForHumans();
                            default:
                                return '';
                        }
                    }),
                TextColumn::make('purpose'),
                TextColumn::make('venue.name'),
                TextColumn::make('starts_at')
                    ->label('Event Start Date')
                    ->dateTime('M d, Y \a\t g:iA')
                    ->sortable(),
                TextColumn::make('date_requested')
                    ->label('Date Requested')
                    ->dateTime()
                    ->formatStateUsing(function ($state) {
                        $date = Carbon::parse($state);
                        $now = Carbon::now();

                        if ($date->isToday()) {
                            return $date->diffForHumans();  // Shows relative time like '3 hours ago'
                        } elseif ($date->isYesterday()) {
                            return 'Yesterday';
                        } elseif ($date->isCurrentYear()) {
                            return $date->format('d, F');  // Shows 'd, F' for dates two days ago or older
                        } else {
                            return $date->format('d, F Y');
                        }
                    })
                    ->sortable(),
                TextColumn::make('participants'),
            ])
            ->defaultSort(function ($query) {
                $user = auth()->user();
                // Directly implement the sorting logic based on user permissions
                if ($user->can('Manage Venue Bookings')) {
                    $query->orderByRaw(
                        "CASE WHEN status = 'Pending' AND approved_by IS NULL THEN 1
                              WHEN status = 'Ongoing' THEN 2
                              WHEN status = 'Pending' AND noted_by IS NULL THEN 3
                              WHEN status = 'Pending' AND approved_by IS NOT NULL THEN 4
                              WHEN status = 'Approved' THEN 5
                              WHEN status = 'Confirmed' THEN 6
                              WHEN status = 'Ended' THEN 7
                              WHEN status = 'Rejected' THEN 8
                              WHEN status = 'Canceled' THEN 9
                              ELSE 10
                         END, created_at DESC"
                    );
                } elseif ($user->can('Book Venues')) {
                    $query->orderByRaw(
                        "CASE
                            WHEN person_responsible = '{$user->id}' THEN 0
                            ELSE 1
                        END,
                        CASE WHEN status = 'Ongoing' THEN 1
                              WHEN status = 'Pending' AND noted_by IS NULL THEN 2
                              WHEN status = 'Pending' AND approved_by IS NULL THEN 3
                              WHEN status = 'Pending' AND approved_by IS NOT NULL THEN 4
                              WHEN status = 'Approved' THEN 5
                              WHEN status = 'Confirmed' THEN 6
                              WHEN status = 'Ended' THEN 7
                              WHEN status = 'Rejected' THEN 8
                              WHEN status = 'Canceled' THEN 9
                              ELSE 10
                         END, created_at DESC"
                    );
                } elseif ($user->can('Note Venue Bookings')) {
                    $query->leftJoin('units', 'bookings.unit_id', '=', 'units.id')
                          ->leftJoin('users as unit_heads', 'units.unit_head', '=', 'unit_heads.id')
                          ->orderByRaw(
                              "CASE
                                  WHEN unit_heads.id = '{$user->id}' THEN 0
                                  ELSE 1
                              END,
                              CASE
                                  WHEN status = 'Ongoing' THEN 1
                                  WHEN status = 'Pending' AND noted_by IS NULL THEN 2
                                  WHEN status = 'Pending' AND approved_by IS NULL THEN 3
                                  WHEN status = 'Pending' AND approved_by IS NOT NULL THEN 4
                                  WHEN status = 'Approved' THEN 5
                                  WHEN status = 'Confirmed' THEN 6
                                  WHEN status = 'Ended' THEN 7
                                  WHEN status = 'Rejected' THEN 8
                                  WHEN status = 'Canceled' THEN 9
                                  ELSE 10
                              END, bookings.created_at DESC"
                          );
                } elseif ($user->can('Approve Venue Bookings as Finance')) {
                    $query->orderByRaw(
                        "CASE WHEN status = 'Pending' AND approved_by IS NOT NULL THEN 1
                              WHEN status = 'Pending' AND approved_by IS NULL THEN 2
                              WHEN status = 'Pending' AND noted_by IS NULL THEN 3
                              WHEN status = 'Ongoing' THEN 4
                              WHEN status = 'Approved' THEN 5
                              WHEN status = 'Confirmed' THEN 6
                              WHEN status = 'Ended' THEN 7
                              WHEN status = 'Rejected' THEN 8
                              WHEN status = 'Canceled' THEN 9
                              ELSE 10
                         END, created_at DESC"
                    );
                } elseif ($user->can('Be In-charge of Venues')) {
                    $query->orderByRaw(
                        "CASE WHEN status = 'Approved' THEN 1
                              WHEN status = 'Ongoing' THEN 2
                              WHEN status = 'Pending' AND approved_by IS NULL THEN 3
                              WHEN status = 'Pending' AND approved_by IS NOT NULL THEN 4
                              WHEN status = 'Pending' AND approved_by IS NULL THEN 5
                              WHEN status = 'Confirmed' THEN 6
                              WHEN status = 'Ended' THEN 7
                              WHEN status = 'Rejected' THEN 8
                              WHEN status = 'Canceled' THEN 9
                              ELSE 10
                         END, created_at DESC"
                    );
                } else {
                    // Fallback sort order for other users
                    $query->orderBy('created_at', 'DESC');
                }
            })
            ->filters([
                DateRangeFilter::make('created_at')->label('Date Range'),
                SelectFilter::make('venue_id')
                    ->label('Venue')
                    ->searchable()
                    ->native(false)
                    ->options(function () {
                        return Venue::query()
                            ->select('id', 'name')
                            ->distinct()
                            ->pluck('name', 'id')
                            ->toArray();
                    }),
                SelectFilter::make('status')
                    ->options([
                        'Pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Confirmed' => 'Confirmed',
                        'Ongoing' => 'Ongoing',
                        'Ended' => 'Ended',
                        'Unavailable' => 'Unavailable',
                        'Rejected' => 'Rejected',
                        'Canceled' => 'Canceled',
                    ])
                    ->multiple(),
                ])
            ->actions([
                Action::make('Generate PDF')
                    ->button()
                    ->color('gray')
                    ->label('PDF')
                    ->icon('heroicon-s-document-arrow-down')
                    ->action(function (Booking $record) {
                        // Create HTML content using a template engine like Blade
                        $html = view('pdfs.venue-booking', ['booking' => $record, 'title' => 'UNIV-029'])->render();

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
                        $filePath = public_path('venue-booking-' . $record->id . '.pdf');
                        file_put_contents($filePath, $output);

                        // Return the generated PDF for download
                        return response()->download($filePath)->deleteFileAfterSend(true);
                    }),
            ]);
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
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'view' => Pages\ViewBooking::route('/{record}'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
