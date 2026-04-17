<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('code_entities', function (Blueprint $table) {
            $table->json('technical_debt')->nullable()->after('vulnerabilities');
        });
    }

    public function down(): void
    {
        Schema::table('code_entities', function (Blueprint $table) {
            $table->dropColumn('technical_debt');
        });
    }
};
