<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('savings_goals', function (Blueprint $table) {
            if (! Schema::hasColumn('savings_goals', 'title')) {
                $table->string('title')->nullable()->after('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('savings_goals', function (Blueprint $table) {
            if (Schema::hasColumn('savings_goals', 'title')) {
                $table->dropColumn('title');
            }
        });
    }
};
