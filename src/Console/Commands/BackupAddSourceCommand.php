<?php

namespace TobiSchulz\RsyncBackupServer\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Throwable;
use TobiSchulz\RsyncBackupServer\Models\SourceServer;

class BackupAddSourceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:add';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add new Backup Source Server';

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
        $this->info('Add new backup source server..');

        $source = new SourceServer();

        $source->name = $this->ask('Please enter the Source Server Name :');

        $source->source = $this->ask('Please enter the Source Server IP :');

        $source->ssh_user = $this->ask('SSH User Name :');

        $source->ssh_private_key_path = $this->ask('SSH private key path :', '~/.ssh/id_rsa');

        $source->ssh_port = $this->ask('SSH port :', 22);

        $source->backup_hour = $this->ask('Backup Hour :', 5);

        $source->source_path = $this->ask('Source Path :', base_path('*'));

        $disks = array_keys(Config::get('filesystems.disks', []));

        if (count($disks)) {
            $source->destination_disk = $this->choice('Destination Disk :', $disks);
        } else {
            $source->destination_disk = $this->ask('Destination Disk :');
        }

        $source->type = $this->choice('Backup type :', ['rsync', 'backup']);

        try {
            $source->save();
        } catch (Throwable $th) {
            $this->error('Cannot able to add new source server. Please check the values');

            return 1;
        }

        return 0;
    }
}
