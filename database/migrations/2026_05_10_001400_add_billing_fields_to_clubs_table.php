<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clubs', function (Blueprint $table): void {
            $table->string('billing_company_name')->nullable()->after('description');
            $table->string('billing_contact_name')->nullable()->after('billing_company_name');
            $table->string('billing_email')->nullable()->after('billing_contact_name');
            $table->string('billing_address_line1')->nullable()->after('billing_email');
            $table->string('billing_address_line2')->nullable()->after('billing_address_line1');
            $table->string('billing_zip')->nullable()->after('billing_address_line2');
            $table->string('billing_city')->nullable()->after('billing_zip');
            $table->string('billing_country', 2)->default('DE')->after('billing_city');
        });
    }

    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table): void {
            $table->dropColumn([
                'billing_company_name',
                'billing_contact_name',
                'billing_email',
                'billing_address_line1',
                'billing_address_line2',
                'billing_zip',
                'billing_city',
                'billing_country',
            ]);
        });
    }
};
