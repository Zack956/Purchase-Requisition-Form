<div class="space-y-4 p-4">
    @forelse($quotations as $quotation)
        <div class="border rounded-lg overflow-hidden bg-white shadow-sm">
            <div class="flex items-center justify-between p-4 bg-gray-50">
                <div class="flex items-center gap-3">
                    <x-heroicon-o-document-text class="w-5 h-5 text-primary-500" />
                    <span class="font-medium">{{ $quotation->file_name }}</span>
                    <span class="text-xs text-gray-500 ml-2">
                        {{ $quotation->created_at->format('M d, Y') }}
                    </span>
                </div>
                <a href="{{ Storage::url($quotation->file_path) }}" 
                   download
                   class="text-sm text-primary-600 hover:underline flex items-center gap-1">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                    Download
                </a>
            </div>
            
            @if (Str::endsWith(strtolower($quotation->file_path), ['.pdf']))
                <iframe src="{{ Storage::url($quotation->file_path) }}#toolbar=0&view=FitH" 
                        class="w-full h-[500px] border-t"
                        frameborder="0"></iframe>
            @else
                <div class="p-4 bg-yellow-50 text-yellow-800 border-t">
                    <p>Preview available only for PDF files</p>
                </div>
            @endif
        </div>
    @empty
        <div class="p-4 text-center text-gray-500">
            No quotation attachments found
        </div>
    @endforelse
    
    @if($quotations->count() > 1)
        <div class="mt-4 flex justify-end">
            <a href="{{ route('filament.download-all', ['pr' => $requisition->id]) }}" 
               class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                <x-heroicon-o-arrow-down-tray class="w-5 h-5 mr-2" />
                Download All (ZIP)
            </a>
        </div>
    @endif
</div>