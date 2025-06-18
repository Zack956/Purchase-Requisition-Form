<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BudgetOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $currentMonth = now()->format('Y-m-01');
        
        return collect(['IT', 'Purchasing', 'HR', 'Business Control', 'Production Engineering', 'Maintenance'])
            ->map(function ($department) use ($currentMonth) {
                $budget = \App\Models\DepartmentBudget::where('department', $department)
                    ->where('month_year', $currentMonth)
                    ->first();
                    
                $spent = \App\Models\PurchaseRequisition::where('department', $department)
                    ->where('status', 'approved')
                    ->whereMonth('request_date', now()->month)
                    ->sum('total_amount');
                    
                $remaining = $budget ? ($budget->amount - $spent) : 0;
                
                return Stat::make("{$department} Budget", 'MYR ' . number_format($remaining, 2) . ' / MYR ' . number_format($budget?->amount ?? 0, 2))
                    ->description('Remaining / Total')
                    ->color($remaining <= 0 ? 'danger' : ($remaining < ($budget->amount * 0.2) ? 'warning' : 'success'));
            })
            ->toArray();
    }
    
    protected function getRemainingBudgetPercentage($department): float
    {
        $budget = \App\Models\DepartmentBudget::where('department', $department)
            ->where('month_year', now()->format('Y-m-01'))
            ->first();
            
        if (!$budget || $budget->amount == 0) return 0;
        
        $spent = \App\Models\PurchaseRequisition::where('department', $department)
            ->where('status', 'approved')
            ->whereMonth('request_date', now()->month)
            ->sum('total_amount');
            
        return (($budget->amount - $spent) / $budget->amount) * 100;
    }

    protected function getCards(): array
{
    return [
        Card::make('Budget Utilization')
            ->schema([
                Tables\Table::make()
                    ->query(
                        \App\Models\DepartmentBudget::query()
                            ->where('month_year', now()->format('Y-m-01'))
                    )
                    ->columns([
                        Tables\Columns\TextColumn::make('department'),
                        Tables\Columns\TextColumn::make('amount')
                            ->money('MYR')
                            ->label('Budget'),
                        Tables\Columns\TextColumn::make('spent')
                            ->money('MYR')
                            ->getStateUsing(function ($record) {
                                return \App\Models\PurchaseRequisition::where('department', $record->department)
                                    ->whereMonth('request_date', now()->month)
                                    ->where('status', 'approved')
                                    ->sum('total_amount');
                            }),
                        Tables\Columns\TextColumn::make('remaining')
                            ->money('MYR')
                            ->getStateUsing(function ($record) {
                                $spent = \App\Models\PurchaseRequisition::where('department', $record->department)
                                    ->whereMonth('request_date', now()->month)
                                    ->where('status', 'approved')
                                    ->sum('total_amount');
                                return $record->amount - $spent;
                            }),
                    ])
            ])
    ];
}
}
