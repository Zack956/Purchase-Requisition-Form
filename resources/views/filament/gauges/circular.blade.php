<div class="flex flex-col items-center space-y-2">
    <!-- Circular Gauge -->
    <div class="relative w-20 h-20">
        <svg class="w-full h-full" viewBox="0 0 36 36">
            <path
                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                fill="none"
                stroke="#e5e7eb"
                stroke-width="3"
            />
            <path
                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                fill="none"
                stroke="@php echo match(true) {
                    $percentage <= 25 => '#ef4444',
                    $percentage <= 50 => '#f59e0b',
                    default => '#10b981'
                } @endphp"
                stroke-width="3"
                stroke-dasharray="@php echo $percentage @endphp, 100"
            />
        </svg>
        <div class="absolute inset-0 flex items-center justify-center text-lg font-bold">
            {{ $percentage }}%
        </div>
    </div>
    <div class="text-center">
        <div class="text-sm font-medium">{{ $label ?? 'Budget' }}</div>
        <div class="text-xs text-gray-500">
            {{ $remaining }} / {{ $total }}
        </div>
    </div>
</div>