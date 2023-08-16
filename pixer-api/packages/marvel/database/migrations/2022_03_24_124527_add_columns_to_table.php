<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->after('id');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->unsignedBigInteger('variation_option_id')->after('product_id')->nullable();
            $table->foreign('variation_option_id')->references('id')->on('variation_options')->onDelete('cascade');
        });

        Schema::table('wishlists', function (Blueprint $table) {
            $table->unsignedBigInteger('variation_option_id')->after('product_id')->nullable();
            $table->foreign('variation_option_id')->references('id')->on('variation_options')->onDelete('cascade');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('cancelled_amount')->after('total')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn('order_id');
            $table->dropColumn('variation_option_id');
        });

        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropColumn('variation_option_id');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('cancelled_amount');
        });
    }
}
