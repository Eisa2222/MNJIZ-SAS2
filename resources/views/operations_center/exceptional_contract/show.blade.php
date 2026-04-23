@extends('layouts.layoutMaster')

@section('title', 'عرض تفاصيل العقد الإستثنائي')

@section('breadcrumb')
    <li><a href="#"> مركز العمليات </a></li>
    <li><a href="{{ route('operations-center.exceptional-contracts.index') }}"> العقود اللإستثنائية</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">عرض تفاصيل العقد الإستثنائي</a>
        <i class="ti ti-star favorite-icon" data-page-name="عرض تفاصيل العقد الإستثنائي" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/katex.scss', 'resources/assets/vendor/libs/quill/editor.scss'])

@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/quill/katex.js', 'resources/assets/vendor/libs/quill/quill.js'])

@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/forms-editors.js', 'resources/assets/js/extended-ui-sweetalert2.js'])
@endsection

@section('content')
    <div class="row">


        <div class="col-12 ">
            <div class="card mb-4">
                <div class="d-flex justify-content-between card-header  py-4">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-license text-warning me-2"></i>
                        <h6 class="card-title mb-0"> تفاصيل العقد الإستثنائي </h6>
                    </div>

                    <div>
                        <a href="{{ route('operations-center.exceptional-contracts.export-pdf.official', $exceptional_contract->id) }}"
                            class="btn btn-sm btn-primary">
                            <i class="ti ti-file-type-pdf"></i> تصدير PDF رسمي
                        </a>

                        <a href="{{ route('operations-center.exceptional-contracts.export-pdf.simple', $exceptional_contract->id) }}"
                            class="btn btn-sm btn-primary">
                            <i class="ti ti-file-type-pdf"></i> تصدير PDF مبسط
                        </a>
                    </div>



                </div>
                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body">
                    <div id="preview-info" class="content">
                        <div class="row position-relative">
                            <div id="template-canvas" class="col-12 position-relative">

                                <div class="ql-editor pt-5" style="white-space: normal">

                                    {!! $processedContent !!}

                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- الاعتمادات -->
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center py-4">
                    <i class="ti ti-shield-check text-warning me-2"></i>
                    <h6 class="card-title mb-0"> حالة الاعتمادات</h6>
                </div>
                <div class="border-1 border-light border-dashed mb-2"></div>
                <div class="card-body">
                    @if ($approvals->isEmpty())
                        <p class="text-muted text-center">لا توجد معلومات اعتماد.</p>
                    @else
                        <div class="d-flex flex-wrap justify-content-around w-100">
                            @foreach ($approvals as $approval)
                                <div class="text-center">
                                    <div class="avatar mb-2" style="width:60px; height:60px; margin:0 auto;">
                                        <img src="{{ $approval->approver->profile_picture
                                            ? asset('storage/' . $approval->approver->profile_picture)
                                            : asset('assets/img/avatars/1.png') }}"
                                            alt="Avatar" class="rounded-circle w-100 h-100">
                                    </div>
                                    <div class="small mb-1">{{ $approval->approver->name }}</div>

                                    <span
                                        class="mt-2 badge bg-{{ $approval->status_badge['bg'] }}">{{ $approval->status_name }}
                                    </span>


                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>


            @if ($exceptional_contract->isRejected())
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-center gap-3">
                            <a href="{{ route('operations-center.exceptional-contracts.edit', $exceptional_contract) }}"
                                class="btn btn-warning">
                                <i class="ti ti-edit me-1"></i> اعادة تعديل العقد الإستثنائي
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            @if ($pending)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-center gap-3">
                            <form class="m-0"
                                action="{{ route('operations-center.exceptional-contracts.approve', $exceptional_contract) }}"
                                method="POST">
                                @csrf
                                <button class="btn btn-success">
                                    <i class="ti ti-shield-check me-1"></i> اعتماد
                                </button>
                            </form>
                            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="ti ti-shield-cancel me-1"></i> رفض
                            </button>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="rejectModal" tabindex="-1">
                    <div class="modal-dialog">
                        <form action="{{ route('operations-center.exceptional-contracts.reject', $exceptional_contract) }}"
                            method="POST" class="modal-content">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">سبب الرفض</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <textarea name="reason" class="form-control" rows="4" placeholder="اكتب سبب الرفض هنا..." required>{{ old('reason') }}</textarea>
                                @error('reason')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-danger">تأكيد الرفض</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
