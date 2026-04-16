<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('code_entities', function (Blueprint $table) {
            $table->string('language', 50)->nullable()->after('type');
        });

        Schema::table('repositories', function (Blueprint $table) {
            $table->json('languages')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('code_entities', function (Blueprint $table) {
            $table->dropColumn('language');
        });

        Schema::table('repositories', function (Blueprint $table) {
            $table->dropColumn('languages');
        });
    }
};
