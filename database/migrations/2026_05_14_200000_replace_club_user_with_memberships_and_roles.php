<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('club_user');

        Schema::create('club_memberships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['club_id', 'user_id']);
        });

        Schema::create('club_membership_roles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('club_membership_id')
                ->constrained('club_memberships')
                ->cascadeOnDelete();
            $table->string('role', 30);
            $table->timestamps();

            $table->unique(['club_membership_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_membership_roles');
        Schema::dropIfExists('club_memberships');

        Schema::create('club_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 20)->default('member');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['club_id', 'user_id']);
        });
    }
};
