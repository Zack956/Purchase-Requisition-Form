<?php

namespace App\Http\Controllers;

use ZipArchive;
use Illuminate\Support\Facades\Storage;

class DownloadQuotationsController extends Controller
{
    public function __invoke($prId)
    {
        $requisition = \App\Models\PurchaseRequisition::with('quotationAttachments')->findOrFail($prId);
        $zip = new ZipArchive;
        $fileName = "quotations-pr-{$requisition->requisition_number}.zip";

        if ($zip->open(storage_path("app/public/{$fileName}"), ZipArchive::CREATE) === TRUE) {
            foreach ($requisition->quotationAttachments as $attachment) {
                $zip->addFile(
                    storage_path("app/public/{$attachment->file_path}"),
                    basename($attachment->file_path)
                );
            }
            $zip->close();
        }

        return Storage::disk('public')->download($fileName);
    }
}