@php
    $displayCount = min(4, $assignedUsers->count());
    $remaining = $assignedUsers->count() - $displayCount;
@endphp

@for ($i = 0; $i < $displayCount; $i++)
    @php
        $assignee = $assignedUsers[$i];
        $profilePicture = $assignee->employee?->profile_picture
            ? asset('storage/' . $assignee->employee->profile_picture)
            : asset('assets/img/avatars/1.png');
    @endphp

    <img class="avatar avatar-task rounded-circle" style="width: 30px; height: 30px;" src="{{ $profilePicture }}"
        alt="{{ $assignee->employee?->name ?? 'مستخدم' }}" title="{{ $assignee->employee?->name ?? 'مستخدم' }}">
@endfor

@if ($remaining > 0)
    <div class="remaining-avatars" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true"
        data-bs-content="{{ $remaining }} مستخدم إضافي"
        style="width: 30px; height: 30px; border-radius: 50%; margin-left: -16px;
                    background: #ccc; display: flex; align-items: center; justify-content: center;
                    font-size: 12px; cursor: pointer;">
        +{{ $remaining }}
    </div>
@endif
