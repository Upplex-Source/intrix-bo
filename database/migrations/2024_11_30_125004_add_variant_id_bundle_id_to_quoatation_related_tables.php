<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVariantIdBundleIdToQuoatationRelatedTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quotation_metas', function (Blueprint $table) {
            $table->foreignId('bundle_id')->nullable()->constrained('bundles')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('tax_method_id')->nullable()->constrained('tax_methods')->onUpdate('restrict')->onDelete('cascade');
        });

        Schema::table('sales_orders_metas', function (Blueprint $table) {
            $table->foreignId('bundle_id')->nullable()->constrained('bundles')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('tax_method_id')->nullable()->constrained('tax_methods')->onUpdate('restrict')->onDelete('cascade');
        });

        Schema::table('invoices_metas', function (Blueprint $table) {
            $table->foreignId('bundle_id')->nullable()->constrained('bundles')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('tax_method_id')->nullable()->constrained('tax_methods')->onUpdate('restrict')->onDelete('cascade');
        });

        Schema::table('delivery_orders_metas', function (Blueprint $table) {
            $table->foreignId('bundle_id')->nullable()->constrained('bundles')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->onUpdate('restrict')->onDelete('cascade');
            $table->foreignId('tax_method_id')->nullable()->constrained('tax_methods')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quotation_metas', function (Blueprint $table) {
            $table->dropForeign(['bundle_id']);
            $table->dropColumn('bundle_id');
            $table->dropForeign(['variant_id']);
            $table->dropColumn('variant_id');
            $table->dropForeign(['tax_method_id']);
            $table->dropColumn('tax_method_id');
        });

        Schema::table('sales_orders_metas', function (Blueprint $table) {
            $table->dropForeign(['bundle_id']);
            $table->dropColumn('bundle_id');
            $table->dropForeign(['variant_id']);
            $table->dropColumn('variant_id');
            $table->dropForeign(['tax_method_id']);
            $table->dropColumn('tax_method_id');
        });

        Schema::table('invoices_metas', function (Blueprint $table) {
            $table->dropForeign(['bundle_id']);
            $table->dropColumn('bundle_id');
            $table->dropForeign(['variant_id']);
            $table->dropColumn('variant_id');
            $table->dropForeign(['tax_method_id']);
            $table->dropColumn('tax_method_id');
        });

        Schema::table('delivery_orders_metas', function (Blueprint $table) {
            $table->dropForeign(['bundle_id']);
            $table->dropColumn('bundle_id');
            $table->dropForeign(['variant_id']);
            $table->dropColumn('variant_id');
            $table->dropForeign(['tax_method_id']);
            $table->dropColumn('tax_method_id');
        });
    }
}
