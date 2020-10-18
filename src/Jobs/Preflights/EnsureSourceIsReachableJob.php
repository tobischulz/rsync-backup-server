<?php

namespace TobiSchulz\RsyncBackupServer\Jobs\Preflights;

use TobiSchulz\RsyncBackupServer\Exceptions\SshUserDoesNotMatchException;
use TobiSchulz\RsyncBackupServer\Models\Backup;
use TobiSchulz\RsyncBackupServer\Models\SourceServer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Ssh\Ssh;
use Symfony\Component\Process\Exception\ProcessFailedException;

class EnsureSourceIsReachableJob implements ShouldQueue
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
     * Execute the job to ensure the source server is reachable
     * by executing the whoami unix command and check if the users
     * matches the source server configurated ssh_user.
     *
     * @return void
     */
    public function handle()
    {
        $process = Ssh::create($this->sourceServer->ssh_user, $this->sourceServer->host)
            ->usePort($this->sourceServer->ssh_port ??= 22)
            ->usePrivateKey($this->sourceServer->ssh_private_key_path)
            ->disableStrictHostKeyChecking()
            ->execute('whoami');

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        if (trim($process->getOutput()) !== $this->sourceServer->ssh_user) {
            $this->setIsSourceReachable(false);

            throw new SshUserDoesNotMatchException();
        }

        $this->setIsSourceReachable(true);
    }

    /**
     *
     */
    protected function setIsSourceReachable(bool $status)
    {
        $this->backup->update([
            'is_source_reachable' => $status,
        ]);
    }
}
