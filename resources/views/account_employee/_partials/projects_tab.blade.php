<div class="tab-pane fade" id="projects_tab">
    <div class="row g-4">
        @forelse($projects as $project)
            @php
                // تحديد دور الموظف في المشروع
                if ($project->created_by == $employee->user_id) {
                    $role = 'منشى المشروع';
                } elseif ($project->manager_user_id == $employee->user_id) {
                    $role = 'مدير مشروع';
                } elseif ($project->teamMembers->contains('id', $employee->id)) {
                    $role = 'عضو في المشروع';
                } else {
                    $role = 'غير معروف';
                }
            @endphp

            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0">
                        <div class="d-flex align-items-center">
                            <div>
                                <h5 class="card-title mb-0">
                                    <a href="{{ route('projects.show', $project->id) }}"
                                        class="stretched-link text-heading fs-6">
                                        {{ \Illuminate\Support\Str::limit($project->project_name, 20, '...') }}
                                    </a>
                                </h5>
                                <p class="small text-secondary my-2">الصفة: {{ $role }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-body pb-0">
                        <p class="mb-1">
                            <small>تاريخ البداية: </small>
                            {{ $project->start_date ?? 'غير معروف' }}
                        </p>
                    </div>

                    <div class="card-footer bg-transparent border-0 d-flex justify-content-end">
                        <span class="badge bg-primary">
                            <small>{{ $project->project_status->name }}</small>
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center">
                    <small>لا توجد مشاريع حاليا</small>
                </div>
            </div>
        @endforelse
    </div>
    <!-- روابط التنقل بين الصفحات -->
    @if ($projects->hasPages())
        <div class="card mt-5">
            <div class="d-flex justify-content-center mt-4">
                {{ $projects->appends(['tab' => 'projects_tab'])->links() }}
            </div>
        </div>
    @endif
</div>
