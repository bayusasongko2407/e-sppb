<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AppSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneNotificationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:prune';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune old system notifications based on retention days setting';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $retentionDays = (int) AppSetting::get('notify_system_retention_days', 30);
        if ($retentionDays <= 0) {
            $retentionDays = 30;
        }

        $cutoffDate = now()->subDays($retentionDays);

        $deleted = DB::table('notifications')
            ->where('created_at', '<', $cutoffDate)
            ->delete();

        $this->info("Berhasil membersihkan {$deleted} notifikasi sistem yang lebih tua dari {$retentionDays} hari.");

        return Command::SUCCESS;
    }
}
