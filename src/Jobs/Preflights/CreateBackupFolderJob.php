<?php

namespace TobiSchulz\RsyncBackupServer\Jobs\Preflights;

use TobiSchulz\RsyncBackupServer\Exceptions\CantCreateBackupFolderException;
use TobiSchulz\RsyncBackupServer\Models\Backup;
use TobiSchulz\RsyncBackupServer\Models\SourceServer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CreateBackupFolderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $sourceServer;

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
        if ($this->sourceServer->type === 'rsync') {
            $folder = "{$this->sourceServer->id}";
        } else {
            $folder = "{$this->sourceServer->id}/" . now()->format(config('rsync-backups.date_format'));
        }

        if (!Storage::disk($this->sourceServer->destination_disk)->makeDirectory($folder)) {
            throw new CantCreateBackupFolderException();
        }

        $this->backup->setBackupFolderName($folder);
    }
}
