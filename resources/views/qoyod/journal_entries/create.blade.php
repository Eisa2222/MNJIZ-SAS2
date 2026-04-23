@extends('layouts.layoutMaster')

@section('title', 'إضافة قيد يومية')

@section('breadcrumb')
    <li><a href="#">قيود</a></li>
    <li><a href="{{ route('qoyod.journal-entries.index') }}">قيود اليومية</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#"> إضافة قيد يومية </a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة قيد يومية" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/css/components/icons.css', 'resources/assets/css/qoyod/journal-entries.css', 'resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])


@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/qoyod/journal_entries/journal-entries-request-wizard.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div id="wizard-validation" class="bs-stepper mt-2">
                <div class="bs-stepper-header">
                    <div class="step" data-target="#basic-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">1</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">المعلومات الأساسية</span>
                                <span class="bs-stepper-subtitle">تفاصيل قيد اليومية </span>
                            </span>
                        </button>
                    </div>
                </div>
                <div class="bs-stepper-content">
                    @if ($errors->any())
                        <div class="alert alert-danger mt-2">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form id="form" action="{{ route('qoyod.journal-entries.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div id="basic-info" class="content dstepper-block">
                            <div class="row g-3">

                                {{-- الوصف --}}
                                <div class="col-md-12">
                                    <label for="description" class="form-label">الوصف</label>
                                    <textarea id="description" name="description" rows="2" class="form-control">{{ old('description') }}</textarea>
                                </div>

                                <div class="col-md-6">
                                    <label for="date" class="form-label">التاريخ  <span
                                            class="text-danger">*</span></label>
                                    <input type="date" id="date" name="date" class="form-control" required>
                                </div>

                                {{-- الموقع (inventory_id) --}}
                                <div class="col-md-6">
                                    <label for="inventory_id" class="form-label">الموقع
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select id="inventory_id" name="inventory_id" class="form-select select2"
                                        data-placeholder="اختر الموقع">
                                        <option value=""></option>
                                        @foreach ($inventories as $inventory)
                                            <option value="{{ $inventory['id'] }}">{{ $inventory['ar_name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>


                                <div class="table-responsive-stack">
                                    <table id="linesTable" class="table table-bordered align-middle">
                                        <thead class="table-light text-center">
                                            <tr>
                                                <th style="min-width: 350px;">الحساب</th>
                                                <th style="min-width: 100px;">مدين</th>
                                                <th style="min-width: 100px;">دائن</th>
                                                <th style="min-width: 200px;">التعليقات</th>
                                                <th style="width:30px"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- صف افتراضي واحد نستنسخه --}}
                                            <tr class="line text-center">
                                                <td data-label="الحساب">
                                                    <select required name="journal_entry[lines][0][account_id]"
                                                        id="journal_entry[lines][0][account_id]"
                                                        class="form-select form-select-sm account-select select2" required>
                                                        <option value=""></option>
                                                        @foreach ($accounts as $account)
                                                            <option value="{{ $account['id'] }}">{{ $account['name_ar'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td data-label="مدين">
                                                    <input  type="number" name="journal_entry[lines][0][debit]"
                                                        class="form-control form-control-sm debit-input" step="0.01"
                                                        value="0.00">
                                                </td>
                                                <td data-label="دائن">
                                                    <input type="number" name="journal_entry[lines][0][credit]"
                                                        class="form-control form-control-sm credit-input" step="0.01"
                                                        value="0.00">
                                                </td>

                                                <td data-label="التعليقات">
                                                    <input type="text" name="journal_entry[lines][0][comment]"
                                                        class="form-control form-control-sm comment-input">
                                                </td>

                                                <td>
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-danger remove-line">&times;</button>
                                                </td>



                                            </tr>
                                        </tbody>
                                        <tfoot class="text-center">
                                            <tr>
                                                <td colspan="1" class="small">المجموع</td>
                                                <td><span class="small" id="total-debit">0.00</span> <span
                                                        class="icon-saudi_riyal"></span></td>
                                                <td><span class="small" id="total-credit">0.00</span> <span
                                                        class="icon-saudi_riyal"></span></td>

                                                <td colspan="2"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-start">
                                    <button type="button" id="addLine" class="btn btn-sm btn-primary">
                                        إضافة المزيد
                                    </button>
                                </div>

                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary btn-submit">حفظ</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
