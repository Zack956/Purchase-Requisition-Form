<div class="space-y-4">
    @foreach($quotations as $quotation)
    <div class="border rounded-lg p-4 bg-white shadow-sm">
        <div class="flex justify-between items-center mb-2">
            <h3 class="font-medium">{{ $quotation->file_name }}</h3>
            <a href="{{ Storage::url($quotation->file_path) }}" 
               download
               class="text-primary-500 hover:text-primary-600">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>
        <iframe src="{{ Storage::url($quotation->file_path) }}#toolbar=0" 
                class="w-full h-[500px] border"
                frameborder="0"></iframe>
    </div>
    @endforeach
</div>