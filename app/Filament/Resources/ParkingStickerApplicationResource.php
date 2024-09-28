<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParkingStickerApplicationResource\Pages;
use App\Filament\Resources\ParkingStickerApplicationResource\RelationManagers;
use App\Livewire\ApplicationWaiver;
use App\Models\Department;
use App\Models\ParkingLimit;
use App\Models\ParkingStickerApplication;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class ParkingStickerApplicationResource extends Resource
{
    protected static ?string $model = ParkingStickerApplication::class;

    protected static ?string $navigationLabel = 'All Applications';

    protected static ?string $modelLabel = 'Application';

    protected static ?string $navigationGroup = 'Parking Sticker Applications';

    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Application Form')
                        ->icon('heroicon-s-clipboard-document-list')
                        ->description(function ($record) {
                            if (!empty($record->created_at)) { // explicitly define $data argument
                                return 'Submitted on: ' . $record->created_at->format('M d, Y');
                            }
                            return 'Submitted on: ' . now()->format('M d, Y');
                        })
                        ->schema([
                            Section::make()
                                ->schema([
                                    Placeholder::make('applicant')
                                        ->label('')
                                        ->hint('Name of Applicant:')
                                        ->content(function ($record) {
                                            if (!empty($record->applicant_id)) {
                                                $user = User::find($record->applicant_id);
                                                // Get first role name; adjust accordingly if multiple roles per user are expected
                                                $roleName = $user->getRoleNames()->first() ?? 'No Role';
                                                return $user->full_name . ' (' . $roleName . ')';
                                            }
                                            // Default to authenticated user
                                            $roleName = auth()->user()->getRoleNames()->first() ?? 'No Role';
                                            return auth()->user()->full_name . ' (' . $roleName . ')';
                                        })->columnSpan(6),
                                    Placeholder::make('contact_number')
                                        ->label('')
                                        ->hint('Contact Number:')
                                        ->content(function ($record) {
                                            if (!empty($record->applicant_id)) {
                                                $user = User::find($record->applicant_id);
                                                return $user->phone;
                                            }
                                            return auth()->user()->phone;
                                        })->columnSpan(6),
                                    Radio::make('parking_type')
                                        ->label('Parking Type')
                                        ->inline()
                                        ->inlineLabel(false)
                                        ->options([
                                            'full_parking' => 'Full Parking',
                                            'drop_off' => 'Drop-Off',
                                        ])
                                        ->reactive()
                                        ->afterStateUpdated(fn ($state, callable $set) => $set('department_id', null))
                                        ->afterStateUpdated(fn ($state, callable $set) => $set('vehicle_id', null))
                                        ->columnSpan(6),
                                    Select::make('department_id')
                                        ->native(false)
                                        ->label('Department')
                                        ->options(Department::all()->pluck('name', 'id'))
                                        ->reactive()
                                        ->visible(fn (callable $get) => $get('parking_type') === 'full_parking')
                                        ->afterStateUpdated(fn ($state, callable $set) => $set('vehicle_id', null))
                                        ->columnSpan(6),
                                    Select::make('vehicle_id')
                                        ->columnSpan(['default'=>6, 'sm'=>6, 'md'=>2, 'lg'=>6, 'xl'=>2, '2xl'=>2])
                                        ->label('Vehicle Type')
                                        ->native(false)
                                        ->required()
                                        ->options(function (callable $get) {
                                            if ($get('parking_type') === 'full_parking' && $get('department_id')) {
                                                return Vehicle::all()->pluck('type', 'id');
                                            }
                                            if ($get('parking_type') === 'drop_off') {
                                                return Vehicle::all()->pluck('type', 'id');
                                            }
                                            return [];
                                        })
                                        ->reactive()  // Ensures the form reacts to changes
                                        ->afterStateUpdated(function ($state, $get, $set) {
                                            if ($state) {
                                                $vehicle = Vehicle::find($state);
                                                $sticker_cost = $vehicle ? '₱' . number_format($vehicle->sticker_cost,) : '₱_______';
                                                $set('sticker_cost', $sticker_cost);  // Update the placeholder content
                                            }
                                        })
                                        ->helperText(function (callable $get) {
                                            $departmentId = $get('department_id');
                                            $vehicleId = $get('vehicle_id');

                                            if ($departmentId && $vehicleId) {
                                                $vehicle = Vehicle::find($vehicleId);
                                                $limit = ParkingLimit::where('department_id', $departmentId)
                                                    ->where('vehicle_category', $vehicle->category)
                                                    ->first();

                                                $currentCount = ParkingStickerApplication::where('department_id', $departmentId)
                                                    ->whereHas('vehicle', function ($query) use ($vehicle) {
                                                        $query->where('category', $vehicle->category);
                                                    })
                                                    ->where('status', 'Active') // Only count active applications
                                                    ->count();

                                                if ($limit) {
                                                    $remainingSlots = $limit->limit - $currentCount;
                                                    return "{$remainingSlots}/{$limit->limit} slots remaining.";
                                                }
                                            }

                                            return null;
                                        }),
                                    ColorPicker::make('vehicle_color')->required()->label('Color')
                                        ->columnSpan(['default'=>3, 'sm'=>3, 'md'=>2, 'lg'=>6, 'xl'=>2, '2xl'=>2]),
                                    TextInput::make('plate_number')->required()->label('Plate No.')
                                        ->columnSpan(['default'=>3, 'sm'=>3, 'md'=>2, 'lg'=>6, 'xl'=>2, '2xl'=>2]),
                                ])->columnSpan(1)->columns(6),
                            Section::make([
                                Placeholder::make('vehicle.vehicle_cost')
                                ->label('Sir/Madam:')
                                ->content(function ($get, $record) {
                                    $stickerCost = '₱______'; // Default to "₱____"
                                    // Check for record existence and selected vehicle
                                    if ($record && $record->vehicle) {
                                        $stickerCost = '₱' . number_format($record->vehicle->sticker_cost, 2);
                                    } else if ($get('vehicle_id')) {
                                        $vehicle = Vehicle::find($get('vehicle_id'));
                                        $stickerCost = $vehicle ? '₱' . number_format($vehicle->sticker_cost, 2) : '₱______';
                                    }
                                    return 'I respectfully apply for a SPUP vehicle entrance/parking sticker. I am willing to pay the amount of ' . $stickerCost . ' for the sticker which is good for one school year and should be transferrable and that it is a privilege subject to present and future SPUP regulations. It may be withdrawn for cause by SPUP. The privilege to park in the designated areas is on a first come-first served basis. Parking may not be available during special occasions.';
                                })->columnSpan(1),
                                SignaturePad::make('signature')->columnSpan(1)
                                    ->required()
                                    ->label(__('Signature of Applicant'))
                                    ->dotSize(2.0)
                                    ->lineMinWidth(0.5)
                                    ->lineMaxWidth(2.5)
                                    ->throttle(16)
                                    ->minDistance(5)
                                    ->velocityFilterWeight(0.7)
                            ])->columnSpan(1)
                        ])->columns(2),
                    Step::make('Attachments')
                        ->icon('heroicon-s-clipboard-document')
                        ->description('Attached are proofs of vehicle ownership and my affiliation with SPUP:')
                        ->schema([
                            FileUpload::make('orcr_attachment')
                                ->required()
                                ->columnSpan(1)
                                ->label('Latest OR and CR of the Vehicle')
                                ->image(),
                            FileUpload::make('assessment_attachment')
                                ->required()
                                ->columnSpan(1)
                                ->label('Latest Assessment of Student for the Current S.Y.')
                                ->image()
                        ])->disabled(function ($record) {
                            // Disable the form if the status is not 'Pending'
                            return $record ? $record->status !== 'Pending' : false;
                        })->columnSpan(2)->columns(2),
                ])->disabled(function ($record) {
                    // Disable the form if the status is not 'Pending'
                    return $record ? $record->status !== 'Pending' : false;
                })->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('status') // Assuming a 'status' field exists
                    ->label('Status')
                    ->color(fn (string $state): string => match ($state) {
                        'Pending' => 'yellow',
                        'Rejected' => 'danger',
                        'Revoked' => 'danger',
                        'Active' => 'primary',
                        'Expired' => 'warning'
                    })
                    ->description(function (ParkingStickerApplication $record): string {
                        // Check for active status and expiry
                        if ($record->status === 'Active') {
                            return 'Expires ' . $record->expiration_date->format('Y, M d');
                        }

                        // Check for rejection
                        if ($record->status === 'Rejected' && $record->rejected_by) {
                            $rejectionReason = $record->rejection_reason;
                            return 'Reason: ' . $rejectionReason;
                        }

                        // Check for revocation
                        if ($record->status === 'Revoked' && $record->revoked_by) {
                            $revocationReason = $record->revocation_reason;
                            return 'Reason: ' . $revocationReason;
                        }

                        if ($record->status === 'Expired' && $record->expiration_date) {
                            return 'Expired ' . $record->expiration_date->diffForHumans();
                        }

                        // Default to submitted status
                        if ($record->applicant) {
                            $applicant = User::find($record->applicant_id);
                            $applicantName = $applicant ? $applicant->full_name : 'Unknown';
                            return 'Under Review';
                        }

                        return "No actions taken yet.";
                    }),
                Tables\Columns\TextColumn::make('plate_number')
                    ->label('Plate Number'),
                Tables\Columns\TextColumn::make('vehicle.type') // Uses relationship to get vehicle type
                    ->label('Vehicle Type'),
                Tables\Columns\ColorColumn::make('vehicle_color')
                    ->label('Color'),
                Tables\Columns\TextColumn::make('applicant.full_name') // Uses relationship to get applicant name
                    ->label('Applicant'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
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
            'index' => Pages\ListParkingStickerApplications::route('/'),
            'create' => Pages\CreateParkingStickerApplication::route('/create'),
            'edit' => Pages\EditParkingStickerApplication::route('/{record}/edit'),
        ];
    }
}
