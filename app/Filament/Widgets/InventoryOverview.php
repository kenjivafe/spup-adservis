<?php

namespace App\Filament\Widgets;

use App\Models\Equipment;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class InventoryOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static bool $isDiscovered = false;

    protected function getStats(): array
    {
        $equipments = Equipment::query();

        // Apply filters to the query
        if (isset($this->filters['equipment_category_id'])) {
            $equipments->whereHas('equipmentType', function ($query) {
                $query->where('equipment_category_id', $this->filters['equipment_category_id']);
            });
        }

        if (isset($this->filters['equipment_type_id'])) {
            $equipments->where('equipment_type_id', $this->filters['equipment_type_id']);
        }

        if (isset($this->filters['unit_id'])) {
            $equipments->where('unit_id', $this->filters['unit_id']);
        }

        // Apply filters to the repairs query
        $filteredEquipments = $equipments->pluck('id'); // Get IDs of filtered equipment

        $brandRepairStats = DB::table('equipment')
            ->select(
                'equipment.equipment_brand_id',
                DB::raw('COUNT(*) as total_repairs'),
                DB::raw('SUM(joe.is_repaired) as successful_repairs'),
                DB::raw('MIN(equipment.date_acquired) as earliest_acquired'),
                DB::raw('MAX(equipment.date_disposed) as latest_disposed'),
                DB::raw('SUM(DATEDIFF(CURRENT_DATE, equipment.date_acquired)) as total_lifespan')
            )
            ->leftJoin('job_order_equipment as joe', 'equipment.id', '=', 'joe.equipment_id')
            ->whereIn('equipment.id', $filteredEquipments)
            ->groupBy('equipment.equipment_brand_id')
            ->get();

        // Calculate success rates and include brands with no repairs
        $brandRepairStats = $brandRepairStats->map(function ($brand) use ($filteredEquipments) {
            $brand->success_rate = $brand->total_repairs > 0
                ? ($brand->successful_repairs / $brand->total_repairs) * 100
                : 0;

            // Calculate lifespan (difference between acquisition and disposal date)
            $brand->lifespan = $brand->earliest_acquired
                ? Carbon::parse($brand->earliest_acquired)->diffInYears(Carbon::now())
                : 0;

            // Calculate repair intervals (average time between repairs)
            $repairIntervals = DB::table('job_order_equipment as joe')
                ->join('equipment', 'joe.equipment_id', '=', 'equipment.id')
                ->where('equipment.equipment_brand_id', $brand->equipment_brand_id)
                ->whereIn('equipment.id', $filteredEquipments)
                ->where('joe.is_repaired', 1)
                ->orderBy('joe.date_repaired')
                ->get();

            if ($repairIntervals->count() > 1) {
                // Calculate intervals between successive repairs
                $intervals = [];
                $previousRepairDate = null;

                foreach ($repairIntervals as $repair) {
                    if ($previousRepairDate) {
                        $intervals[] = Carbon::parse($repair->date_repaired)->diffInDays(Carbon::parse($previousRepairDate));
                    }
                    $previousRepairDate = $repair->date_repaired;
                }

                $brand->avg_repair_interval = $intervals ? array_sum($intervals) / count($intervals) : 0;
            } else {
                $brand->avg_repair_interval = 0;
            }

            return $brand;
        });

        // Find the best brand based on success rate and consider lifespan
        $bestBrand = $brandRepairStats->sortByDesc(function ($brand) {
            // If there are no repairs, prioritize the longest lifespan
            if ($brand->total_repairs == 0) {
                return $brand->lifespan; // No repairs, use the lifespan
            }
            // If both repaired and non-repaired brands exist, prioritize based on total uptime lifespan
            return $brand->success_rate + ($brand->total_lifespan * 0.5); // Combining success rate and total lifespan
        })->first();

        // dd($bestBrand); // See if the bestBrand is actually selected

        $bestBrandId = optional($bestBrand)->equipment_brand_id;

        // Retrieve the best brand's name
        $bestBrandName = $bestBrandId
            ? Equipment::where('equipment_brand_id', $bestBrandId)
                ->with('equipmentBrand')
                ->first()
                ->equipmentBrand->name
            : 'N/A';

            // dd($bestBrandName); // Check if the name is retrieved properly

        // Calculate metrics for the best-performing brand
        $averageRepairs = $bestBrand ? $bestBrand->avg_repair_interval : 'N/A';
        $overallPerformance = 0;

        if ($bestBrandId) {
            $bestBrandEquipments = Equipment::where('equipment_brand_id', $bestBrandId)
                ->whereIn('id', $filteredEquipments);

            // Overall Performance
            $totalEquipments = $bestBrandEquipments->count();
            $disposedEquipments = $bestBrandEquipments->where('status', 'Disposed')->count();

            $overallPerformance = $totalEquipments > 0
                ? (($totalEquipments - $disposedEquipments) / $totalEquipments) * 100
                : 0;
        }

        return [
            Stat::make('Best Performing Brand', $bestBrandName),
            Stat::make('Average Repair Interval (Days)', $averageRepairs),
            Stat::make('Overall Performance', number_format($overallPerformance, 2) . '%'),
        ];
    }
}
