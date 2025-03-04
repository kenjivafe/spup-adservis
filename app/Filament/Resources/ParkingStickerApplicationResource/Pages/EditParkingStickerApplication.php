<?php

namespace App\Filament\Resources\ParkingStickerApplicationResource\Pages;

use App\Filament\Resources\ParkingStickerApplicationResource;
use App\Models\ParkingStickerApplication;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Spatie\Browsershot\Browsershot;

class EditParkingStickerApplication extends EditRecord
{
    protected static string $resource = ParkingStickerApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Generate PDF')
                ->button()
                ->color('gray')
                ->label('PDF')
                ->icon('heroicon-s-document-arrow-down')
                ->action(function (ParkingStickerApplication $record) {
                    $stickerCost = '₱______';

                    if ($record && $record->vehicle) {
                        $stickerCost =  number_format($record->vehicle->sticker_cost, 2) . 'php';
                    }
                    // Create HTML content using a template engine like Blade
                    $html = view('pdfs.parking-sticker-application', ['application' => $record, 'title' => 'ADM-001', 'stickerCost' => $stickerCost])->render();
                    // Set up DOMPDF options
                    $options = new Options();
                    $options->set('isHtml5ParserEnabled', true);
                    $options->set('isRemoteEnabled', true);
                    $options->set('isPhpEnabled', true); // Enable PHP functions in Blade view (like Carbon or custom functions)

                    $dompdf = new Dompdf($options);

                    // Load the HTML content
                    $dompdf->loadHtml($html);

                    // (Optional) Set paper size and orientation (A4, landscape in this case)
                    $dompdf->setPaper('A4', 'landscape'); // If you want portrait orientation, you can set it to 'portrait'

                    // Render the PDF (first pass)
                    $dompdf->render();

                    // Output the PDF to a file
                    $output = $dompdf->output();
                    $filePath = public_path('parking-sticker-application-' . $record->id . '.pdf');
                    file_put_contents($filePath, $output);

                    // Return the PDF as a download response
                    return response()->download($filePath)->deleteFileAfterSend(true);
                }),
            Actions\ActionGroup::make([
                Actions\Action::make('Approve')
                    ->color('primary')
                    ->action(function (ParkingStickerApplication $application): void {
                        $expirationDate = today()->addYear(1);

                        DB::transaction(function () use ($application, $expirationDate) {
                            $application->update([
                                'status' => 'Active',
                                'approved_by' => auth()->id(),  // Assuming 'approved_by' is the field name in your database
                                'approved_at' => Carbon::now(),
                                'expiration_date' => $expirationDate,
                            ]);

                            $application->save();
                            $this->redirect($this->getResource()::getUrl('edit', ['record' => $application->getKey()]));
                        });
                    })
                    ->visible(fn ($record) =>
                        optional($record)->status === 'Pending' &&
                        empty($record->approved_by) &&
                        auth()->user()->can('Manage Job Orders')
                    )
                    ->icon('heroicon-s-check'),

                Actions\Action::make('Reject')
                    ->color('danger')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->placeholder('Please provide a reason for rejection.'),
                    ])
                    ->requiresConfirmation()
                    ->action(function (ParkingStickerApplication $application, array $data,): void {
                        $rejectionReason = $data['rejection_reason'];

                        DB::transaction(function () use ($application, $rejectionReason) {
                            $application->update([
                                'status' => 'Rejected',
                                'rejected_by' => auth()->id(),
                                'rejected_at' => Carbon::now(),
                                'rejection_reason' => $rejectionReason,
                            ]);
                        });
                        $this->redirect($this->getResource()::getUrl('edit', ['record' => $application->getKey()]));
                    })
                    ->visible(fn ($record) =>
                        optional($record)->status === 'Pending' &&
                        empty($record->approved_by) &&
                        empty($record->rejected_by) &&
                        auth()->user()->can('Manage Parking Application')
                    )
                    ->icon('heroicon-o-no-symbol'),
                ])
                ->label('Approval')->icon('heroicon-o-chevron-down')->button()->color('yellow'),
            Actions\Action::make('Revoke')
                ->label('Revoke Sticker')
                ->color('danger')
                ->form([
                    Textarea::make('revocation_reason')
                        ->label('Revocation Reason')
                        ->required()
                        ->placeholder('Please provide a reason for revocation.'),
                ])
                ->requiresConfirmation()
                ->action(function (ParkingStickerApplication $application, array $data,): void {
                    $revocationReason = $data['revocation_reason'];

                    DB::transaction(function () use ($application, $revocationReason) {
                        $application->update([
                            'status' => 'Revoked',
                            'revoked_by' => auth()->id(),
                            'revoked_at' => Carbon::now(),
                            'revocation_reason' => $revocationReason,
                        ]);
                    });
                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $application->getKey()]));
                })
                ->visible(fn ($record) =>
                    optional($record)->status === 'Active' &&
                    empty($record->revoked_by) &&
                    auth()->user()->can('Manage Parking Applications')
                )
                ->icon('heroicon-o-no-symbol'),
            // Actions\DeleteAction::make(),
        ];
    }


    protected function getFormActions(): array
    {

        return array_merge(parent::getFormActions());
    }
}
