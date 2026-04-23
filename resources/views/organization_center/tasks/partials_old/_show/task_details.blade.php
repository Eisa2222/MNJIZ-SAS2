    <div class="tab-pane fade show active" id="taskDetails">
        <div class="row g-2">
            <div class="col-12 col-md-8 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap mb-6 gap-2">
                            <div class="me-1">
                                <h5>
                                    {{ $task->task_name }}
                                </h5>
                                @if ($task->status === 'pending')
                                    <span class="badge fw-bold rounded-pill bg-danger">غير مكتملة</span>
                                @elseif($task->status === 'completed')
                                    <span class="mr-2 badge fw-bold rounded-pill bg-success">مكتملة</span>
                                @endif
                            </div>
                            <div class="d-flex">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-label-danger">{{ $task->task_field_in_arabic }}</span>
                                    <i class="icon-base ti tabler-share icon-lg mx-4"></i>
                                    <i class="icon-base ti tabler-bookmarks icon-lg"></i>
                                </div>

                                <div>
                                    <span class="priority-indicator priority-{{ $task->priority }}"></span>
                                    {{ $task->priority_in_arabic }}
                                </div>
                            </div>
                        </div>

                        <div class="card academy-content shadow-none border">

                            <div class="card-body pt-4">


                                <hr class="my-6">
                                <h6>وصف المهمة </h6>
                                <p class="mb-6 fw-bold">
                                    {{ $task->description ?? 'لا يوجد وصف' }}
                                </p>
                                <hr class="my-6">
                                <h6>المكلفين</h6>
                                <div class="row">
                                    @foreach ($task->assignedUsers as $item)
                                        <div class="col-md-6 mb-4">
                                            <a href="{{ route('account.employee.profile', $item->id) }}" class="mb-1">
                                                <div class="d-flex align-items-center user-name border p-2">
                                                    <div class="avatar-wrapper me-4">
                                                        <img src="{{ $item->profile_picture ? asset('storage/' . $item->profile_picture) : asset('assets/img/avatars/1.png') }}"
                                                            alt="Avatar" class="rounded-circle" width="40">
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <p class="mb-1 fw-semibold">{{ $item->name }}</p>
                                                        <span
                                                            class="fw-medium">{{ $item->getRoleNames()->implode(', ') }}</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

            </div>
            <div class="col-12 col-md-4">
                <div class="card border h-100">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="ti ti-info-circle me-2"></i> معلومات أساسية
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <p class="mb-2">
                                    <strong><i class="ti ti-tag me-2"></i> الأولوية:</strong>
                                </p>
                                <p class="mb-2">
                                    <strong><i class="ti ti-category me-2"></i> المجال:</strong>
                                </p>
                                <p class="mb-2">
                                    <strong><i class="ti ti-calendar me-2"></i> تاريخ الإنشاء:</strong>
                                </p>
                                <p class="mb-2">
                                    <strong><i class="ti ti-calendar-due me-2"></i> تاريخ الاستحقاق:</strong>
                                </p>
                            </div>
                            <div class="col-6">
                                <p class="mb-2">
                                    <span
                                        class="badge
                                            {{ $task->priority === 'low' ? 'bg-success' : ($task->priority === 'medium' ? 'bg-warning' : 'bg-danger') }}">
                                        {{ $task->priority_text }}
                                    </span>
                                </p>
                                <p class="mb-2">
                                    <span class="badge bg-info">{{ $task->task_field_text }}</span>
                                </p>
                                <p class="mb-2">
                                    {{ $task->created_at->format('Y-m-d H:i') }}
                                </p>
                                <p class="mb-2">
                                    {{ $task->due_date }} {{ $task->due_time }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <h6>تم إنشاءها بواسطة</h6>
                <div class="d-flex align-items-center">
                    <div class="me-2">
                        <img src="{{ $task->createdBy->employee->profile_picture
                            ? asset('storage/' . $task->createdBy->employee->profile_picture)
                            : asset('assets/img/avatars/1.png') }}"
                            class="rounded-circle" width="40" alt="Avatar">
                    </div>
                    <div class="info-value fw-bold">
                        {{ $task->createdBy->name }}
                    </div>
                </div>
            </div>
        </div>
    </div>
