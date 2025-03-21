<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserNotificationUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_notification_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId( 'user_notification_id' )->constrained( 'user_notifications' )->onUpdate( 'restrict' )->onDelete( 'cascade' );
            $table->foreignId( 'user_id' )->constrained( 'users' )->onUpdate( 'restrict' )->onDelete( 'cascade' );
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
        Schema::dropIfExists('user_notification_users');
    }
}
