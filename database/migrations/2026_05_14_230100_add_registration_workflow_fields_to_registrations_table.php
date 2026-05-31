<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table): void {
            $table->dateTime('billable_at')->nullable()->after('fighter_snapshot');
            $table->string('billable_reason', 50)->nullable()->after('billable_at');
            $table->dateTime('withdrawn_at')->nullable()->after('billable_reason');
            $table->dateTime('status_changed_at')->nullable()->after('withdrawn_at');

            $table->index(['event_id', 'billable_at']);
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table): void {
            $table->dropIndex(['event_id', 'billable_at']);
            $table->dropColumn(['billable_at', 'billable_reason', 'withdrawn_at', 'status_changed_at']);
        });
    }
};