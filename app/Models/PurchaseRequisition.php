<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseRequisition extends Model
{
    protected $fillable = [
        'requisition_number',
        'request_date',
        'requested_by',
        'department',
        'purpose',
        'status',
        'items',
        'total_amount',
        'remarks'
    ];

    protected $attributes = [
        'request_date' => null, // Temporary fix for development
    ];

    public function quotationAttachments(): HasMany
    {
        return $this->hasMany(QuotationAttachment::class, 'purchase_requisition_id');
    }

    public function vendor()
{
    return $this->belongsTo(Vendor::class);
}

    public function departmentBudget()
    {
        return $this->belongsTo(DepartmentBudget::class, 'department', 'department');
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'department' => 'required',
        'amount' => 'required|numeric|min:0',
        'description' => 'required'
    ]);

    // Get current budget
    $budget = DepartmentBudget::where('department', $validated['department'])
                             ->where('month_year', now()->format('Y-m-01'))
                             ->firstOrFail();

    // Check sufficient funds
    if ($budget->remaining_amount < $validated['amount']) {
        return back()->withErrors(['amount' => 'Insufficient budget remaining']);
    }

    // Create PR and deduct budget
    DB::transaction(function () use ($validated, $budget) {
        $pr = PurchaseRequisition::create($validated + ['status' => 'pending']);
        $budget->deductAmount($validated['amount']);
    });

    return redirect()->route('pr.index')->with('success', 'PR created successfully');
}

    

    protected $casts = [
        'items' => 'array',
        'request_date' => 'date',
        'total_amount' => 'decimal:2'
    ];

    protected static function booted()
    {
        static::creating(function ($requisition) {
            if (empty($requisition->requisition_number)) {
                $requisition->requisition_number = 'PR-' . date('Ymd') . '-' . strtoupper(uniqid());
            }
        });

        static::saving(function ($requisition) {
            $requisition->total_amount = collect($requisition->items)->sum(function ($item) {
                return ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
            });
        });
    }
}