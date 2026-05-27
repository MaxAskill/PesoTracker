<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('savings_goals', function (Blueprint $table) {
            if (!Schema::hasColumn('savings_goals', 'saved_amount')) {
                $table->decimal('saved_amount', 10, 2)->default(0);
            }

            if (!Schema::hasColumn('savings_goals', 'status')) {
                $table->string('status')->default('active');
            }

            if (!Schema::hasColumn('savings_goals', 'description')) {
                $table->text('description')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('savings_goals', function (Blueprint $table) {
            $columns = array_filter(
                ['saved_amount', 'status', 'description'],
                fn ($column) => Schema::hasColumn('savings_goals', $column)
            );

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
