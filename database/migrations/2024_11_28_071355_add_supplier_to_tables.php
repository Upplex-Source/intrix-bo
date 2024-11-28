<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSupplierToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onUpdate('restrict')->onDelete('cascade');
            $table->decimal('amount' , 20, 2 )->nullable();
            $table->decimal('original_amount' , 20, 2)->nullable();
            $table->decimal('final_amount' , 20, 2)->nullable();
            $table->decimal('paid_amount' , 20, 2)->nullable();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onUpdate('restrict')->onDelete('cascade');
            $table->decimal('amount' , 20, 2 )->nullable();
            $table->decimal('original_amount' , 20, 2)->nullable();
            $table->decimal('final_amount' , 20, 2)->nullable();
            $table->decimal('paid_amount' , 20, 2)->nullable();
            $table->timestamp('confirmed_date')->nullable();
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onUpdate('restrict')->onDelete('cascade');
            $table->decimal('amount' , 20, 2 )->nullable();
            $table->decimal('original_amount' , 20, 2)->nullable();
            $table->decimal('final_amount' , 20, 2)->nullable();
            $table->decimal('paid_amount' , 20, 2)->nullable();
            $table->timestamp('confirmed_date')->nullable();
        });

        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onUpdate('restrict')->onDelete('cascade');
            $table->decimal('amount' , 20, 2 )->nullable();
            $table->decimal('original_amount' , 20, 2)->nullable();
            $table->decimal('final_amount' , 20, 2)->nullable();
            $table->decimal('paid_amount' , 20, 2)->nullable();
            $table->timestamp('confirmed_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
            $table->dropColumn('amount');
            $table->dropColumn('original_amount');
            $table->dropColumn('final_amount');
            $table->dropColumn('paid_amount');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
            $table->dropColumn('amount');
            $table->dropColumn('original_amount');
            $table->dropColumn('final_amount');
            $table->dropColumn('paid_amount');
            $table->dropColumn('confirmed_date');
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
            $table->dropColumn('amount');
            $table->dropColumn('original_amount');
            $table->dropColumn('final_amount');
            $table->dropColumn('paid_amount');
            $table->dropColumn('confirmed_date');
        });

        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
            $table->dropColumn('amount');
            $table->dropColumn('original_amount');
            $table->dropColumn('final_amount');
            $table->dropColumn('paid_amount');
            $table->dropColumn('confirmed_date');
        });
    }
}
