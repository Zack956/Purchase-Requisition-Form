<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('quotation_attachments', function (Blueprint $table) {
        // Check if old column exists first
        if (Schema::hasColumn('quotation_attachments', 'requisition_id')) {
            $table->renameColumn('requisition_id', 'purchase_requisition_id');
        }
    });
}

public function down()
{
    Schema::table('quotation_attachments', function (Blueprint $table) {
        $table->renameColumn('purchase_requisition_id', 'requisition_id');
    });
}
};
