<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // 1. Para el Check-in (Tiempo)
            $table->timestamp('started_at')->nullable()->after('priority');
            
            // 2. Para los Comentarios del empleado
            $table->text('employee_notes')->nullable()->after('started_at');
            
            // 3. Para la ruta de la imagen de evidencia
            $table->string('evidence_path')->nullable()->after('employee_notes');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['started_at', 'employee_notes', 'evidence_path']);
        });
    }
};