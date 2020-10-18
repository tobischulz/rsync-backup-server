<?php

namespace TobiSchulz\RsyncBackupServer\Console\Commands;

use TobiSchulz\RsyncBackupServer\Jobs\BackupJob;
use TobiSchulz\RsyncBackupServer\Models\SourceServer;
use Illuminate\Console\Command;

class BackupRunCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:run {sourceName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs a backup job for a given source name';

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
        $sourceName = $this->argument('sourceName');

        $sourceServer = SourceServer::where('name', $sourceName)
            ->firstOrFail();

        dispatch(new BackupJob($sourceServer));

        $this->line("Jobchain was dispatched.");

        return 0;
    }
}
