<?php

namespace TobiSchulz\RsyncBackupServer\Exceptions;

use Exception;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class BackupFailedException extends ProcessFailedException
{
    public function __construct(Process $process)
    {
        parent::__construct($process);
    }
}
