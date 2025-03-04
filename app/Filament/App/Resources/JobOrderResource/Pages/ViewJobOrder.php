<?php

namespace App\Filament\App\Resources\JobOrderResource\Pages;

use App\Filament\App\Resources\JobOrderResource;
use App\Models\Equipment;
use App\Models\JobOrder;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ViewJobOrder extends ViewRecord
{
    protected static string $resource = JobOrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ActionGroup::make([
                Actions\Action::make('Recommend')
                    ->after(function () {
                        $jobOrder = $this->record;

                        foreach ($jobOrder->jobOrderEquipments as $jobOrderEquipment) {
                            $equipment = $jobOrderEquipment->equipment;
                            if ($equipment) {
                                $equipment->markAsInactive();
                                $equipment->save();
                            }
                        }
                    })
                    ->modalWidth(MaxWidth::Medium)
                    ->form([
                        Fieldset::make('Assign a User before recommending to VP Admin')
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
                                    ->native(false)
                                    ->validationMessages([
                                        'required' => 'Assign a user to recommend a job order.',
                                    ])
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
                                    })
                                    ->required(),
                            ])
                    ])
                    ->color('blue')
                    ->action(function (array $data, JobOrder $jobOrder): void {
                        // Access form data from $data
                        $assignedRole = $data['assigned_role'];
                        $assignedTo = $data['assigned_to'];

                        // Perform validation if necessary
                        Validator::make($data, [
                            'assigned_role' => 'required',
                            'assigned_to' => 'required',
                        ])->validate();


                        DB::transaction(function () use ($jobOrder, $assignedTo, $assignedRole) {
                            $jobOrder->update([
                                'recommended_by' => auth()->id(),
                                'recommended_at' => Carbon::now(),
                                'assigned_role' => $assignedRole, // Assuming your model has this field
                                'assigned_to' => $assignedTo,
                            ]);

                            $jobOrder->save();
                            $this->redirect($this->getResource()::getUrl('view', ['record' => $jobOrder->getKey()]));
                        });
                    })
                    ->visible(fn ($record) =>
                        optional($record)->status === 'Pending' &&
                        empty($record->assigned_to) &&
                        empty($record->recommended_by) &&
                        auth()->user()->can('Recommend Job Orders')
                    )
                    ->icon('heroicon-s-hand-thumb-up'),

                Actions\Action::make('Reject')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->placeholder('Please provide a reason for rejection.'),
                    ])
                    ->requiresConfirmation()
                    ->color('danger')
                    ->action(function (JobOrder $jobOrder, array $data,): void {
                        $rejectionReason = $data['rejection_reason'];

                        DB::transaction(function () use ($jobOrder, $rejectionReason) {
                            $jobOrder->update([
                                'status' => 'Rejected',
                                'rejected_by' => auth()->id(),
                                'rejected_at' => Carbon::now(),
                                'rejection_reason' => $rejectionReason,
                            ]);
                            $this->redirect($this->getResource()::getUrl('view', ['record' => $jobOrder->getKey()]));
                        });
                    })
                    ->visible(fn ($record) =>
                        optional($record)->status === 'Pending' &&
                        empty($record->assigned_to) &&
                        empty($record->recommended_by) &&
                        is_null($record->rejected_by) &&
                        auth()->user()->can('Recommend Job Orders')
                    )
                    ->icon('heroicon-s-hand-thumb-down'),
                ])
                ->label('Recommendation')->icon('heroicon-m-chevron-down')->button()->color('blue'),

            Actions\Action::make('Accomplish')
                ->fillForm(fn (JobOrder $record): array => [
                    'jobOrderEquipments' => $record->equipment_id,
                ])
                ->color('purple')
                ->button()
                ->form([
                        Repeater::make('jobOrderEquipments')
                            ->visible(fn ($record) => $record->jobOrderEquipments->count() > 0)
                            ->relationship()
                            ->default(function ($record) {
                                return $record->jobOrderEquipments->map(function ($jobOrderEquipment) {
                                    return [
                                        'equipment_id' => $jobOrderEquipment->equipment_id,
                                    ];
                                })->toArray();
                            })
                            ->label('Equipments')
                            ->schema([
                                Select::make('equipment_id')
                                    ->options(
                                        Equipment::where('status', '!=', 'Disposed') // Filter available equipment
                                            ->pluck('code', 'id'))
                                    ->label('')
                                    ->disabled()
                                    ->required()
                                    ->reactive(),
                                Toggle::make('is_repaired')
                                    ->label('Is equipment repaired?')
                                    ->dehydrated(true)
                            ])
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->grid(2)
                            ->itemLabel(function (array $state): ?string {
                                $equipment = Equipment::find($state['equipment_id']);
                                if ($equipment) {
                                    return "{$equipment->equipmentBrand->name} {$equipment->equipmentType->name} of {$equipment->unit->name}";
                                }
                                return null;
                            }),
                ])
                ->action(function (JobOrder $jobOrder): void {
                    DB::transaction(function () use ($jobOrder) {
                        $jobOrder->update([
                            'accomplished_by' => auth()->id(),  // Assuming 'approved_by' is the field name in your database
                            'accomplished_at' => Carbon::now(),
                        ]);

                        foreach ($jobOrder->jobOrderEquipments as $jobOrderEquipment) {
                            $jobOrderEquipment->update([
                                'is_repaired' => $jobOrderEquipment->is_repaired,
                                'date_repaired' => $jobOrderEquipment->is_repaired ? now() : null,
                            ]);
                        }

                        $this->redirect($this->getResource()::getUrl('view', ['record' => $jobOrder->getKey()]));
                    });
                })
                ->visible(fn ($record) =>
                    $record->status === 'Assigned' &&
                    !empty($record->approved_by) &&
                    empty($record->accomplished_by) &&
                    auth()->user()->id == $record->assigned_to
                )
                ->icon('heroicon-s-clipboard-document-check'),

            Actions\Action::make('Verify')
                ->color('blue')
                ->button()
                ->action(function (JobOrder $jobOrder): void {
                    DB::transaction(function () use ($jobOrder) {
                        $jobOrder->update([
                            'status' => 'Completed',
                            'date_completed' => now('Asia/Manila')->format('Y-m-d H:i'),
                            'checked_by' => auth()->id(),  // Assuming 'approved_by' is the field name in your database
                            'checked_at' => Carbon::now(),
                        ]);

                        foreach ($jobOrder->jobOrderEquipments as $jobOrderEquipment) {
                            if ($jobOrderEquipment->is_repaired) {
                                $equipment = $jobOrderEquipment->equipment;
                                if ($equipment) {
                                    $equipment->markAsActive();
                                    $equipment->save();
                                }
                            }
                        }

                        $jobOrder->save();
                        $this->redirect($this->getResource()::getUrl('view', ['record' => $jobOrder->getKey()]));
                    });
                })
                ->visible(fn ($record) =>
                    $record->status === 'Assigned' &&
                    empty($record->checked_by) &&
                    !empty($record->accomplished_by) &&
                    auth()->user()->id == $record->recommended_by
                )
                ->icon('heroicon-s-check-badge'),

            Actions\Action::make('Confirm')
                ->color('primary')
                ->button()
                ->action(function (JobOrder $jobOrder): void {
                    DB::transaction(function () use ($jobOrder) {
                        $jobOrder->update([
                            'confirmed_by' => auth()->id(),  // Assuming 'approved_by' is the field name in your database
                            'confirmed_at' => Carbon::now(),
                        ]);
                        $this->redirect($this->getResource()::getUrl('view', ['record' => $jobOrder->getKey()]));
                    });
                })
                ->visible(fn ($record) =>
                    $record->status === 'Completed' &&
                    empty($record->confirmed_by) &&
                    !empty($record->checked_by) &&
                    auth()->user()->id == $record->requested_by
                )
                ->icon('heroicon-s-check-badge'),

            Actions\Action::make('Cancel')
                ->label('Cancel Job Order')
                ->color('danger')
                ->form([
                    Textarea::make('cancelation_reason')
                        ->label('Cancelation Reason')
                        ->required()
                        ->placeholder('Please provide a reason for cancelation.'),
                ])
                ->requiresConfirmation()
                ->action(function (JobOrder $jobOrder, array $data,): void {
                    $cancelationReason = $data['cancelation_reason'];

                    DB::transaction(function () use ($jobOrder, $cancelationReason) {
                        $jobOrder->update([
                            'status' => 'Canceled',
                            'canceled_by' => auth()->id(),
                            'canceled_at' => Carbon::now(),
                            'cancelation_reason' => $cancelationReason
                        ]);
                        $this->redirect($this->getResource()::getUrl('view', ['record' => $jobOrder->getKey()]));
                    });
                })
                ->visible(fn ($record) =>
                    $record->status != 'Completed' &&
                    $record->status != 'Rejected' &&
                    empty($record->canceled_by) &&
                    auth()->user()->id == $record->requested_by
                )
                ->icon('heroicon-o-no-symbol'),
        ];
    }
}
