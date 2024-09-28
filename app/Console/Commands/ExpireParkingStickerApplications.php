<?php

namespace App\Console\Commands;

use App\Models\ParkingStickerApplication;
use Illuminate\Console\Command;

class ExpireParkingStickerApplications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:expire-parking-sticker-applications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Application Expires';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredApplications = ParkingStickerApplication::where('status', 'Active')
            ->where('expiration_date', '<=', today())
            ->get();

        foreach ($expiredApplications as $application) {
            $application->update(['status' => 'Expired']);
        }

        $this->info('Expired applications updated successfully.');
    }
}
