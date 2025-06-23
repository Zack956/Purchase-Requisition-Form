<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseRequisitionResource\Pages;
use App\Models\PurchaseRequisition;
use App\Services\BudgetService;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Tables\Actions\ViewAction;

class PurchaseRequisitionResource extends Resource
{
    protected static ?string $model = PurchaseRequisition::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('requisition_number')
                            ->default('PR-' . date('Ymd') . '-' . Str::upper(Str::random(6)))
                            ->disabled()
                            ->dehydrated(),
                            
                        Forms\Components\DatePicker::make('request_date')
                            ->required()
                            ->default(now()),
                            
                        Forms\Components\TextInput::make('requested_by')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(3),
                
                Forms\Components\Section::make('Department & Budget')
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
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $remaining = BudgetService::getRemainingBudget($state);
                                    $set('remaining_budget', 'MYR ' . number_format($remaining, 2));
                                }
                            }),
                            
                        Forms\Components\TextInput::make('current_budget')
                            ->label('Current Month Budget')
                            ->disabled()
                            ->dehydrated(),
                            
                        Forms\Components\TextInput::make('remaining_budget')
                            ->label('Remaining Budget')
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->columns(3),
                
                Forms\Components\Section::make('Vendor Information')
                    ->schema([
                        Forms\Components\Select::make('vendor_id')
    ->label('Vendor')
    ->relationship('vendor', 'name')
    ->searchable()
    ->preload()
    ->createOptionForm([
        Forms\Components\TextInput::make('name')
            ->required(),
    ])
    ->createOptionAction(function (FormAction $action) {  // Use FormAction here
        return $action
            ->modalHeading('Create New Vendor')
            ->modalWidth('lg');
    })
                            ->required(),
                    ]),
                
                Forms\Components\Section::make('Purchase Details')
                    ->schema([
                        Forms\Components\Textarea::make('purpose')
                            ->required()
                            ->columnSpanFull(),
                            
                        Forms\Components\Repeater::make('items')
                            ->schema([
                                Forms\Components\TextInput::make('description')
                                    ->required(),
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(function ($get, $set) {
                                        self::calculateTotal($get, $set);
                                    }),
                                Forms\Components\TextInput::make('unit_price')
                                    ->prefix('MYR')
                                    ->numeric()
                                    ->required()
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(function ($get, $set) {
                                        self::calculateTotal($get, $set);
                                    }),
                            ])
                            ->live()
                            ->afterStateUpdated(function ($get, $set) {
                                self::calculateTotal($get, $set);
                            })
                            ->columns(3)
                            ->columnSpanFull(),
                            
                        Forms\Components\TextInput::make('total_amount')
                            ->prefix('MYR')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->live(debounce: 500)
                            ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                $department = $get('department');
                                if ($department) {
                                    $remainingBudget = BudgetService::getRemainingBudget($department);
                                    if ($state > $remainingBudget) {
                                        $set('total_amount', $remainingBudget);
                                        throw new \Exception("Amount exceeds remaining budget of MYR " . number_format($remainingBudget, 2));
                                    }
                                }
                            }),
                    ]),
                
                    Forms\Components\Section::make('Quotation Attachments')
    ->schema([
        Forms\Components\Repeater::make('quotationAttachments')
            ->relationship(
                name: 'quotationAttachments',
                modifyQueryUsing: fn (Builder $query) => $query->orderBy('created_at', 'desc')
            )
            ->schema([
                TextInput::make('file_name')
                    ->label('Display Name')
                    ->required()
                    ->maxLength(255),

                FileUpload::make('file_path')
                    ->label('PDF File')
                    ->directory('quotations/' . now()->format('Y/m'))
                    ->acceptedFileTypes(['application/pdf'])
                    ->preserveFilenames()
                    ->required()
                    ->columnSpan(2)
            ])
            ->columns(3)
            ->collapsible()
            ->maxItems(5)
            ->createItemButtonLabel('Add Quotation')
            ->columnSpanFull()
    ]),
                
                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Textarea::make('remarks')
                            ->columnSpanFull(),
                            
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'pending' => 'Pending Approval',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('draft'),
                    ]),
            ]);
    }

    protected static function calculateTotal($get, $set): void
    {
        $items = $get('items');
        $total = 0;

        if (is_array($items)) {
            foreach ($items as $item) {
                $quantity = floatval($item['quantity'] ?? 0);
                $unitPrice = floatval($item['unit_price'] ?? 0);
                $total += $quantity * $unitPrice;
            }
        }

        $set('total_amount', number_format($total, 2, '.', ''));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('requisition_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('request_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('requested_by')
                    ->searchable(),
                Tables\Columns\TextColumn::make('department')
                    ->searchable(),
                /*Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->formatStateUsing(fn ($state) => $state ?? 'No Vendor')
                    ->searchable()
                    ->sortable(),*/
                /*Tables\Columns\TextColumn::make('total_amount')
                    ->money('MYR')
                    ->label('Total Amount'),*/
                Tables\Columns\TextColumn::make('quotation_attachments_count')
                    ->label('Quotations')
                    ->counts('quotationAttachments')
                    ->formatStateUsing(fn ($state) => "$state PDF(s)"),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),
            ])
            ->filters([
                // Add filters if needed
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('download_requisition')
    ->label('Download PR')
    ->icon('heroicon-o-document-arrow-down')
    ->action(function ($record) {
        return response()->streamDownload(
            function () use ($record) {
                echo Pdf::loadHtml(
                    Blade::render('pdf.requisition', ['record' => $record])
                )
                ->setPaper('a4', 'portrait')
                ->setOption('enable_php', true)
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', true)
                ->setOption('defaultFont', 'DejaVu Sans')
                ->stream();
            },
            "PR-{$record->requisition_number}.pdf"
        );
    }),
    Tables\Actions\Action::make('view_quotations')
    ->label('View Quotes')
    ->icon('heroicon-o-document-magnifying-glass')
    ->modalHeading(fn ($record) => "Quotations for PR {$record->requisition_number}")
    ->modalDescription('Review attached quotation documents')
    ->modalContent(function ($record) {
        // Eager load attachments if not already loaded
        $record->load('quotationAttachments');
        
        return view('filament.tables.quotations-view', [
            'quotations' => $record->quotationAttachments,
            'requisition' => $record // Pass parent record if needed
        ]);
    })
    ->modalSubmitAction(false)
    ->modalCancelActionLabel('Close')
    ->hidden(fn ($record) => $record->quotationAttachments()->count() === 0)
                // Implement zip download if needed
    
])
->bulkActions([
Tables\Actions\BulkActionGroup::make([
    Tables\Actions\DeleteBulkAction::make(),
]),
]);
}

    public static function getRelations(): array
    {
        return [
            // Add relations if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseRequisitions::route('/'),
            'create' => Pages\CreatePurchaseRequisition::route('/create'),
            'edit' => Pages\EditPurchaseRequisition::route('/{record}/edit'),
            'view' => Pages\ViewPurchaseRequisition::route('/{record}'),
        ];
    }
}