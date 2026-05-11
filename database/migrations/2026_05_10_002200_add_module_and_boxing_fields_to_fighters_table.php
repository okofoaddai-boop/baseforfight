<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fighters', function (Blueprint $table): void {
            if (! Schema::hasColumn('fighters', 'sport_modules')) {
                $table->json('sport_modules')->nullable()->after('weight_class');
            }

            if (! Schema::hasColumn('fighters', 'boxing_weight_entries')) {
                $table->json('boxing_weight_entries')->nullable()->after('sport_modules');
            }

            if (! Schema::hasColumn('fighters', 'boxing_bout_count_entries')) {
                $table->json('boxing_bout_count_entries')->nullable()->after('boxing_weight_entries');
            }

            if (! Schema::hasColumn('fighters', 'boxing_pass_entries')) {
                $table->json('boxing_pass_entries')->nullable()->after('boxing_bout_count_entries');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fighters', function (Blueprint $table): void {
            if (Schema::hasColumn('fighters', 'boxing_pass_entries')) {
                $table->dropColumn('boxing_pass_entries');
            }

            if (Schema::hasColumn('fighters', 'boxing_bout_count_entries')) {
                $table->dropColumn('boxing_bout_count_entries');
            }

            if (Schema::hasColumn('fighters', 'boxing_weight_entries')) {
                $table->dropColumn('boxing_weight_entries');
            }

            if (Schema::hasColumn('fighters', 'sport_modules')) {
                $table->dropColumn('sport_modules');
            }
        });
    }
};
