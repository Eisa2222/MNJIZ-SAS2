@extends('layouts.layoutMaster')

@section('title', 'طلب مراجعة للمرتب')

@section('breadcrumb')
    <li><a href="#"> الموارد البشرية</a></li>
    <li><a href="{{ route('hr.payrolls.wps.index') }}">مسيرات الرواتب</a></li>
    <li><a
            href="{{ route('hr.payrolls.wps.details.index', $wps_payroll_details->wpsPayroll->id) }}">{{ $wps_payroll_details->wpsPayroll->reference }}</a>
    </li>

    <li class="breadcrumb-item d-flex align-items-center"><a href="#">طلب مراجعة للمرتب</a>
        <i class="ti ti-star favorite-icon" data-page-name="طلب مراجعة للمرتب" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('page-script')
    @vite(['resources/assets/js/general_additions.js'])
@endsection


@section('content')
    <div class="card mb-6">
        <div class="card-body pt-10">
            <form method="POST"
                action="{{ route('hr.payrolls.wps.details.revision.store', [
                    $wps_payroll_details->wps_payroll_id,
                    $wps_payroll_details->employee_id,
                ]) }}"
                class="fv-plugins-bootstrap5 fv-plugins-framework">
                @csrf
                @method('POST')

                <div class="row g-3 mb-6">
                    {{-- الراتب الأساسي --}}
                    <div class="col-md-6">
                        <label for="basic" class="form-label">الراتب الأساسي</label>
                        <input type="text" step="0.01" id="basic" name="basic"
                            class="form-control  numeric-only @error('basic') is-invalid @enderror"
                            value="{{ old('basic', $wps_payroll_details->getRawOriginal('basic')) }}">
                        @error('basic')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- بدل النقل --}}
                    <div class="col-md-6">
                        <label for="transport" class="form-label">بدل النقل</label>
                        <input type="text" step="0.01" id="transport" name="transport"
                            class="form-control numeric-only @error('transport') is-invalid @enderror"
                            value="{{ old('transport', $wps_payroll_details->getRawOriginal('transport')) }}">
                        @error('transport')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- بدل السكن --}}
                    <div class="col-md-6">
                        <label for="housing" class="form-label">بدل السكن</label>
                        <input type="text" step="0.01" id="housing" name="housing"
                            class="form-control  numeric-only @error('housing') is-invalid @enderror"
                            value="{{ old('housing', $wps_payroll_details->getRawOriginal('housing')) }}">
                        @error('housing')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- بدلات أخرى --}}
                    <div class="col-md-6">
                        <label for="other" class="form-label">بدلات أخرى</label>
                        <input type="text" step="0.01" id="other" name="other"
                            class="form-control  numeric-only @error('other') is-invalid @enderror"
                            value="{{ old('other', $wps_payroll_details->getRawOriginal('other')) }}">
                        @error('other')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- التأمين --}}
                    <div class="col-md-6">
                        <label for="insurance" class="form-label">التأمين</label>
                        <input type="text" step="0.01" id="insurance" name="insurance"
                            class="form-control  numeric-only @error('insurance') is-invalid @enderror"
                            value="{{ old('insurance', $wps_payroll_details->getRawOriginal('insurance')) }}">
                        @error('insurance')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- الاستقطاعات --}}
                    <div class="col-md-6">
                        <label for="deductions" class="form-label">الخصومات</label>
                        <input type="text" step="0.01" id="deductions" name="deductions"
                            class="form-control  numeric-only @error('deductions') is-invalid @enderror"
                            value="{{ old('deductions', $wps_payroll_details->deductionsWithoutInsurance) }}">
                        @error('deductions')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- الحوافز --}}
                    <div class="col-md-6">
                        <label for="incentives" class="form-label">الحوافز</label>
                        <input type="text" step="0.01" id="incentives" name="incentives"
                            class="form-control  numeric-only @error('incentives') is-invalid @enderror"
                            value="{{ old('incentives', $wps_payroll_details->getRawOriginal('incentives')) }}">
                        @error('incentives')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ملاحظات --}}
                    <div class="col-md-12 mb-3">
                        <label for="notes" class="form-label">ملاحظات</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="قم بتوضيح الملاحظات هنا">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <div class="mt-2 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary me-3">
                        حفظ التعديلات
                    </button>
                    <button type="reset" class="btn btn-label-secondary">
                        إلغاء
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
