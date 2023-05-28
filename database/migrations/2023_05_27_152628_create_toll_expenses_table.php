<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTollExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('toll_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->nullable()->constrained('companies')->onUpdate('restrict')->onDelete('cascade');
            $table->string('transaction_number',25)->nullable();
            $table->string('entry_location')->nullable();
            $table->string('entry_sp')->nullable();
            $table->string('exit_location')->nullable();
            $table->string('exit_sp')->nullable();
            $table->string('reload_location')->nullable();
            $table->string('tag_number',10)->default('00000000');
            $table->string('remarks')->nullable();
            $table->string('day',2)->nullable();
            $table->string('month',2)->nullable();
            $table->string('year',4)->nullable();
            $table->date('posted_date')->nullable();
            $table->decimal('amount',12,2)->default(0);
            $table->decimal('balance',12,2)->default(0);
            $table->tinyInteger('class')->default(1);
            $table->tinyInteger('type')->default(1)->comment('1:toll usage 2:reload');
            $table->tinyInteger('status')->default(10);
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
        Schema::dropIfExists('toll_expenses');
    }
}
