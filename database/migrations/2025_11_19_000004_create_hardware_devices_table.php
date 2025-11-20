<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('hardware_devices');
    }

    public function down(): void
    {
        // Tidak ada aksi rollback untuk modul yang telah dihapus.
    }
};
