<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @foreach($quotations as $quotation)
    <div class="border rounded-lg p-4 bg-white shadow-sm">
        <div class="h-64">
            <iframe 
                src="{{ Storage::url($quotation->file_path) }}#toolbar=0&navpanes=0" 
                class="w-full h-full border"
                frameborder="0"
            ></iframe>
        </div>
        <div class="mt-2 flex justify-between items-center">
            <span class="text-sm text-gray-600 truncate">{{ $quotation->file_name }}</span>
            <a 
                href="{{ Storage::url($quotation->file_path) }}" 
                download
                class="text-primary-500 hover:text-primary-600 text-sm"
            >
                Download
            </a>
        </div>
    </div>
    @endforeach
</div>