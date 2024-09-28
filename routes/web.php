<?php

use App\Models\JobOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Http\Controllers\Auth\EmailVerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/email/verify/{id}/{hash}', EmailVerificationController::class)
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');

Route::get('/filament/job-orders/{jobOrder}/pdf', function (App\Models\JobOrder $jobOrder) {
    $pdf = Pdf::loadView('filament.job_orders.job-order-pdf', ['jobOrder' => $jobOrder]);
    return $pdf->download('job_order_' . $jobOrder->id . '.pdf');
})->name('job_orders.job-order-pdf')->middleware(['web', 'auth']); // Ensure the route is protected

Route::get('/filament/job-orders/pdf', function (Request $request) {
    $query = JobOrder::query();

    // Apply filters
    if ($request->has('filters')) {
        $filters = $request->get('filters');
        if (!empty($filters['status'])) {
            $query->whereIn('status', $filters['status']);
        }
        if (!empty($filters['unit_name'])) {
            $query->where('unit_name', 'like', '%' . $filters['unit_name'] . '%');
        }
        if (!empty($filters['created_at'])) {
            $dates = explode(' to ', $filters['created_at']);
            if (count($dates) === 2) {
                $query->whereBetween('created_at', [$dates[0], $dates[1]]);
            }
        }
    }

    // Apply sorting
    if ($request->has('sorts')) {
        foreach ($request->get('sorts') as $sort => $direction) {
            $query->orderBy($sort, $direction);
        }
    }

    $jobOrders = $query->get();

    $pdf = Pdf::loadView('filament.job_orders.job-orders-pdf', ['jobOrders' => $jobOrders]);
    return $pdf->download('job_orders.pdf');
})->name('job_orders.job-orders-pdf')->middleware(['auth']);

