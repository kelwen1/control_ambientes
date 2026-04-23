<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('nivel_programa')) {
            return;
        }
        if (Schema::hasColumn('nivel_programa', 'id_duracion')) {
            return;
        }

        Schema::table('nivel_programa', function (Blueprint $table) {
            $table->unsignedInteger('id_duracion')->nullable();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('nivel_programa')) {
            return;
        }
        if (! Schema::hasColumn('nivel_programa', 'id_duracion')) {
            return;
        }

        Schema::table('nivel_programa', function (Blueprint $table) {
            $table->dropColumn('id_duracion');
        });
    }
};
