<?php

namespace App\Filament\Widgets;

use App\Models\Vendor;
use App\Models\DepartmentBudget;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BudgetOverview extends BaseWidget
{

    /*
    protected function getStats(): array
    {
        $currentMonth = now()->format('Y-m-01');
        
        // 1. Get Department Budget Stats (Existing)
        $departmentStats = collect(['IT', 'Purchasing', 'HR', 'Business Control', 'Production Engineering', 'Maintenance'])
            ->map(function ($department) use ($currentMonth) {
                $budget = \App\Models\DepartmentBudget::where('department', $department)
                    ->where('month_year', $currentMonth)
                    ->first();
                    
                $spent = \App\Models\PurchaseRequisition::where('department', $department)
                    ->where('status', 'approved')
                    ->whereMonth('request_date', now()->month)
                    ->sum('total_amount');
                    
                $remaining = $budget ? ($budget->amount - $spent) : 0;
                
                return Stat::make("{$department} Budget", 
                    'MYR ' . number_format($remaining, 2) . ' / MYR ' . number_format($budget?->amount ?? 0, 2))
                    ->description('Remaining / Total')
                    ->color($this->getBudgetColor($remaining, $budget->amount ?? 0))
                    ->icon($this->getDepartmentIcon($department));
            })
            ->toArray();
*/

protected function getStats(): array
{
    // 1. Get Department Stats
    $departmentStats = collect(['IT', 'Purchasing', 'HR', 'Business Control', 'Production Engineering', 'Maintenance'])
        ->map(function ($department) {
            $percentage = $this->calculateBudgetPercentage($department);
            
            return Stat::make(
                "{$department} Budget",
                $this->formatMoney($this->getRemainingBudget($department))
            )
            ->icon($this->getDepartmentIcon($department))
            ->color($this->getDepartmentColor($percentage))
            ->description($this->getBudgetDescription($department, $percentage))
            ->view('filament.gauges.circular', [
                'percentage' => $percentage,
                'label' => $department,
                'remaining' => $this->formatMoney($this->getRemainingBudget($department)),
                'total' => $this->formatMoney($this->getTotalBudget($department))
            ]);
        })
        ->toArray();

    // 2. Get Vendor Stats
    $vendorStats = [
        Stat::make('Total Vendors', Vendor::count())
            ->icon('heroicon-o-users')
            ->description('Registered suppliers')
            ->color('gray'),
            
        Stat::make('Active Vendors', Vendor::has('purchaseRequisitions')->count())
            ->icon('heroicon-o-check-badge')
            ->description('With approved PRs')
            ->color('success'),
            
        Stat::make('Top Vendor', $this->getTopVendor())
            ->icon('heroicon-o-trophy')
            ->description('Most purchases this month')
            ->color('warning'),
    ];

    // 3. Combine both sets of stats
    return array_merge($departmentStats, $vendorStats);
}
    // Helper Methods
    protected function getRemainingBudget(string $department): float
    {
        $budget = DepartmentBudget::forDepartment($department)->first();
        if (!$budget) {
            return 0;
        }
        
        $spent = \App\Models\PurchaseRequisition::query()
            ->where('department', $department)
            ->where('status', 'approved')
            ->whereMonth('request_date', now()->month)
            ->sum('total_amount');
        
        // Debug output - remove after verification
        logger()->info("Department {$department} - Budget: {$budget->amount}, Spent: {$spent}");
        
        return $budget->amount - $spent;
    }

protected function calculateBudgetPercentage(string $department): int
{
    $budget = DepartmentBudget::forDepartment($department)->first();
    if (!$budget || $budget->amount == 0) return 0;
    
    $spent = \App\Models\PurchaseRequisition::where('department', $department)
        ->where('status', 'approved')
        ->whereMonth('request_date', now()->month)
        ->sum('total_amount');
    
    return min(100, max(0, round(($spent / $budget->amount) * 100)));
}

protected function getTotalBudget(string $department): float
{
    return DepartmentBudget::forDepartment($department)->first()->amount ?? 0;
}

protected function formatMoney(float $amount): string
{
    return 'MYR ' . number_format($amount);
}

    /*protected function getBudgetColor(float $remaining, float $total): string
    {
        if ($remaining <= 0) return 'danger';
        if ($remaining < ($total * 0.2)) return 'warning';
        return 'success';
    }*/

    protected function getDepartmentIcon(string $department): string
    {
        return match($department) {
            'IT' => 'heroicon-o-computer-desktop',
            'Purchasing' => 'heroicon-o-shopping-bag',  // Changed from shopping-cart
            'HR' => 'heroicon-o-user-group',            // Changed from users
            'Business Control' => 'heroicon-o-building-office-2',
            'Production Engineering' => 'heroicon-o-cog-6-tooth',
            'Maintenance' => 'heroicon-o-wrench-screwdriver',
            default => 'heroicon-o-building-library'    // Changed from office-building
        };
    }

    protected function getDepartmentColor(float $percentage): string
{
    return match(true) {
        $percentage > 80 => 'danger',
        $percentage > 50 => 'warning',
        default => 'success'
    };
}

protected function getBudgetDescription(string $department, float $percentage): string
{
    return "{$percentage}% of budget used";  // This now correctly shows spent percentage
}

    protected function getTopVendor(): string
    {
        $vendor = Vendor::withCount(['purchaseRequisitions as pr_count' => function($query) {
                $query->where('status', 'approved')
                    ->whereMonth('request_date', now()->month);
            }])
            ->orderByDesc('pr_count')
            ->first();

        return $vendor ? "{$vendor->name} ({$vendor->pr_count})" : 'N/A';
    }
}