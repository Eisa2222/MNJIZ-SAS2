{{-- resources/views/hr/payroll/show.blade.php --}}
@extends('layouts.layoutMaster')

@section('title', 'تفاصيل المرتب')

@section('breadcrumb')
    <li><a href="#"> الموارد البشرية</a></li>
    <li><a href="{{ route('hr.payrolls.wps.index') }}">مسيرات الرواتب</a></li>
    <li><a
            href="{{ route('hr.payrolls.wps.details.index', $wps_payroll_details->wpsPayroll->id) }}">{{ $wps_payroll_details->wpsPayroll->reference }}</a>
    </li>

    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تفاصيل المرتب</a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل المرتب" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/css/components/icons.css'])
@endsection


@section('content')

    <div>
        <!-- البطاقة الرئيسية -->
        <div class="card shadow-sm border-0 mb-3">
            <!-- رأس البطاقة -->
            <div class="card-header py-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                    <div>
                        <h6>
                            {{ $employee->raw_name }}
                        </h6>
                        <span class="fs-6 me-1">الشهر: {{ $wps_payroll_details->wpsPayroll->run_date->format('m') }}</span>
                        <span class="fs-6">السنة: {{ $wps_payroll_details->wpsPayroll->run_date->format('Y') }}</span>
                    </div>
                    <div>
                        <a href="{{ route('hr.payrolls.wps.details.export_pdf', [$wps_payroll_details->wps_payroll_id, $wps_payroll_details->employee_id]) }}"
                            class="btn btn-sm btn-outline-secondary">
                            تصدير PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- محتوى البطاقة -->
        <div class="card-body">
            <!-- ملخص الأرقام الرئيسية -->
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="card h-100 border-start border-2">
                        <div class="card-body text-center py-3">
                            <div class="d-flex justify-content-center align-items-center mb-3">
                                <div class="bg-dark bg-opacity-25 rounded-circle p-3">
                                    <i class="ti ti-wallet text-white" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                            <h6 class="card-title fw-bold">الراتب الأساسي</h6>
                            <h6 class="card-text fw-bold mb-0">
                                {{ $wps_payroll_details->basic }}
                            </h6>
                            <span class="icon-saudi_riyal mx-2"></span>

                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100 border-start border-2">
                        <div class="card-body text-center py-3">
                            <div class="d-flex justify-content-center align-items-center mb-3">
                                <div class="bg-dark bg-opacity-25 rounded-circle p-3">
                                    <i class="ti ti-plus text-white" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                            <h6 class="card-title fw-bold">إجمالي البدلات</h6>
                            <h6 class="card-text fw-bold mb-0">
                                {{ number_format($wps_payroll_details->getRawOriginal('transport') + $wps_payroll_details->getRawOriginal('housing') + $wps_payroll_details->getRawOriginal('other'), 2) }}
                            </h6>
                            <span class="icon-saudi_riyal mx-2"></span>

                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100 border-start border-2">
                        <div class="card-body text-center py-3">
                            <div class="d-flex justify-content-center align-items-center mb-3">
                                <div class="bg-dark bg-opacity-25 rounded-circle p-3">
                                    <i class="ti ti-minus text-white" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                            <h6 class="card-title fw-bold">إجمالي الخصومات</h6>
                            <h6 class="card-text fw-bold mb-0">
                                {{ $wps_payroll_details->deductions }}
                            </h6>
                            <span class="icon-saudi_riyal mx-2"></span>

                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card h-100 border-start border-2">
                        <div class="card-body text-center py-3">
                            <div class="d-flex justify-content-center align-items-center mb-3">
                                <div class="bg-dark bg-opacity-25 rounded-circle p-3">
                                    <i class="ti ti-award text-white" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                            <h6 class="card-title fw-bold">إجمالي المكافأت و الحوافز</h6>
                            <h6 class="card-text fw-bold mb-0">
                                {{ $wps_payroll_details->incentives }}
                            </h6>
                            <span class="icon-saudi_riyal mx-2"></span>

                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">

                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header py-2">
                            <h5 class="fw-bold mb-0">تفاصيل البدلات</h5>
                        </div>
                        <div class="border-1 border-light border-dashed mb-2"></div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <strong>بدل النقل</strong>
                                <strong dir="ltr">
                                    <span class="icon-saudi_riyal mx-2"></span>
                                    {{ $wps_payroll_details->transport }}
                                </strong>


                            </div>

                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <strong>بدل السكن</strong>
                                <strong dir="ltr">
                                    <span class="icon-saudi_riyal mx-2"></span>
                                    {{ $wps_payroll_details->housing }}
                                </strong>
                            </div>

                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <strong>بدلات أخرى</strong>
                                <strong dir="ltr">
                                    <span class="icon-saudi_riyal mx-2"></span>
                                    {{ $wps_payroll_details->other }}
                                </strong>
                            </div>

                            <div class="d-flex justify-content-between py-2">
                                <strong>مجموع البدلات</strong>
                                <strong dir="ltr">
                                    <span class="icon-saudi_riyal mx-2"></span>
                                    {{ number_format($wps_payroll_details->getRawOriginal('transport') + $wps_payroll_details->getRawOriginal('housing') + $wps_payroll_details->getRawOriginal('other'), 2) }}
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header py-2">
                            <h5 class="fw-bold mb-0">تفاصيل إضافية </h5>
                        </div>
                        <div class="border-1 border-light border-dashed mb-2"></div>

                        <div class="card-body">
                            <!-- إجمالي الراتب (الأساسي + البدلات) -->
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <strong>إجمالي الراتب (الأساسي + البدلات)</strong>
                                <strong dir="ltr">
                                    <span class="icon-saudi_riyal mx-2"></span>
                                    {{ number_format($wps_payroll_details->getRawOriginal('basic') + $wps_payroll_details->getRawOriginal('transport') + $wps_payroll_details->getRawOriginal('housing') + $wps_payroll_details->getRawOriginal('other'), 2) }}
                                </strong>
                            </div>

                            <!-- إجمالي أيام العمل -->
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <strong> التأمين</strong>
                                <strong dir="ltr">
                                    <span class="icon-saudi_riyal mx-2"></span>
                                    {{ $wps_payroll_details->insurance }}
                                </strong>
                            </div>

                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <strong>الخصومات </strong>
                                <strong dir="ltr">
                                    <span class="icon-saudi_riyal mx-2"></span>
                                    {{ number_format($wps_payroll_details->getRawOriginal('deductions') - $wps_payroll_details->getRawOriginal('insurance'), 2) }}
                                </strong>
                            </div>


                            <div class="d-flex justify-content-between py-2 ">
                                <strong> المكافأت </strong>
                                <strong dir="ltr">
                                    <span class="icon-saudi_riyal mx-2"></span>
                                    {{ $wps_payroll_details->incentives }}
                                </strong>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            <!-- جدول الإجماليات -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <tbody>
                                        <tr>
                                            <th class="w-50">إجمالي المستحق (الراتب الأساسي + البدلات)</th>
                                            <td class="fw-bold">
                                                <span>
                                                    {{ number_format($wps_payroll_details->getRawOriginal('basic') + $wps_payroll_details->getRawOriginal('transport') + $wps_payroll_details->getRawOriginal('housing') + $wps_payroll_details->getRawOriginal('other'), 2) }}
                                                    <span class="icon-saudi_riyal mx-2"></span>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>إجمالي الخصومات</th>
                                            <td class="fw-bold">
                                                <span>
                                                    {{ $wps_payroll_details->deductions }}
                                                    <span class="icon-saudi_riyal mx-2"></span>
                                                </span>
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>إجمالي المكافأت</th>
                                            <td class="fw-bold">
                                                <span>
                                                    {{ $wps_payroll_details->incentives }}
                                                    <span class="icon-saudi_riyal mx-2"></span>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr class="table-active">
                                            <th class="fw-bold">صافي الراتب</th>
                                            <td class="fw-bold">
                                                <span>
                                                    {{ $wps_payroll_details->net }}
                                                    <span class="icon-saudi_riyal mx-2"></span>
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col">
                    <div class="card shadow-sm">
                        <div class="card-header py-2">
                            <p class="fw-bold mb-0">السلف المطبقة على الراتب </p>
                        </div>
                        <div class="border-1 border-light border-dashed mb-2"></div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <tbody>
                                        @forelse ($wps_payroll_details->wpsPayroll->advances($employee->id) as $advance)
                                            <tr>
                                                <th class="text-center">
                                                    <a href="{{ route('hr.advances.show', $advance->id) }}">
                                                        {{ $advance->advance_number }}
                                                    </a>
                                                </th>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center text-muted">لا يوجد</td>
                                            </tr>
                                        @endforelse

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card shadow-sm">
                        <div class="card-header py-2">
                            <p class="fw-bold mb-0">المكافأت المطبقة على الراتب </p>
                        </div>
                        <div class="border-1 border-light border-dashed mb-2"></div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <tbody>
                                        @forelse ($wps_payroll_details->wpsPayroll->rewards($employee->id) as $reward)
                                            <tr>
                                                <th class="text-center">
                                                    <a href="{{ route('hr.rewards.show', $reward->id) }}">
                                                        {{ $reward->reward_number }}
                                                    </a>
                                                </th>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center text-muted">لا يوجد</td>
                                            </tr>
                                        @endforelse

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card shadow-sm">
                        <div class="card-header py-2">
                            <p class="fw-bold mb-0">الخصومات المطبقة على الراتب </p>
                        </div>
                        <div class="border-1 border-light border-dashed mb-2"></div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <tbody>
                                        @forelse ($wps_payroll_details->wpsPayroll->deductions($employee->id) as $deduction)
                                            <tr>
                                                <th class="text-center">
                                                    <a href="{{ route('hr.deductions.show', $deduction->id) }}">
                                                        {{ $deduction->deduction_number }}
                                                    </a>
                                                </th>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center text-muted">لا يوجد</td>
                                            </tr>
                                        @endforelse

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div> <!-- نهاية محتوى البطاقة -->
    </div>
@endsection
