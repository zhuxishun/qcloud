<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQcloudSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qcloud_sessions;', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid',100)->default('');
            $table->string('skey',100)->unique();
            $table->string('openid',100)->unique();
            $table->string('session_key',100)->default('');
            $table->string('userinfo',2048)->default('');
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
        Schema::dropIfExists('qcloud_sessions;');
    }
}
