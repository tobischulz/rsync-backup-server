<?php

namespace TobiSchulz\RsyncBackupServer;

use Illuminate\Support\ServiceProvider;
use TobiSchulz\RsyncBackupServer\Console\Commands\BackupAddSourceCommand;
use TobiSchulz\RsyncBackupServer\Console\Commands\BackupRunCommand;
use TobiSchulz\RsyncBackupServer\Console\Commands\BackupsDispatchCommand;

class RsyncBackupServerProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/rsync-backups.php' => config_path('rsync-backups.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/rsync-backups.php',
            'rsync-backups'
        );

        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations')
        ], 'migrations');

        $this->app->bind('command.backup:run', BackupRunCommand::class);
        $this->app->bind('command.backup:add', BackupAddSourceCommand::class);
        $this->app->bind('command.backups:dispatch', BackupsDispatchCommand::class);

        $this->commands([
            'command.backup:run',
            'command.backup:add',
            'command.backups:dispatch',
        ]);
    }
}
