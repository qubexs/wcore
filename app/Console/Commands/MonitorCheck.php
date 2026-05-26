<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\WebsiteMonitor\Controllers\WebsiteMonitorController;

class MonitorCheck extends Command
{
    protected $signature = 'monitor:check';
    protected $description = 'Run automatic health checks for all Website Monitor targets';

    public function handle(): int
    {
        $this->info('Starting Website Monitor auto-check...');

        try {
            $controller = app(WebsiteMonitorController::class);
            $results = $controller->cronCheck();

            $checked = count($results);
            $down = count(array_filter($results, fn($r) => $r['status'] === 'down'));

            $this->info("Checked {$checked} targets. {$down} down.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
