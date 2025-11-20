<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'default_branch_id')) {
                $table->foreignId('default_branch_id')
                    ->nullable()
                    ->constrained('branches')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('users', 'is_super_admin')) {
                $table->boolean('is_super_admin')->default(false);
            }
        });

        if (Schema::hasTable('user_tokens')) {
            Schema::drop('user_tokens');
        }

        if (Schema::hasTable('user_mfa_recovery_codes')) {
            Schema::drop('user_mfa_recovery_codes');
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'default_branch_id')) {
                $table->dropForeign(['default_branch_id']);
                $table->dropColumn('default_branch_id');
            }

            if (Schema::hasColumn('users', 'is_super_admin')) {
                $table->dropColumn('is_super_admin');
            }
        });

        // Token and recovery code tables are intentionally not recreated.
    }
};
