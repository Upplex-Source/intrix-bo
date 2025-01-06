<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->onUpdate( 'restrict')->onDelete('cascade');
            $table->string('checkout_id')->nullable();
            $table->string('checkout_url')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('layout_version')->default('v1');
            $table->string('redirect_url')->nullable();
            $table->string('notify_url')->nullable();
            $table->string('order_no')->default();
            $table->string('order_title')->nullable();
            $table->string('order_detail')->nullable();
            $table->decimal('amount',16,2);
            $table->string('currency')->default('MYR');
            $table->tinyInteger('transaction_type')->default(1)->comment('1: topup 2:insurance');
            $table->tinyInteger('status')->default(1)->comment('1: pending 10:success 20:failed');
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
        Schema::dropIfExists('order_transactions');
    }
}
