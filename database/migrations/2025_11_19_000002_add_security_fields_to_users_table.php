<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }

            if (!Schema::hasColumn('users', 'mfa_enabled')) {
                $table->boolean('mfa_enabled')->default(false)->after('remember_token');
            }

            if (!Schema::hasColumn('users', 'mfa_secret')) {
                $table->string('mfa_secret')->nullable()->after('mfa_enabled');
            }

            if (!Schema::hasColumn('users', 'password_changed_at')) {
                $table->timestamp('password_changed_at')->nullable()->after('mfa_secret');
            }

            if (!Schema::hasColumn('users', 'must_change_password')) {
                $table->boolean('must_change_password')->default(false)->after('password_changed_at');
            }
        });

        if (!Schema::hasTable('user_mfa_recovery_codes')) {
            Schema::create('user_mfa_recovery_codes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('code');
                $table->timestamp('used_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_mfa_recovery_codes')) {
            Schema::dropIfExists('user_mfa_recovery_codes');
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'must_change_password')) {
                $table->dropColumn('must_change_password');
            }

            if (Schema::hasColumn('users', 'password_changed_at')) {
                $table->dropColumn('password_changed_at');
            }

            if (Schema::hasColumn('users', 'mfa_secret')) {
                $table->dropColumn('mfa_secret');
            }

            if (Schema::hasColumn('users', 'mfa_enabled')) {
                $table->dropColumn('mfa_enabled');
            }

            if (Schema::hasColumn('users', 'phone')) {
                $table->dropColumn('phone');
            }
        });
    }
};
