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
            $table->foreignId('fuel_expense_id')->nullable()->constrained('fuel_expenses')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('toll_expense_id')->nullable()->constrained('toll_expenses')->onUpdate('restrict')->onDelete('cascade');
            $table->decimal('amount',12,2)->default(0);
            $table->tinyInteger('type')->default(1);
            $table->timestamp('transaction_time')->nullable();
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
