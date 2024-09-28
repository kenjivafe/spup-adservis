<?php

namespace App\Filament\Resources\JobOrderResource\Pages;

use App\Filament\Exports\JobOrderExporter;
use App\Filament\Resources\JobOrderResource;
use App\Models\JobOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Request;

class ListJobOrders extends ListRecords
{
    protected static string $resource = JobOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->icon('heroicon-o-arrow-down-tray')
                ->label('Export CSV/XLSX')
                ->exporter(JobOrderExporter::class),

            Actions\CreateAction::make(),
            // Action::make('exportPdf')
            //     ->label('Export PDF')
            //     ->url(function () {
            //         // Merge current request parameters to maintain filters, sorting, and pagination
            //         $queryParams = array_merge(Request::query(), [
            //             'filters' => Request::get('filters', []),
            //             'sorts' => Request::get('sorts', []),
            //         ]);
            //         return route('job_orders.job-orders-pdf', $queryParams);
            //     })
            //     // ->openUrlInNewTab(true),
        ];
    }
}
