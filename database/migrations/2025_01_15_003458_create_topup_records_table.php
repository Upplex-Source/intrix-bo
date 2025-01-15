<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTopupRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('topup_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('wallet_transaction_id')->nullable()->constrained('wallet_transactions')->onUpdate( 'restrict')->onDelete('cascade');
            $table->string('reference')->nullable();
            $table->longText('payment_url')->nullable();
            $table->tinyInteger('payment_attempt')->default(1);
            $table->decimal('amount', 8, 2)->nullable();
            $table->tinyInteger('status')->default(10);
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
        Schema::dropIfExists('topup_records');
    }
}
