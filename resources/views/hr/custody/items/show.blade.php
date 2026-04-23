@extends('layouts.layoutMaster')

@section('title', 'تفاصيل الاصل')

@section('breadcrumb')
    <li><a href="#">الموارد البشرية</a></li>
    <li><a href="{{ route('hr.custody.items.index') }}">إدارة الاصول</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">تفاصيل الاصل</a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل الاصل" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-12 col-md-8">
            <div class="card">
                <div class="card-header py-4">

                    <h6 class="card-title mb-0">
                        <i class="ti ti-list-details text-warning me-2"></i>
                        تفاصيل الاصل
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body px-5">
                    <div class="table-responsive">
                        <table class="table table-striped  small text-center">
                            <tr>
                                <td class="fw-bold">الاسم </td>
                                <td>{{ $item->name }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">الرقم التسلسلي</td>
                                <td>
                                    {{ $item->serial_number }}
                                </td>
                            </tr>

                            <tr>
                                <td class="fw-bold">التصنيف</td>
                                <td>{{ $item->assetCategory->name }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">مرجعية الاصل</td>
                                <td>{{ $item->storageLocation->name }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">حالة الاستخدام</td>
                                <td>{{ $item->use_status->label() }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">الوصف</td>
                                <td>
                                    {{ $item->description }}
                                </td>
                            </tr>

                        </table>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-12 col-md-4">
            {{-- سجل النشاط --}}
            <div class="card">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-logs text-warning me-2"></i>
                        سجل النشاطات
                    </h6>
                </div>
                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body">
                    <ul class="timeline ">
                        <li class="timeline-item">
                            <span class="timeline-point bg-primary"></span>
                            <div class="timeline-event">
                                <small class="timeline-title text-capitalize">
                                    تمت الاضافة بتاريخ
                                </small>
                                <small class="text-muted d-block">
                                    {{ $item->created_at }}
                                </small>
                                <small>
                                    <b>اضيف بواسطة</b>
                                    <a href="{{ route('account.employee.profile', $item->created_by) }}">
                                        {{ $item->createdBy->getRawNameAttribute() }}
                                    </a>
                                </small>
                            </div>
                        </li>

                        @if ($item->updated_by)
                            <li class="timeline-item">
                                <span class="timeline-point bg-warning"></span>
                                <div class="timeline-event">
                                    <small class="timeline-title text-capitalize">
                                        اخر تحديث بتاريخ
                                    </small>
                                    <small class="text-muted d-block">
                                        {{ $item->updated_at }}
                                    </small>
                                    <small>
                                        <b>التعديل بواسطة</b>
                                        <a href="{{ route('account.employee.profile', $item->updated_by) }}">
                                            {{ $item->updatedBy->getRawNameAttribute() }}
                                        </a>
                                    </small>
                                </div>
                            </li>
                        @endif

                    </ul>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header py-4">

                    <h6 class="card-title mb-0">
                        <i class="ti ti-arrows-shuffle text-warning me-2"></i>
                        تفاصيل حركة الاصل
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body px-5">
                    <div class="table-responsive">
                        <table class="table table-striped  small text-center">
                            <tr>
                                <th>الموظف</th>
                                <th>الحركة</th>
                                <th>التاريخ</th>
                                <th>الملاحظات</th>
                            </tr>


                            @forelse($item->logs as $log)
                                <tr>
                                    <td>
                                        <a href="{{ route('account.employee.profile', $log->request->employee->id) }}">
                                            {{ $log->request->employee->getRawNameAttribute() }}
                                        </a>
                                    </td>
                                    <td>{{ $log->action->label() }}</td>
                                    <td>{{ $log->log_date }}</td>
                                    <td>{{ $log->request->notes }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">لا توجد حركات بعد</td>
                                </tr>
                            @endforelse

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
