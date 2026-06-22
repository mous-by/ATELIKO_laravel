<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications_ateliko', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('recipient_id');
            $table->text('message');
            $table->string('type')->nullable();
            $table->boolean('is_read')->default(false);
            $table->uuid('related_entity_id')->nullable();
            $table->string('related_entity_type')->nullable();
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamps();

            $table->foreign('recipient_id')->references('id')->on('utilisateurs')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications_ateliko');
    }
};
