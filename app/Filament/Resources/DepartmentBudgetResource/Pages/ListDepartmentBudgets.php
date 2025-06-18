<?php

namespace App\Filament\Resources\DepartmentBudgetResource\Pages;

use App\Filament\Resources\DepartmentBudgetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDepartmentBudgets extends ListRecords
{
    protected static string $resource = DepartmentBudgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
