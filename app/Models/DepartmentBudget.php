<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentBudget extends Model
{
    protected $fillable = [
        'department',
        'amount',
        'month_year'
    ];

    protected $casts = [
        'month_year' => 'date:Y-m-d',
        'amount' => 'decimal:2'
    ];

    public function purchaseRequisitions()
    {
        return $this->hasMany(PurchaseRequisition::class);
    }

    public function deductAmount($amount)
    {
        $this->remaining_amount -= $amount;
        $this->save();
    }
}
