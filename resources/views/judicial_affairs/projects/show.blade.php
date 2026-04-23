@extends('judicial_affairs/projects/layout')

@section('sections')

    <div class="col-12  order-1 order-md-0 mb-5">
        <div class="row g-6">
            <div class="col-12 col-xxl-8">
                <div class="card h-100 d-flex flex-column">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div class="card-title mb-0">
                            <h5 class="m-0 me-2 text-primary fw-bold">تفاصيل المشروع</h5>
                        </div>
                    </div>
                    <div class="card-body flex-grow-1">
                        <table class="table card-table mb-0">
                            <tbody class="table-border-bottom-0">
                                <!-- اسم المشروع -->
                                <tr>
                                    <td class="w-50 ps-0">
                                        <div class="d-flex justify-content-start align-items-center">
                                            <div class="me-2">
                                                <i class='ti ti-building ti-lg text-heading'></i>
                                            </div>
                                            <h6 class="mb-0 fw-normal">اسم المشروع</h6>
                                        </div>
                                    </td>
                                    <td class="pe-0">
                                        <h6 class="mb-0 text-primary fw-bold">{{ $project->project_name }}</h6>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="w-50 ps-0">
                                        <div class="d-flex justify-content-start align-items-center">
                                            <div class="me-2">
                                                <i class='ti ti-badge ti-lg text-heading'></i>
                                            </div>
                                            <h6 class="mb-0 fw-normal">رقم المشروع</h6>
                                        </div>
                                    </td>
                                    <td class="pe-0">
                                        <h6 class="mb-0 text-primary fw-bold">{{ $project->project_number }}</h6>
                                    </td>
                                </tr>
                                <!-- العميل -->
                                <tr>
                                    <td class="w-50 ps-0">
                                        <div class="d-flex justify-content-start align-items-center">
                                            <div class="me-2">
                                                <i class='ti ti-user ti-lg text-heading'></i>
                                            </div>
                                            <h6 class="mb-0 fw-normal">العقد</h6>
                                        </div>
                                    </td>
                                    <td class="pe-0 text-nowrap">
                                        <h6 class="mb-0 text-primary fw-bold">
                                            {{ $project->contract->contract_name ?? 'العقد غير متاح حالياً' }}</h6>
                                    </td>
                                </tr>
                                <!-- مدير المشروع -->
                                <tr>
                                    <td class="w-50 ps-0">
                                        <div class="d-flex justify-content-start align-items-center">
                                            <div class="me-2">
                                                <i class='ti ti-user-plus ti-lg text-heading'></i>
                                            </div>
                                            <h6 class="mb-0 fw-normal">مدير المشروع</h6>
                                        </div>
                                    </td>
                                    <td class="pe-0">
                                        <h6 class="mb-0 text-primary fw-bold">
                                            {{ $project->manager_user->employee->name ?? 'لم يتم تعيين المدير بعد' }}</h6>
                                    </td>
                                </tr>
                                <!-- تاريخ البدء -->
                                <tr>
                                    <td class="w-50 ps-0">
                                        <div class="d-flex justify-content-start align-items-center">
                                            <div class="me-2">
                                                <i class='ti ti-calendar ti-lg text-heading'></i>
                                            </div>
                                            <h6 class="mb-0 fw-normal">تاريخ البدء</h6>
                                        </div>
                                    </td>
                                    <td class="pe-0">
                                        <h6 class="mb-0 text-primary fw-bold">{{ $project->start_date }}</h6>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="w-50 ps-0">
                                        <div class="d-flex justify-content-start align-items-center">
                                            <div class="me-2">
                                                <i class='ti ti-lock ti-lg text-heading'></i>
                                            </div>
                                            <h6 class="mb-0 fw-normal">الإغلاق التعاقدي</h6>
                                        </div>
                                    </td>
                                    <td class="pe-0">
                                        <h6 class="mb-0 text-primary fw-bold">
                                            {{ $project->contractual_closure ? $project->contractual_closure : 'لا يوجد' }}
                                        </h6>
                                    </td>
                                </tr>

                                <!-- تاريخ الإغلاق -->
                                <tr>
                                    <td class="w-50 ps-0">
                                        <div class="d-flex justify-content-start align-items-center">
                                            <div class="me-2">
                                                <i class='ti ti-calendar ti-lg text-heading'></i>
                                            </div>
                                            <h6 class="mb-0 fw-normal">تاريخ الإغلاق</h6>
                                        </div>
                                    </td>
                                    <td class="pe-0">
                                        <h6 class="mb-0 text-primary fw-bold">
                                            {{ $project->end_date ? $project->end_date : 'لا يوجد' }}
                                        </h6>
                                    </td>
                                </tr>
                                <!-- مدة المشروع -->

                                <!-- مرفق العقد -->
                                <tr>
                                    <td class="w-50 ps-0">
                                        <div class="d-flex justify-content-start align-items-center">
                                            <div class="me-2">
                                                <i class="ti ti-paperclip ti-lg text-heading"></i>
                                            </div>
                                            <h6 class="mb-0 fw-normal">مرفق العقد</h6>
                                        </div>
                                    </td>
                                    <td class="pe-0">
                                        @if ($project->contract_attachment)
                                            <a href="{{ Storage::url($project->contract_attachment) }}" target="_blank"
                                                class="btn btn-sm btn-primary">
                                                عرض المرفق
                                            </a>
                                        @else
                                            <span class="text-primary fw-bold">لا يوجد مرفق</span>
                                        @endif
                                    </td>
                                </tr>
                                <!-- حالة المشروع -->
                                <tr>
                                    <td class="w-50 ps-0">
                                        <div class="d-flex justify-content-start align-items-center">
                                            <div class="me-2">
                                                <i class="ti ti-flag ti-lg text-heading"></i>
                                            </div>
                                            <h6 class="mb-0 fw-normal">حالة المشروع</h6>
                                        </div>
                                    </td>
                                    <td class="pe-0">
                                        <h6 class="mb-0 text-primary fw-bold">
                                            @switch($project->status)
                                                @case('ongoing')
                                                    <span class="badge bg-info">جاري التنفيذ</span>
                                                @break

                                                @case('completed')
                                                    <span class="badge bg-success">مكتمل</span>
                                                @break

                                                @case('postponed')
                                                    <span class="badge bg-warning">مؤجل</span>
                                                @break

                                                @case('canceled')
                                                    <span class="badge bg-danger">ملغي</span>
                                                @break

                                                @default
                                                    <span class="badge bg-secondary">غير محدد</span>
                                            @endswitch
                                        </h6>
                                    </td>
                                </tr>
                                <!-- إجمالي المطالبة -->
                                <tr>
                                    <td class="w-50 ps-0">
                                        <div class="d-flex justify-content-start align-items-center">
                                            <div class="me-2">
                                                <i class='ti ti-currency-dollar ti-lg text-heading'></i>
                                            </div>
                                            <h6 class="mb-0 fw-normal">إجمالي المطالبة</h6>
                                        </div>
                                    </td>
                                    <td class="pe-0">
                                        <h6 class="mb-0 text-primary fw-bold">{{ number_format($project->total_claim, 2) }}
                                            ريال</h6>
                                    </td>
                                </tr>
                                <!-- الإغلاق التعاقدي -->

                                <!-- وصف المشروع -->
                                <tr>
                                    <td class="w-50 ps-0">
                                        <div class="d-flex justify-content-start align-items-center">
                                            <div class="me-2">
                                                <i class="ti ti-file-description ti-lg text-heading"></i>
                                            </div>
                                            <h6 class="mb-0 fw-normal">وصف المشروع</h6>
                                        </div>
                                    </td>
                                    <td class="pe-0">
                                        <h6 class="mb-0 text-primary fw-bold">{{ $project->description ?? 'لا يوجد' }}</h6>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="w-50 ps-0">
                                        <div class="d-flex justify-content-start align-items-center">
                                            <div class="me-2">
                                                <i class="ti ti-user ti-lg text-heading"></i>
                                            </div>
                                            <h6 class="mb-0 fw-normal">اضيف بواسطة</h6>
                                        </div>
                                    </td>
                                    <td class="pe-0">
                                        <h6 class="mb-0 text-primary fw-bold">
                                            {{ $project->start_user ? $project->start_user->employee->name : 'لا يوجد' }}
                                        </h6>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="w-50 ps-0">
                                        <div class="d-flex justify-content-start align-items-center">
                                            <div class="me-2">
                                                <i class="ti ti-user ti-lg text-heading"></i>
                                            </div>
                                            <h6 class="mb-0 fw-normal">استكمل بواسطة</h6>
                                        </div>
                                    </td>
                                    <td class="pe-0">
                                        <h6 class="mb-0 text-primary fw-bold">
                                            {{ $project->complate_user ? $project->complate_user->employee->name : 'لا يوجد' }}
                                        </h6>

                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xxl-4">

                <div class="card h-100 d-flex flex-column">
                    @if ($project->manager_user)
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div class="user-avatar-section text-center">
                                <img class="img-fluid rounded mb-4"
                                    src="{{ asset($project->manager_user->employee->profile_picture ? 'storage/' . $project->manager_user->employee->profile_picture : 'assets/img/avatars/1.png') }}"
                                    height="120" width="120" alt="User avatar" />
                                <h5>{{ $project->manager_user->employee->name }}</h5>
                                <span class="badge bg-label-secondary">مدير المشروع</span>
                            </div>

                            <div class="info-container">
                                <ul class="list-unstyled my-3">
                                    <li class="d-flex align-items-center mb-3"><i class="ti ti-user ti-lg"></i><span
                                            class="fw-medium mx-2"></span>
                                        <span>{{ $project->manager_user->employee->name }}</span>
                                    </li>
                                    <li class="d-flex align-items-center mb-3"><i class="ti ti-crown ti-lg"></i><span
                                            class="fw-medium mx-2"> </span>
                                        <span>{{ $project->manager_user->employee->job_title }}</span>
                                    </li>
                                    <li class="d-flex align-items-center mb-3"><i class="ti ti-flag ti-lg"></i><span
                                            class="fw-medium mx-2"></span>
                                        <span>{{ $project->manager_user->employee->country->name ?? 'غير محدد' }}</span>
                                    </li>
                                    <li class="d-flex align-items-center mb-3"><i class="ti ti-language ti-lg"></i><span
                                            class="fw-medium mx-2">
                                        </span>
                                        <span>{{ $project->manager_user->employee->work_email }}</span>
                                    </li>
                                    <li class="d-flex align-items-center mb-3"><i class="ti ti-phone ti-lg"></i><span
                                            class="fw-medium mx-2"></span>
                                        <span>{{ $project->manager_user->employee->mobile ?? 'غير متوفر' }}</span>
                                    </li>
                                </ul>

                                <div class="d-grid gap-2">
                                    <a href="mailto:{{ $project->manager_user->employee->work_email }}"
                                        class="btn btn-primary">
                                        <i class="ti ti-mail me-1"></i> التواصل عبر البريد الإلكتروني
                                    </a>
                                    @if ($project->manager_user->employee->mobile)
                                        <a href="tel:{{ $project->manager_user->employee->mobile }}"
                                            class="btn btn-success">
                                            <i class="ti ti-phone-call me-1"></i> التواصل عبر الهاتف
                                        </a>
                                    @endif
                                    <a href="{{ route('chat.index') }}" class="btn btn-info">
                                        <i class="ti ti-message me-1"></i> الدردشة مع المدير
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card-body text-center">
                            <h5>لم يتم تعيين المدير بعد</h5>
                        </div>
                    @endif

                </div>
            </div>


        </div>
    </div>
@endsection
