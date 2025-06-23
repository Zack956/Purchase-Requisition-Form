<div class="flex flex-col space-y-2">
    <!-- Bar Progress Indicator -->
    <div class="w-full h-4 bg-gray-200 rounded-full overflow-hidden">
        <div 
            class="h-full rounded-full"
            style="
                width: {{ $percentage }}%;
                background-color: @php echo match(true) {
                    $percentage <= 25 => '#ef4444',    // red
                    $percentage <= 50 => '#f59e0b',    // amber
                    default => '#10b981'               // emerald
                } @endphp;
            "
        ></div>
    </div>
    
    <!-- Labels -->
    <div class="flex justify-between text-xs">
        <span class="font-medium">{{ $label ?? 'Budget' }}</span>
        <span class="text-gray-500">
            {{ $remaining }} / {{ $total }} ({{ $percentage }}%)
        </span>
    </div>
</div>