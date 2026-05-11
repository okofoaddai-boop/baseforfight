<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fighters', function (Blueprint $table): void {
            if (! Schema::hasColumn('fighters', 'sex')) {
                $table->string('sex', 1)->nullable()->after('birth_date');
                $table->index('sex');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fighters', function (Blueprint $table): void {
            if (Schema::hasColumn('fighters', 'sex')) {
                $table->dropIndex(['sex']);
                $table->dropColumn('sex');
            }
        });
    }
};
