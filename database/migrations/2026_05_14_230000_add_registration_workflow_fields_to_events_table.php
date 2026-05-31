<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->string('registration_approval_mode', 20)->default('auto')->after('registration_deadline');
            $table->timestamp('billing_locked_at')->nullable()->after('allow_waitlist');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropColumn(['registration_approval_mode', 'billing_locked_at']);
        });
    }
};