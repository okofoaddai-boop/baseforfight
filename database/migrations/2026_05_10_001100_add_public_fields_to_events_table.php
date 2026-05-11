<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->text('description')->nullable()->after('title');
            $table->unsignedInteger('entry_fee_cents')->nullable()->after('max_registrations');
            $table->char('currency', 3)->default('EUR')->after('entry_fee_cents');
            $table->json('info_documents')->nullable()->after('currency');
            $table->dateTime('published_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropColumn([
                'description',
                'entry_fee_cents',
                'currency',
                'info_documents',
                'published_at',
            ]);
        });
    }
};
