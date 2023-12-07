<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->integer('account_id');
            $table->integer('subscription_id');
            $table->string('subscription_type');
            $table->string('status');
            $table->string('real_status');
            $table->dateTime('start_date');
            $table->dateTime('expire_date');
            $table->dateTime('renewal_date');
            $table->string('package');
            $table->timestamps();
            $table->index(['account_id', 'start_date', 'expire_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
