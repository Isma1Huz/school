<?php

declare(strict_types=1);

namespace App\Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Horizon\Contracts\MetricsRepository;
use Laravel\Horizon\Contracts\JobRepository;

final class HorizonQueueMonitor extends Command
{
    protected $signature = 'horizon:queue-status';
    protected $description = 'List all Horizon queues with pending count, processed today, and failed today.';

    private const QUEUES = [
        'default',
        'events',
        'notifications',
        'sms',
        'reports',
        'imports',
        'analytics',
    ];

    public function handle(MetricsRepository $metrics, JobRepository $jobs): int
    {
        $rows = [];

        foreach (self::QUEUES as $queue) {
            try {
                $pending   = $this->getPendingCount($queue);
                $processed = $this->getProcessedToday($queue, $metrics);
                $failed    = $this->getFailedToday($queue);

                $rows[] = [
                    $queue,
                    $pending,
                    $processed,
                    $failed,
                ];
            } catch (\Throwable $e) {
                $rows[] = [$queue, 'N/A', 'N/A', 'N/A'];
            }
        }

        $this->table(
            ['Queue', 'Pending', 'Processed Today', 'Failed Today'],
            $rows
        );

        return self::SUCCESS;
    }

    private function getPendingCount(string $queue): int
    {
        try {
            return (int) \Illuminate\Support\Facades\Redis::connection('default')
                ->llen("queues:{$queue}");
        } catch (\Throwable) {
            return 0;
        }
    }

    private function getProcessedToday(string $queue, MetricsRepository $metrics): int
    {
        try {
            return (int) ($metrics->throughputForQueue($queue) ?? 0);
        } catch (\Throwable) {
            return 0;
        }
    }

    private function getFailedToday(string $queue): int
    {
        try {
            return (int) \Illuminate\Support\Facades\DB::table('failed_jobs')
                ->where('queue', $queue)
                ->whereDate('failed_at', today())
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }
}
