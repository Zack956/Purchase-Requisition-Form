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
{
    Schema::table('quotation_attachments', function (Blueprint $table) {
        $table->dropForeign(['purchase_requisition_id']); // If exists
        
        $table->foreign('purchase_requisition_id')
              ->references('id')
              ->on('purchase_requisitions') // Explicit table name
              ->onDelete('cascade');
    });
}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
