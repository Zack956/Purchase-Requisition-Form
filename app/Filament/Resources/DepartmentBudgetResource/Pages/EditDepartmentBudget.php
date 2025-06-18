<?php

namespace App\Filament\Resources\DepartmentBudgetResource\Pages;

use App\Filament\Resources\DepartmentBudgetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDepartmentBudget extends EditRecord
{
    protected static string $resource = DepartmentBudgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
