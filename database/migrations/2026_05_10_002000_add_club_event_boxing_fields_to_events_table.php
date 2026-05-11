<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->boolean('allow_waitlist')->default(false)->after('max_registrations');
            $table->string('venue_name')->nullable()->after('allow_waitlist');
            $table->string('address_line1')->nullable()->after('venue_name');
            $table->string('address_line2')->nullable()->after('address_line1');
            $table->string('postal_code', 50)->nullable()->after('address_line2');
            $table->string('city')->nullable()->after('postal_code');
            $table->char('country', 2)->nullable()->after('city');
            $table->string('boxing_package_key', 80)->nullable()->after('country');
            $table->json('boxing_age_classes')->nullable()->after('boxing_package_key');
            $table->json('boxing_sexes')->nullable()->after('boxing_age_classes');
            $table->json('boxing_performance_classes')->nullable()->after('boxing_sexes');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropColumn([
                'allow_waitlist',
                'venue_name',
                'address_line1',
                'address_line2',
                'postal_code',
                'city',
                'country',
                'boxing_package_key',
                'boxing_age_classes',
                'boxing_sexes',
                'boxing_performance_classes',
            ]);
        });
    }
};