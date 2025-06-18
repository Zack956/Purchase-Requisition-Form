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
        Schema::table('quotation_attachments', function (Blueprint $table) {
            // Add missing columns
            if (!Schema::hasColumn('quotation_attachments', 'file_name')) {
                $table->string('file_name')->after('purchase_requisition_id');
            }
            
            // Fix foreign key if needed
            $table->foreign('purchase_requisition_id')
                  ->references('id')
                  ->on('purchase_requisitions')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
