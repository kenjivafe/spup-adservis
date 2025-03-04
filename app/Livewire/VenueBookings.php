<?php

namespace App\Livewire;

use App\Filament\Resources\BookingResource\Pages\ViewBooking;
use App\Models\Venue;
use App\Models\Booking;
use App\Models\Unit;
use App\Models\User;
use App\Rules\BookingDateConflict;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class VenueBookings extends Component implements HasForms, HasTable
{
    use InteractsWithTable, InteractsWithForms;

    public Venue $record; // Public property to hold the current venue

    public function mount(Venue $record) // Dependency injection
    {
        $this->record = $record;
    }

    public function table(Table $table): Table
    {
        $currentVenueId = $this->record->id;
        return $table
        ->headerActions([
            CreateAction::make()
                ->model(Booking::class)
                ->action(function (array $data) {
                    // Here we mutate the data before creation
                    $data['person_responsible'] = auth()->user()->id;
                    $data['date_requested'] = now('Asia/Manila')->format('Y-m-d H:i'); // Include both date and time

                    // Now, create the booking
                    Booking::create($data);
                })
                ->form([
                    Section::make()
                        ->columnSpan(1)
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
                                    if (!empty($record->created_at)) { // explicitly define $data argument
                                        return 'Date: ' . $record->created_at->format('M d, Y');
                                    }
                                    return 'Date: ' . now()->format('M d, Y');
                                }),
                            TextInput::make('venue_name')
                                ->label('Venue')
                                ->columnSpan('full')
                                ->default(Venue::find($currentVenueId)->name ?? 'No venue')
                                ->disabled(true),
                            Hidden::make('venue_id')
                                ->default($currentVenueId),
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
                            TextInput::make('participants')
                                ->label('No. of Participants')
                                ->numeric()
                                ->step(10)
                                ->required()
                                ->maxLength(255),
                            TextInput::make('purpose')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('fund_source')
                                ->label('Source of Fund')
                                ->required()
                                ->maxLength(255),
                            DateTimePicker::make('starts_at')
                                ->seconds(false)
                                ->minDate(now()->addDays(3)->startOfMinute())  // Disallow past dates
                                ->required()
                                ->reactive()
                                ->rule(function ($get) {
                                    return new BookingDateConflict(
                                        $get('starts_at'),
                                        $get('ends_at'),
                                        $get('venue_id')
                                    );
                                }),
                            DateTimePicker::make('ends_at')
                                // ->columnSpan(['default'=>2, 'sm'=>1, 'md'=>1, 'lg'=>1, 'xl'=>2, '2xl'=>1])
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
                    Section::make('Specifications')
                        ->description('Arrangements/Things Needed')
                        ->icon('heroicon-m-information-circle')
                        ->columnSpan(['default'=>2, 'sm'=>2, 'md'=>1, 'lg'=>2, 'xl'=>1, '2xl'=>1])
                        ->schema([
                            MarkdownEditor::make('specifics')
                                ->label(''),
                        ]),
                    Section::make()
                        ->columnSpan(['default'=>2, 'sm'=>2, 'md'=>1, 'lg'=>2, 'xl'=>1, '2xl'=>1])
                            ->columns(2)
                            ->schema([
                                Placeholder::make('noted_by')
                                    ->columnSpan('full')
                                    ->label('')
                                    ->hint('Noted by:')
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
                                    ->label('')
                                    ->hint('Approved by:')
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
                                    ->label('')
                                    ->hint('Received by:')
                                    ->content(function ($record) {
                                        if (!empty($record->received_by)) {
                                            $user = User::find($record->received_by); // Fetch user details based on approved_by field
                                            return $user ? $user->full_name : 'User not found';
                                        } else {
                                            return ' '; // If status is null, return an empty space
                                        }
                                    }),
                            ])
                        ]),
                ])
            ->query(Booking::where('venue_id', $currentVenueId))
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
                            case 'Confirmed':
                                return 'Received by ' . $received_by;
                            case 'Unavailable':
                                return 'Booking Date Conflict';
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
            ->actions([
                ActionGroup::make([
                    Action::make('view')
                    ->color('gray')
                    ->icon('heroicon-m-eye')
                    ->label('View')
                    ->modalHeading('Booking Details')
                    ->modalSubmitAction(false)
                    ->form([
                        Section::make()
                            ->columnSpan(1)
                            ->columns(2)
                            ->schema([
                                Placeholder::make('person_responsible')
                                    ->label('Person Responsible: ')
                                    ->content(function ($record) {
                                        if (!empty($record->person_responsible)) {
                                            $user = User::find($record->person_responsible); // explicitly define $data argument
                                            return $user->full_name;
                                        }
                                        return auth()->user()->full_name;;
                                    }),
                                Placeholder::make('created_at')
                                    ->label(' ')
                                    ->hint(function ($record) {
                                        if (!empty($record->created_at)) { // explicitly define $data argument
                                            return 'Date Submitted: ' . $record->created_at->format('M d, Y');
                                        }
                                        return 'Date: ' . now()->format('M d, Y');
                                    }),
                                TextInput::make('venue_id')
                                    ->disabled()
                                    ->columnSpan(2)
                                    ->placeholder(function ($record) {
                                        $venue = Venue::find($record->venue_id);
                                        return $venue->name;
                                    })
                                    ->label('Venue'),
                                TextInput::make('unit_name')
                                    ->label('Unit/Department')
                                    ->placeholder(function ($record) {
                                        return $record->unit_name;
                                    })
                                    ->disabled(),
                                TextInput::make('participants')
                                    ->label('No. of Participants')
                                    ->placeholder(function ($record) {
                                        return $record->participants;
                                    })
                                    ->disabled(),
                                TextInput::make('purpose')
                                    ->placeholder(function ($record) {
                                        return $record->purpose;
                                    })
                                    ->disabled(),
                                TextInput::make('fund_source')
                                    ->label('Source of Fund')
                                    ->placeholder(function ($record) {
                                        return $record->fund_source;
                                    })
                                    ->disabled(),
                                DateTimePicker::make('starts_at')
                                    ->placeholder(function ($record) {
                                        return $record->starts_at->format('m/d/Y G:i a');
                                    })
                                    ->native(false)
                                    ->seconds(false)
                                    ->disabled(),
                                DateTimePicker::make('ends_at')
                                    ->placeholder(function ($record) {
                                        return $record->starts_at->format('m/d/Y G:i a');
                                })
                                    ->native(false)
                                    ->seconds(false)
                                    ->disabled(),
                                ]),
                        Section::make()
                            ->columnSpan(1)
                            ->schema([
                                MarkdownEditor::make('specifics')
                                    ->label('Specifications')
                                    ->placeholder(function ($record) {
                                        return $record->specifics ?? 'N/A';
                                    })
                                    ->hint('(Arrangements/Things Needed)')
                                    ->disabled(),
                                ])
                            ]),
                EditAction::make()
                    ->color('primary')
                    ->action(function (array $data, Booking $booking) {  // Ensure that $booking is being passed correctly
                        // Set the person responsible
                        $data['person_responsible'] = auth()->user()->id;

                        // Update the booking
                        $booking->update($data);
                        })
                    ->form([
                        Section::make()
                            ->columnSpan(1)
                            ->columns(2)
                            ->schema([
                                Placeholder::make('person_responsible')
                                    ->label('Person Responsible: ')
                                    ->content(function ($record) {
                                        if (!empty($record->person_responsible)) {
                                            $user = User::find($record->person_responsible); // explicitly define $data argument
                                            return $user->full_name;
                                        }
                                        return auth()->user()->full_name;;
                                    }),
                                Placeholder::make('created_at')
                                    ->label(' ')
                                    ->hint(function ($record) {
                                        if (!empty($record->created_at)) { // explicitly define $data argument
                                            return 'Date: ' . $record->created_at;
                                        }
                                        return 'Date: ' . now()->format('M d, Y');
                                    }),
                                TextInput::make('venue_name')
                                    ->label('Venue')
                                    ->columnSpan('full')
                                    ->placeholder(Venue::find($currentVenueId)->name ?? 'No venue')
                                    ->disabled(true),
                                Hidden::make('venue_id')
                                    ->default($currentVenueId),
                                TextInput::make('unit_name')
                                    ->label('Unit/Department')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('participants')
                                    ->label('No. of Participants')
                                    ->numeric()
                                    ->step(10)
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('purpose')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('fund_source')
                                    ->label('Source of Fund')
                                    ->required()
                                    ->maxLength(255),
                                DateTimePicker::make('starts_at')
                                    ->seconds(false)
                                    ->minDate(now()->addDays(3)->startOfMinute())  // Disallow past dates
                                    ->required()
                                    ->reactive(),
                                DateTimePicker::make('ends_at')
                                    ->seconds(false)
                                    ->minDate(fn ($get) => $get('starts_at') ? Carbon::parse($get('starts_at'))->addDay()->startOfMinute() : now()->addDays(4)->startOfMinute())
                                    ->required(),
                                ]),
                        Section::make()
                            ->columnSpan(1)
                            ->schema([
                                MarkdownEditor::make('specifics')
                                    ->label('Specifications')
                                    ->hint('(Arrangements/Things Needed)'),
                                ])
                            ]),
                ]),
                    ])
            ->recordAction(ViewAction::class)
            ->recordUrl(null)
            ->filters([
                // SelectFilter::make('venue_id')
                //     ->label('Venue')
                //     ->options(Venue::all()->pluck('name', 'id'))
            ]);
    }

    public static function getPages(): array
    {
        return [
            'view' => ViewBooking::route('/{record}'),
        ];
    }
}
