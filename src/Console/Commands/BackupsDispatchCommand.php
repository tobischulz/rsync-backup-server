<?php

namespace TobiSchulz\RsyncBackupServer\Console\Commands;

use TobiSchulz\RsyncBackupServer\Jobs\BackupJob;
use TobiSchulz\RsyncBackupServer\Models\SourceServer;
use Illuminate\Console\Command;

class BackupsDispatchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backups:dispatch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatches new backup jobs';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $currentScheduleHour = now()->hour;

        $this->info("trying to schedule backups for source servers with hour {$currentScheduleHour}");

        // get source servers that needs to get a backup
        $sourceServers = SourceServer::where('backup_hour', $currentScheduleHour)
            ->whereNull('is_paused')
            ->get();

        $this->info("found {$sourceServers->count()} servers to schedule");

        // dispatch backup jobs for each source
        $sourceServers->each(function ($server) {
            dispatch(new BackupJob($server));
        });

        // done
        return 0;
    }
}
