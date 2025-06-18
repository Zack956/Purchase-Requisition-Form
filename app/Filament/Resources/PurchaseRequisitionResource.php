<?php

namespace App\Filament\Resources;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Blade;
use App\Filament\Resources\PurchaseRequisitionResource\Pages;
use App\Models\PurchaseRequisition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Services\BudgetService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;


//Jackwan Skelleton brain
class PurchaseRequisitionResource extends Resource
{
    protected static ?string $model = PurchaseRequisition::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
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
                        $budget = \App\Models\DepartmentBudget::where('department', $state)
                            ->where('month_year', now()->format('Y-m-01'))
                            ->first();
                            
                        $spent = \App\Models\PurchaseRequisition::where('department', $state)
                            ->where('status', 'approved')
                            ->whereMonth('request_date', now()->month)
                            ->sum('total_amount');
                            
                            $remaining = BudgetService::getRemainingBudget($state);
                            $set('remaining_budget', 'MYR ' . number_format($remaining, 2));
                    }
                }),

                Forms\Components\Select::make('vendor_id')
    ->label('Vendor')
    ->relationship('vendor', 'name')
    ->searchable()
    ->preload()
    ->createOptionForm([
        Forms\Components\TextInput::make('name')
            ->required(),
        // Add other vendor fields as needed
    ])
    ->createOptionAction(function (Action $action) {
        return $action
            ->modalHeading('Create New Vendor')
            ->modalWidth('lg');
    }),
                
            Forms\Components\TextInput::make('current_budget')
                ->label('Current Month Budget')
                ->disabled()
                ->dehydrated(),
                
            Forms\Components\TextInput::make('remaining_budget')
                ->label('Remaining Budget')
                ->disabled()
                ->dehydrated(),
                    
                //Forms\Components\TextInput::make('department')
                //    ->required()
                //    ->maxLength(255),
                    
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

    Forms\Components\Repeater::make('quotationAttachments')
    ->relationship()
    ->schema([
        FileUpload::make('file_path')
            ->label('PDF Quotation')
            ->directory('quotations')
            ->preserveFilenames()
            ->acceptedFileTypes(['application/pdf'])
            ->downloadable()
            ->openable() // Enables preview
            ->previewable(true)
            ->required()
    ])
    ->collapsible()
    ->maxItems(5) // Limit to 5 quotations
    ->columnSpanFull(),
                    
                //Forms\Components\TextInput::make('total_amount')
                //    ->prefix('MYR')
                //    ->numeric()
                //    ->disabled()
                //    ->dehydrated(),
                    
                Forms\Components\Textarea::make('remarks')
                    ->columnSpanFull(),

                Forms\Components\Select::make('vendor_id')
                    ->label('Vendor')
                    ->relationship('vendor', 'name')
                    ->required(),
                    
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending' => 'Pending Approval',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required()
                    ->default('draft'),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
{
    $remainingBudget = BudgetService::getRemainingBudget($data['department']);
    
    // Include current PR amount in the check
    $totalAfterThisPR = $remainingBudget - $data['total_amount'];
    
    if ($totalAfterThisPR < 0) {
        throw new \Exception("Insufficient budget. Remaining after this PR would be: MYR " 
            . number_format($totalAfterThisPR, 2));
    }
    
    return $data;
}

protected function afterCreate(): void
{
    //
}

protected function afterSave(): void
{
    // Budget deduct when status change to approve
    if ($this->record->wasChanged('status') && $this->record->status === 'approved') {
        BudgetService::deductFromBudget(
            $this->record->department,
            $this->record->total_amount
        );
        
        // Refresh the budget display if form is still open
        $this->refreshFormData([
            'remaining_budget' => 'MYR ' . number_format(
                BudgetService::getRemainingBudget($this->record->department), 
                2
            )
        ]);
    }
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

    public function quotationAttachments()
{
    return $this->hasMany(QuotationAttachment::class, 'purchase_requisition_id');
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

                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->formatStateUsing(fn ($state) => 'MYR ' . number_format($state, 2))
                    ->label('Total Amount'),

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
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                // PDF download
                Tables\Actions\Action::make('download_requisition')
                    ->label('Download PR')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function ($record) {
                        return response()->streamDownload(
                            function () use ($record) {
                                echo Pdf::loadHtml(
                                    Blade::render('pdf.requisition', ['record' => $record])
                                )->stream();
                            },
                            "requisition-{$record->requisition_number}.pdf"
                        );
                    }),
                
                // View PDF quotation
                Tables\Actions\Action::make('view_quotations')
    ->label('View Quotes')
    ->icon('heroicon-o-document-magnifying-glass')
    ->modalHeading('Quotation Attachments')
    ->modalDescription(fn ($record) => "Viewing quotations for PR {$record->requisition_number}")
    ->modalContent(function ($record) {
        return view('filament.tables.quotations-view', [
            'quotations' => $record->quotationAttachments
        ]);
    })
    ->modalSubmitAction(false)
    ->modalCancelActionLabel('Close')
    ->hidden(fn ($record) => !$record->quotationAttachments || $record->quotationAttachments->isEmpty())
                ]);
    }
                  

    public static function getRelations(): array
    {
        return [];
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