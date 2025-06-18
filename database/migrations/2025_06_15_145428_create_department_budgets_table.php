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
        Schema::create('department_budgets', function (Blueprint $table) {
            $table->id();
            $table->enum('department', ['IT', 'Purchasing', 'HR', 'Business Control', 'Production Engineering', 'Maintenance']);
            $table->decimal('amount', 10, 2)->default(0); // Fixed: specify precision and remove ->change()
            $table->decimal('allocated_amount', 12, 2)->after('amount');
            $table->renameColumn('amount', 'remaining_amount');
            $table->date('month_year')->comment('Stores as YYYY-MM-01 to represent whole month');
            $table->timestamps();
            
            $table->unique(['department', 'month_year']);
            $table->index('month_year');
            $table->index('department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_budgets');
    }
};
