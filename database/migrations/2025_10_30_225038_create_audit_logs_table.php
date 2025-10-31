<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditLogsTable extends Migration
{
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('table_name', 128);
            $table->unsignedBigInteger('record_id')->nullable();
            $table->string('field_name')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('action', 32); // created, updated, deleted, restored
            $table->text('meta')->nullable(); // JSON with extra data if needed
            $table->timestamp('created_at')->useCurrent();
            // no updated_at (audit is immutable)
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
}
