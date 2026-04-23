<div class="text-center">
    <div class="progress" style="height: 10px;">
        <div class="progress-bar {{ $barClass }}" role="progressbar" style="width: {{ $progressWidthPercent }}%;"
            aria-valuenow="{{ $progressWidthPercent }}" aria-valuemin="0" aria-valuemax="100">
        </div>
    </div>
    <small style="font-size: 10px;" class="{{ $statusClass }}">{{ $timeText }}</small>
</div>
