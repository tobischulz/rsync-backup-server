<?php

namespace TobiSchulz\RsyncBackupServer\Jobs;

use Throwable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use TobiSchulz\RsyncBackupServer\Models\Backup;
use TobiSchulz\RsyncBackupServer\Models\SourceServer;
use TobiSchulz\RsyncBackupServer\Jobs\Rsync\RsyncBackupJob;
use TobiSchulz\RsyncBackupServer\Jobs\Preflights\CreateBackupFolderJob;
use TobiSchulz\RsyncBackupServer\Jobs\Preflights\EnsureSourceIsReachableJob;
use TobiSchulz\RsyncBackupServer\Jobs\Preflights\EnsureDestinationIsReachableJob;

class BackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $sourceServer;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(SourceServer $sourceServer)
    {
        $this->sourceServer = $sourceServer;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $backup = Backup::create([
            'source_server_id' => $this->sourceServer->id,
            'status' => 'pending',
        ]);

        Bus::chain([
            function () use ($backup) {
                $backup->markAsRunning();
            },
            new EnsureSourceIsReachableJob($this->sourceServer, $backup),
            new EnsureDestinationIsReachableJob($this->sourceServer, $backup),
            new CreateBackupFolderJob($this->sourceServer, $backup),
            new RsyncBackupJob($this->sourceServer, $backup),
        ])
        ->catch(function (Throwable $e) use ($backup) {
            $backup->markAsFailed();
        })
        ->dispatch();
    }
}
