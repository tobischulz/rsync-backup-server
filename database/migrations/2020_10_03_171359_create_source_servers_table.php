<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSourceServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('source_servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('host');
            $table->string('ssh_user');
            $table->string('ssh_private_key_path')->default('~/.ssh/id_rsa');
            $table->integer('ssh_port')->default(22);
            $table->integer('backup_hour')->default(1);
            $table->string('type');
            $table->integer('timeout')->default(86400);
            $table->string('source_path')->default('~/backups');
            $table->string('destination_disk')->default('local');
            $table->json('pre_backup_commands')->nullable();
            $table->json('post_backup_commands')->nullable();
            $table->json('includes')->nullable();
            $table->json('excludes')->nullable();
            $table->boolean('is_paused')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('source_servers');
    }
}
