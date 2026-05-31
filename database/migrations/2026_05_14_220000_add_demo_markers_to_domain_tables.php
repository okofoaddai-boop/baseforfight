<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['users', 'clubs', 'fighters', 'events', 'club_join_requests'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->boolean('is_demo')->default(false)->after('updated_at');
                $table->string('demo_batch', 80)->nullable()->after('is_demo');
                $table->index(['is_demo', 'demo_batch']);
            });
        }
    }

    public function down(): void
    {
        foreach (['users', 'clubs', 'fighters', 'events', 'club_join_requests'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropIndex([$table->getTable() . '_is_demo_demo_batch_index']);
                $table->dropColumn(['is_demo', 'demo_batch']);
            });
        }
    }
};