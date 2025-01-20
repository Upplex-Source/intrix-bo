<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserBundleTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_bundle_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('product_bundle_id')->nullable()->constrained('product_bundles')->onUpdate( 'restrict')->onDelete('cascade');
            $table->string('reference')->nullable();
            $table->tinyInteger('payment_attempt')->default(1);
            $table->decimal('price', 8, 2)->nullable();
            $table->longText('payment_url')->nullable();
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
        Schema::dropIfExists('user_bundle_transactions');
    }
}
