<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationAttachment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'purchase_requisition_id',
        'vendor_id', // Add this
        'file_path',
        'file_name'
    ];

    /**
     * Get the purchase requisition that owns the quotation attachment.
     */
    public function purchaseRequisition() {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function vendor()
{
    return $this->belongsTo(Vendor::class);
}
}