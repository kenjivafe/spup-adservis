<?php

namespace App\Filament\Exports;

use App\Models\JobOrder;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class JobOrderExporter extends Exporter
{
    protected static ?string $model = JobOrder::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('job_order_title'),
            ExportColumn::make('unit_name'),
            ExportColumn::make('date_requested'),
            ExportColumn::make('date_needed'),
            ExportColumn::make('particulars'),
            ExportColumn::make('materials'),
            ExportColumn::make('status'),
            ExportColumn::make('assigned_role'),
            ExportColumn::make('requested_by'),
            ExportColumn::make('canceled_by'),
            ExportColumn::make('recommended_by'),
            ExportColumn::make('rejected_by'),
            ExportColumn::make('approved_by'),
            ExportColumn::make('assigned_to'),
            ExportColumn::make('accomplished_by'),
            ExportColumn::make('checked_by'),
            ExportColumn::make('confirmed_by'),
            ExportColumn::make('date_begun'),
            ExportColumn::make('date_completed'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your job order export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
