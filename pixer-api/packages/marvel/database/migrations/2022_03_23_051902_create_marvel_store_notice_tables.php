<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Marvel\Enums\StoreNoticePriority;
use Marvel\Enums\StoreNoticeType;

class CreateMarvelStoreNoticeTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('store_notices', function (Blueprint $table) {
            $table->id();
            $table->enum('priority', [
                StoreNoticePriority::HIGH,
                StoreNoticePriority::MEDIUM,
                StoreNoticePriority::LOW,
            ]);
            $table->text('notice');
            $table->text('description')->nullable();
            $table->dateTime('effective_from')->default(now());
            $table->dateTime('expired_at');
            $table->enum(
                'type',
                [
                    StoreNoticeType::ALL_VENDOR,
                    StoreNoticeType::SPECIFIC_VENDOR,
                    StoreNoticeType::ALL_SHOP,
                    StoreNoticeType::SPECIFIC_SHOP,
                ]
            );
            $table->foreignId('created_by')->nullable()->references('id')->on('users');
            $table->foreignId('updated_by')->nullable()->references('id')->on('users');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('store_notice_user', function (Blueprint $table) {
            $table->foreignId('store_notice_id')->nullable()->references('id')->on('store_notices')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->references('id')->on('users')->cascadeOnDelete();
        });
        Schema::create('store_notice_shop', function (Blueprint $table) {
            $table->foreignId('store_notice_id')->nullable()->references('id')->on('store_notices')->cascadeOnDelete();
            $table->foreignId('shop_id')->nullable()->references('id')->on('shops')->cascadeOnDelete();
        });
        Schema::create('store_notice_read', function (Blueprint $table) {
            $table->foreignId('store_notice_id')->nullable()->references('id')->on('store_notices')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->references('id')->on('users')->cascadeOnDelete();
            $table->boolean('is_read')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_notices');
        Schema::dropIfExists('store_notice_user');
        Schema::dropIfExists('store_notice_shop');
        Schema::dropIfExists('store_notice_read');
    }
}
