<?php

namespace App\Filament\Resources\PurchaseRequisitionResource\Pages;

use App\Filament\Resources\PurchaseRequisitionResource;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Blade;

class ViewPurchaseRequisition extends ViewRecord
{
    protected static string $resource = PurchaseRequisitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('pdf')
                ->label('Download PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function () {
                    return response()->streamDownload(
                        function () {
                            echo Pdf::loadHtml(
                                Blade::render('pdf.requisition', ['record' => $this->record])
                            )->stream();
                        },
                        "requisition-{$this->record->requisition_number}.pdf"
                    );
                }),
        ];
    }
}