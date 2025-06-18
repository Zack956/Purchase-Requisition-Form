<?php

namespace App\Services;

use App\Models\DepartmentBudget;
use App\Models\PurchaseRequisition;

class BudgetService
{
    public static function getRemainingBudget(string $department): float
    {
        $budget = DepartmentBudget::where('department', $department)
            ->where('month_year', now()->format('Y-m-01'))
            ->first();

        if (!$budget) return 0;

        // Calculate sum of ALL approved PRs + current PR being created
        $spent = PurchaseRequisition::where('department', $department)
            ->where('status', 'approved')
            ->whereMonth('request_date', now()->month)
            ->sum('total_amount');

        return $budget->amount - $spent;
    }

    public static function deductFromBudget(string $department, float $amount): void
    {
        $budget = DepartmentBudget::where('department', $department)
            ->where('month_year', now()->format('Y-m-01'))
            ->first();

        if ($budget) {
            $budget->increment('used_amount', $amount);
        }
    }
}