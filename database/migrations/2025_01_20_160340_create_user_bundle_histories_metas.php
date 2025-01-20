<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserBundleHistoriesMetas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_bundle_histories_metas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_bundle_history_id')->nullable()->constrained('user_bundle_histories')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('froyo_id')->nullable()->constrained('froyos')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('syrup_id')->nullable()->constrained('syrups')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('topping_id')->nullable()->constrained('toppings')->onUpdate( 'restrict')->onDelete('cascade');
            $table->json('bundle_selections')->nullable();
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
        Schema::dropIfExists('user_bundle_histories_metas');
    }
}
