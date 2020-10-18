# Rsync Backup Server

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

This package let you handle all of your off-site backups by your own laravel project on your own hosted server. Add your source to the database and dispatch a new backup job through the artisan command. Laravel queue system will handle all steps to backup your databases or files.

## Installation

Pull this package in using composer:

```bash
composer require tobischulz/rsync-backup-server
```

Publish the package files:

```bash
php artisan vendor:publish --provider=TobiSchulz\RsyncBackupServer\RsyncBackupServerProvider
```

Add a backup destination storage disk `config/filesystems.php`, where to store all the backup files. Driver **has** to be **`local`**.

```php
'disks' => [

    //...

    'my_backup_disk' => [
        'driver' => 'local',  // must be local
        'root' => '/Volumes/Share/rsync-backups',  // your own path
    ],

    //...
]
```

Add backup dispatch schedule command to `app\Console\Kernel.php`:

```php
/**
 * Define the application's command schedule.
 *
 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
 * @return void
 */
protected function schedule(Schedule $schedule)
{
    $schedule->command('backups:dispatch')->hourly();
}
```

Set your queue system in your .env file:

```env
QUEUE_CONNECTION=database  // redis for horizon
```

This package should work with every laravel queue system. Currently i tested it only with database. Make sure your migrations has been run for database queue driver.

## Usage

Creating your first source:

```php
$source = SourceServer::create([
    'name' => 'mySource',   // source name
    'host' => '123.123.123.123',    // ip of the source server
    'ssh_user' => 'forge',  // ssh user on the source server
    'ssh_private_key_path' => '~/.ssh/id_rsa',  // path to the ssh private key from backup server
    'ssh_port' => 22,   // ssh port, default 22
    'backup_hour' => 5, // this sets the schedule for 5am UTC
    'source_path' => '~/backups/*', // folder on source to backup
    'destination_disk' => 'my_backup_disk',  // disk we created in /config/filesystems.php
    'type' => 'rsync' // set 'rsync' or 'backup'
]);

```

### backup_hour

Set your hour when the backup should be scheduled every day. The schedule command in `app\Console\Kernel.php` will read all source servers from database that backup_hour is equal to the current time and dispatch the new backup job.

### ssh_private_key_path

You will need to add your ssh public key from your backup server to every source server you want to connect to and make backups from. In this configuration you will need to set the private key of the backup server, that will be used to connect to your source server. With this database path of your private key you will be able to use different keys for each source server.

### type

There are two types of backups in this package. The default `backup` will create a new folder every time a backup runs. This will increase the disk usage on every backup but this should be handeld by the `cleanup` command later.

The second type is `rsync`. This type will sync the source folder with the destination folder.


### Manually dispatch Backup

You can manually dispatch a new backup job by using the artisan command `php artisan backup:run mySource` where *mySource* is the source name in your database.

### Schedule Backups

Backup jobs will be scheduled based on your `backup_hour` configuration on your source servers. This is handeld by the added artisan schedule command `php artisan backups:dispatch` in `app\Console\Kernel.php`.

## Security

If you discover any security-related issues, please email tobias@byte.software instead of using the issue tracker.

## Credits

This package is heavly inspired by the upcoming package [spatie/laravel-backup-server](https://spatie.be/docs/laravel-backup-server/v1/introduction) made by [Spatie](https://github.com/spatie/).

- [Tobias Schulz](https://github.com/tobischulz)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
