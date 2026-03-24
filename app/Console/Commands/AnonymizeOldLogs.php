<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AuditLog;
use Carbon\Carbon;

class AnonymizeOldLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lgpd:anonymize {--months=6 : Number of months to keep full data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Anonymize personal data (IP/User Agent) from old audit logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $months = $this->option('months');
        $cutoff = Carbon::now()->subMonths($months);

        $count = AuditLog::where('created_at', '<', $cutoff)
            ->where(function ($query) {
                $query->whereNotNull('ip_address')
                      ->orWhereNotNull('user_agent');
            })
            ->update([
                'ip_address' => '0.0.0.0',
                'user_agent' => 'Anonymized (LGPD)',
            ]);

        $this->info("Successfully anonymized {$count} old audit logs.");
    }
}
