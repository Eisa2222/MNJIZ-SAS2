<div class="card">
    <div class="card-header">
        <h5 class="text-primary fw-bold mb-0">سجل النشاط</h5>
    </div>
    <div class="card-body">
        @if ($task->tasksLogs->count())
            <ul class="timeline">
                @foreach ($task->tasksLogs as $log)
                    @php
                        // تحديد لون الشارة بناءً على حالة المهمة
                        $iconClass = 'bg-secondary';
                        switch ($log->event_type) {
                            case 'pending':
                                $iconClass = 'bg-info';
                                break;
                            case 'in_progress':
                                $iconClass = 'bg-primary';
                                break;
                            case 'completed':
                                $iconClass = 'bg-success';
                                break;
                            case 'cancel_completion':
                                $iconClass = 'bg-danger';
                                break;
                        }
                    @endphp
                    <li class="timeline-item">
                        <span class="timeline-point {{ $iconClass }}"></span>
                        <div class="timeline-event">
                            <h6 class="timeline-title text-capitalize">
                              {{ $log->message }}
                            </h6>
                                <p class="text-muted">بواسطة: {{ $log->user->name }}   </p>
                            <small class="text-muted">{{ $log->created_at->format('Y-m-d H:i A') }}</small>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-muted mb-0">لا يوجد سجلات بعد.</p>
        @endif
    </div>
</div>