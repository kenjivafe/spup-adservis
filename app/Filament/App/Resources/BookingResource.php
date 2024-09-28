<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\BookingResource\Pages;
use App\Filament\App\Resources\BookingResource\RelationManagers;
use App\Models\Booking;
use App\Models\Unit;
use App\Models\User;
use App\Models\Venue;
use App\Rules\BookingDateConflict;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
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

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationLabel = 'Bookings';

    protected static ?string $modelLabel = 'Booking';

    protected static ?string $navigationGroup = 'Venue Bookings';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return  $user->can('Book Venues') ||
                $user->can('Note Venue Bookings') ||
                $user->can('Approve Venue Bookings as Finance') ||
                $user->can('Be In-charge of Venues');
    }

    // Restrict creating records
    public static function canCreate(): bool
    {
        return auth()->user()->can('Book Venues');
    }

    // Restrict editing records
    public static function canEdit($record): bool
    {
        $user = auth()->user();

        return  $user->can('Book Venues') ||
                $user->can('Note Venue Bookings') ||
                $user->can('Approve Venue Bookings as Finance') ||
                $user->can('Be In-charge of Venues');
    }

    // Restrict deleting records
    public static function canDelete($record): bool
    {
        return false;
    }

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
                            ->native(false)
                            ->required()
                            ->columnSpan(['default'=>2, 'sm'=>2, 'md'=>2, 'lg'=>2, 'xl'=>2, '2xl'=>2])
                            ->placeholder('Select Venue')
                            ->label('Venue')
                            ->options(function () {
                                return Venue::select('id', 'name')->pluck('name', 'id');
                            })
                            ->reactive(),
                        Select::make('unit_id')
                            ->required()
                            ->columnSpan(['default'=>2, 'sm'=>1, 'md'=>1, 'lg'=>1, 'xl'=>1, '2xl'=>1])
                            ->native(false)
                            ->label('Unit/Department')
                            ->options(function () {
                                return Unit::select('id', 'name')->pluck('name', 'id');
                            })
                            ->reactive(),
                        Forms\Components\TextInput::make('participants')
                        ->columnSpan(['default'=>2, 'sm'=>1, 'md'=>1, 'lg'=>1, 'xl'=>1, '2xl'=>1])
                            ->label('No. of Participants')
                            ->numeric()
                            ->step(10)
                            ->required()
                            ->maxLength(255),
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
                    $query->select('bookings.*')
                        ->leftJoin('units', 'bookings.unit_id', '=', 'units.id')
                        ->leftJoin('users as unit_heads', 'units.unit_head', '=', 'unit_heads.id')
                        ->orderByRaw(
                            "CASE
                                WHEN unit_heads.id = ? THEN 0
                                ELSE 1
                            END,
                            CASE
                                WHEN bookings.status = 'Ongoing' THEN 1
                                WHEN bookings.status = 'Pending' AND bookings.noted_by IS NULL THEN 2
                                WHEN bookings.status = 'Pending' AND bookings.approved_by IS NULL THEN 3
                                WHEN bookings.status = 'Pending' AND bookings.approved_by IS NOT NULL THEN 4
                                WHEN bookings.status = 'Approved' THEN 5
                                WHEN bookings.status = 'Confirmed' THEN 6
                                WHEN bookings.status = 'Ended' THEN 7
                                WHEN bookings.status = 'Rejected' THEN 8
                                WHEN bookings.status = 'Canceled' THEN 9
                                ELSE 10
                            END, bookings.created_at DESC",
                            [$user->id]
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
                    ->multiple()
                ])
            ->actions([
                // ActionGroup::make([
                //     Tables\Actions\ViewAction::make(),
                //     Tables\Actions\EditAction::make()->color('primary'),
                // ])
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
