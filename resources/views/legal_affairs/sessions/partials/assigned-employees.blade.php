@php
    $displayCount = min(5, $assignedUsers->count());
    $remaining = $assignedUsers->count() - $displayCount;
@endphp

<div class="d-flex justify-content-center">
    <div class="d-flex align-items-center gap-1">
        @forelse($assignedUsers->take($displayCount) as $assignee)
            @php
                $profilePicture = $assignee->employee?->profile_picture
                    ? asset('storage/' . $assignee->employee->profile_picture)
                    : asset('assets/img/avatars/1.png');
            @endphp

            <img class="rounded-circle border border-white" style="width: 28px; height: 28px; margin-left: -8px;"
                src="{{ $profilePicture }}" data-bs-toggle="tooltip" title="{{ $assignee->employee?->name ?? 'مستخدم' }}"
                alt="{{ $assignee->employee?->name ?? 'مستخدم' }}">
        @empty
            <span class="badge bg-label-secondary">لا يوجد</span>
        @endforelse

        @if ($remaining > 0)
            <span class="badge bg-label-primary ms-1" data-bs-toggle="tooltip"
                title="{{ $assignedUsers->skip($displayCount)->pluck('employee.name')->filter()->implode('، ') }}">
                +{{ $remaining }}
            </span>
        @endif
    </div>
</div>
