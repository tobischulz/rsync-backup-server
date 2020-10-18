<?php

namespace TobiSchulz\RsyncBackupServer\Jobs\Preflights;

use TobiSchulz\RsyncBackupServer\Exceptions\DestinationDoesNotExistException;
use TobiSchulz\RsyncBackupServer\Models\Backup;
use TobiSchulz\RsyncBackupServer\Models\SourceServer;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class EnsureDestinationIsReachableJob implements ShouldQueue
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
        try {
            Storage::disk($this->sourceServer->destination_disk);
        } catch (\Exception $e) {
            throw new DestinationDoesNotExistException();

            $this->setIsDestinationReachable(false);
        }

        $this->setIsDestinationReachable(true);
    }

    /**
     *
     */
    protected function setIsDestinationReachable(bool $status)
    {
        $this->backup->update([
            'is_destination_reachable' => $status,
        ]);
    }
}
