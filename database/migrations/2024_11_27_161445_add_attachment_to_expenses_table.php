<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAttachmentToExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('expenses', function (Blueprint $table) {
            //
            $table->string('attachment', 100)->nullable();
            $table->string('reference')->nullable();
            $table->string('title')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('expenses_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('expenses', function (Blueprint $table) {
            //
            $table->dropColumn('attachment');
            $table->dropColumn('reference');
            $table->dropColumn('title');
            $table->dropColumn('remarks');
            $table->dropColumn('expenses_date');
        });
    }
}
