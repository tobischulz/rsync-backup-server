<?php

namespace TobiSchulz\RsyncBackupServer\Jobs\Rsync;

use TobiSchulz\RsyncBackupServer\Exceptions\BackupFailedException;
use TobiSchulz\RsyncBackupServer\Models\Backup;
use TobiSchulz\RsyncBackupServer\Models\SourceServer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class RsyncBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 86400;

    /**
     *
     */
    public $sourceServer;

    /**
     *
     */
    public $backup;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(SourceServer $sourceServer, Backup $backup)
    {
        $this->sourceServer = $sourceServer;
        $this->backup = $backup;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $sourcePath = sprintf(
            '%s@%s:%s',
            $this->sourceServer->ssh_user,
            $this->sourceServer->host,
            $this->sourceServer->source_path
        );

        $destinationPath = Storage::disk($this->sourceServer->destination_disk)
            ->path($this->backup->folder);

        $process = new Process([
            'rsync',
            '-a',
            '-e',
            "ssh -p {$this->sourceServer->ssh_port} -i {$this->sourceServer->ssh_private_key_path} -o \"StrictHostKeyChecking=no\"",
            $sourcePath,
            $destinationPath
        ]);

        $process->setTimeout($this->sourceServer->timeout);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new BackupFailedException($process);
        }

        $this->backup->markAsFinished();
    }
}
