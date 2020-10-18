<?php

namespace TobiSchulz\RsyncBackupServer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Sets the generated foldername for the new backup.
     *
     * @return void
     */
    public function setBackupFolderName(string $folder) : void
    {
        $this->update([
            'folder' => $folder,
        ]);
    }

    /**
     *
     */
    public function markAsRunning()
    {
        $this->update([
            'status' => 'running',
        ]);
    }

    /**
     *
     */
    public function markAsFailed()
    {
        $this->update([
            'status' => 'failed',
            'finished_at' => now()
        ]);
    }

    /**
     *
     */
    public function markAsFinished()
    {
        $this->update([
            'status' => 'finished',
            'finished_at' => now()
        ]);
    }
}
