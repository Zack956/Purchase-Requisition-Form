<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentBudgetResource\Pages;
use App\Filament\Resources\DepartmentBudgetResource\RelationManagers;
use App\Models\DepartmentBudget;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DepartmentBudgetResource extends Resource
{
    protected static ?string $model = DepartmentBudget::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Select::make('department')
                ->options([
                    'IT' => 'IT',
                    'Purchasing' => 'Purchasing',
                    'HR' => 'HR',
                    'Business Control' => 'Business Control',
                    'Production Engineering' => 'Production Engineering',
                    'Maintenance' => 'Maintenance',
                ])
                ->required(),
                
            Forms\Components\DatePicker::make('month_year')
                ->label('Month/Year')
                ->displayFormat('M Y')
                ->format('Y-m-d')
                ->default(now()->startOfMonth())
                ->required(),
                
            Forms\Components\TextInput::make('amount')
                ->prefix('MYR')
                ->numeric()
                ->required(),
        ]);
}

    

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            Tables\Columns\TextColumn::make('department'),
            Tables\Columns\TextColumn::make('month_year')
                ->date('M Y'),
            Tables\Columns\TextColumn::make('amount')
                ->money('MYR'),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('department')
                ->options([
                    'IT' => 'IT',
                    'Purchasing' => 'Purchasing',
                    // ... other departments
                ]),
            Tables\Filters\Filter::make('month_year')
                ->form([
                    Forms\Components\DatePicker::make('month_year')
                        ->label('Month')
                        ->displayFormat('M Y')
                        ->format('Y-m-d')
                ])
                ->query(function ($query, array $data) {
                    if ($data['month_year']) {
                        $query->where('month_year', $data['month_year']);
                    }
                })
        ]);
}

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

public static function canCreate(): bool
{
    return auth()->user()?->hasRole('admin') ?? false;
}

// Similarly for edit/delete

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepartmentBudgets::route('/'),
            'create' => Pages\CreateDepartmentBudget::route('/create'),
            'edit' => Pages\EditDepartmentBudget::route('/{record}/edit'),
        ];
    }
}
