<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Unit;
use App\Models\User;
use App\Models\Venue;
use App\Rules\BookingDateConflict;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{
    public Model | string | null $model = Booking::class;

    public function getFormSchema(): array
    {
        return [
            Section::make()
                ->columnSpan(['default'=>2, 'sm'=>2, 'md'=>1, 'lg'=>2, 'xl'=>1, '2xl'=>1])
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
                    Select::make('venue_id')
                        ->required()
                        ->native(false)
                        ->columnSpan(['default'=>2, 'sm'=>2, 'md'=>2, 'lg'=>2, 'xl'=>2, '2xl'=>2])
                        ->placeholder('Select Venue')
                        ->label('Venue')
                        ->options(function () {
                            return Venue::select('id', 'name')->pluck('name', 'id');
                        })
                        ->reactive(),
                    Select::make('unit_id')
                        ->required()
                        ->native(false)
                        ->columnSpan(['default'=>2, 'sm'=>1, 'md'=>1, 'lg'=>1, 'xl'=>1, '2xl'=>1])
                        ->label('Unit/Department')
                        ->options(function () {
                            return Unit::select('id', 'name')->pluck('name', 'id');
                        })
                        ->reactive(),
                    TextInput::make('participants')
                        ->columnSpan(['default'=>2, 'sm'=>1, 'md'=>1, 'lg'=>1, 'xl'=>1, '2xl'=>1])
                        ->label('No. of Participants')
                        ->numeric()
                        ->step(10)
                        ->required()
                        ->maxLength(255),
                    TextInput::make('purpose')
                        ->columnSpan(['default'=>2, 'sm'=>1, 'md'=>1, 'lg'=>1, 'xl'=>1, '2xl'=>1])
                        ->required()
                        ->maxLength(255),
                    TextInput::make('fund_source')
                        ->columnSpan(['default'=>2, 'sm'=>1, 'md'=>1, 'lg'=>1, 'xl'=>1, '2xl'=>1])
                        ->label('Source of Fund')
                        ->required()
                        ->maxLength(255),
                    DateTimePicker::make('starts_at')
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
                    DateTimePicker::make('ends_at')
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
        ];
    }

    protected function handleFormSubmission(array $data): void
    {
        // Here, handle the creation or updating of Booking based on the form data
        // Since `person_responsible` is set as hidden and defaults to the logged-in user's ID,
        // it will be included automatically in $data array.

        $booking = new Booking($data);
        $booking->save();

        $this->notify('success', 'Booking created successfully.');
        $this->closeModal();
    }

    protected function modalActions(): array
    {
        return [
        ];
    }

    public function fetchEvents(array $fetchInfo): array
    {
        return Booking::query()
            ->where(function ($query) use ($fetchInfo) {
                // Fetch bookings that are either Approved, Confirmed, Ongoing, or Ended
                $query->where(function ($subQuery) {
                    $subQuery->whereIn('status', ['Approved', 'Confirmed', 'Ongoing', 'Ended']);
                })
                // Or Canceled but must have 'approved_by' not empty
                ->orWhere(function ($subQuery) {
                    $subQuery->where('status', 'Canceled')
                             ->whereNotNull('approved_by');
                });
            })
            ->where(function ($query) use ($fetchInfo) {
                // Ensure the booking dates fall within the specified fetch range
                $query->whereBetween('starts_at', [$fetchInfo['start'], $fetchInfo['end']])
                      ->orWhereBetween('ends_at', [$fetchInfo['start'], $fetchInfo['end']]);
            })
            ->get()
            ->map(function (Booking $booking) {
                // Determine the color based on the status
                $color = match ($booking->status) {
                    'Approved' => 'green',
                    'Confirmed' => 'green',
                    'Ongoing' => 'blue',
                    'Ended' => 'gray',
                    'Canceled' => 'red',
                    default => 'gray',
                };

                return [
                    'id' => $booking->id,
                    'title' => $booking->purpose . " - " . $booking->status,
                    'start' => $booking->starts_at->toIso8601String(),
                    'end' => $booking->ends_at->toIso8601String(),
                    'color' => $color,  // Apply the color based on the booking status
                    'textColor' => 'white',  // Ensuring the text is readable on all backgrounds
                ];
            })
            ->toArray();
    }

    public static function canView(): bool
    {
        return false;
    }
}
