<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expenses_account_id')->nullable()->constrained('expenses_accounts')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('expenses_category_id')->nullable()->constrained('expenses_categories')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId( 'causer_id' )->nullable()->constrained( 'administrators' )->onUpdate( 'restrict' )->onDelete( 'cascade' );
            $table->decimal('amount' , 20, 2 )->nullable();
            $table->decimal('original_amount' , 20, 2)->nullable();
            $table->decimal('final_amount' , 20, 2)->nullable();
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
        Schema::dropIfExists('expenses');
    }
}
