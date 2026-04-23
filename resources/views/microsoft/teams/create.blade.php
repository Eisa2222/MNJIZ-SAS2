@extends('layouts.layoutMaster')

@section('title', 'إضافة إجتماع جديد')

@section('breadcrumb')
    <li><a href="{{ route('microsoft.teams.index') }}"> الاجتماعات</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">إضافة إجتماع جديد </a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة إجتماع جديد" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/typeahead-js/typeahead.scss', 'resources/assets/vendor/libs/tagify/tagify.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])

    <style>
        .was-validated .form-control:invalid,
        .form-control.is-invalid {
            border-color: #d1d0d4 !important;
            border-width: 2px;
        }

        .hide-elements {
            display: none;
        }
    </style>
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/typeahead-js/typeahead.js', 'resources/assets/vendor/libs/tagify/tagify.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])


@endsection
@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

<style>
    /* اللون الافتراضي للأزرار */

    /* اللون عند التفعيل */
    .active-button {
        background-color: #986e27 !important;
        /* لون أخضر عند التفعيل */
        color: white !important;
        border: 1px solid #986e27 !important;
    }
</style>
<!-- Page Scripts -->
@section('page-script')
    @vite(['resources/assets/js/form-validation.js'])

    <script>
        $(document).ready(function() {
            // تهيئة الحقل ليكون متعدد الخيارات
            $('.select2').select2({
                placeholder: function() {
                    return $(this).data('placeholder');
                },
                allowClear: true,
                width: '100%', // تأكد من ضبط العرض ليكون كاملاً
                language: 'ar', // دعم اللغة العربية
                dir: 'rtl', // دعم الاتجاه من اليمين إلى اليسار
                tags: true // السماح بإضافة قيم جديدة (اختياري)
            });
        });
    </script>
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-6 p-7">
        <div class="card-body pt-4">
            <form id="meeting-form" class="needs-validation" novalidate action="{{ route('teams.store') }}" method="POST">
                @csrf
                <div class="row">

                    <!-- موضوع الاجتماع -->
                    <div class="col-md-6 mb-4">
                        <label for="subject" class="form-label">موضوع الاجتماع</label>
                        <input type="text" id="subject" name="subject" class="form-control" required />
                        <div class="invalid-feedback">يرجى إدخال موضوع الاجتماع.</div>
                    </div>

                    <!-- وقت بدء الاجتماع -->
                    <div class="col-md-6 mb-4">
                        <label for="start_time" class="form-label">وقت بدء الاجتماع</label>
                        <input type="datetime-local" id="start_time" name="start_time" class="form-control" required />
                        <div class="invalid-feedback">يرجى اختيار وقت بدء الاجتماع .</div>
                    </div>

                    <!-- وقت انتهاء الاجتماع -->
                    <div class="col-md-6 mb-4">
                        <label for="end_time" class="form-label">وقت انتهاء الاجتماع</label>
                        <input type="datetime-local" id="end_time" name="end_time" class="form-control" required />
                        <div class="invalid-feedback">يرجى اختيار وقت انتهاء الاجتماع بعد وقت البدء.</div>
                    </div>

                    <div class="col-md-6 mb-4 ">
                        <label for="meeting_participants" class="form-label">أطراف الإجتماع</label>
                        <select id="meeting_participants" class="form-control select2" multiple="multiple"
                            data-placeholder="اختر أطراف الإجتماع">
                            <option value="employees">الموظفين</option>
                            <option value="customers">العملاء</option>
                            <option value="opponents">الخصوم</option>
                            <option value="additional">بريد آخر</option>
                        </select>
                        <div class="invalid-feedback">يرجى اختيار أطراف المشروع</div>
                    </div>


                    <!-- حقل الموظفين -->
                    <div class="col-md-6 mb-4 hide-elements" id="field-employees">
                        <label for="attendees_employee" class="form-label">اختر الموظفين</label>
                        <select id="attendees_employee" name="attendees_employee[]" class="form-control select2"
                            data-placeholder="اختر الموظفين" multiple="multiple">
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->work_email }}">{{ $employee->name }}
                                    ({{ $employee->work_email }})
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">يرجى اختيار بريد إلكتروني صالح.</div>
                    </div>

                    <!-- حقل العملاء -->
                    <div class="col-md-6 mb-4 hide-elements" id="field-customers">
                        <label for="attendees_customer" class="form-label">اختر العملاء</label>
                        <select id="attendees_customer" name="attendees_customer[]" class="form-control select2"
                            data-placeholder="اختر العملاء" multiple="multiple">
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->email }}">{{ $customer->name }} ({{ $customer->email }})
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">يرجى اختيار بريد إلكتروني صالح.</div>
                    </div>

                    <!-- حقل الخصوم -->
                    <div class="col-md-6 mb-4 hide-elements" id="field-opponents">
                        <label for="attendees_opponent" class="form-label">اختر الخصوم</label>
                        <select id="attendees_opponent" name="attendees_opponent[]" class="form-control select2"
                            data-placeholder="اختر الخصوم" multiple="multiple">
                            @foreach ($opponent as $opponent)
                                <option value="{{ $opponent->email }}">{{ $opponent->name }} ({{ $opponent->email }})
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">يرجى اختيار بريد إلكتروني صالح.</div>
                    </div>

                    <!-- حقل بريد آخر -->
                    <div class="col-md-6 mb-4 hide-elements" id="field-additional">
                        <label for="additional_emails" class="form-label">إضافة بريد إلكتروني آخر</label>
                        <select id="additional_emails" name="additional_emails[]" class="form-control select2"
                            multiple="multiple" data-placeholder="أدخل بريد إلكتروني"></select>
                        <div class="invalid-feedback">يرجى إدخال بريد إلكتروني صالح.</div>
                    </div>



                    <div class="col-md-6 mb-4">
                        <label for="meeting_field" class="form-label">مجال الاجتماع</label>
                        <select id="meeting_field" name="meeting_field" class="form-select select2" required
                            data-placeholder="اختر مجال الاجتماع">
                            <option></option>
                            <option value="public" {{ old('meeting_field') == 'public' ? 'selected' : '' }}>عام</option>

                            <option value="projects" {{ old('meeting_field') == 'projects' ? 'selected' : '' }}>المشاريع
                            </option>

                        </select>
                        <div class="invalid-feedback">يرجى تحديد مجال الاجتماع</div>
                    </div>

                    <!-- المشاريع -->
                    <div class="col-md-6 mb-4 hide-elements" id="projects_container">
                        <label for="project_id" class="form-label">اختر المشروع</label>
                        <select id="project_id" name="project_id" class="form-select select2"
                            data-placeholder="اختر المشروع">
                            <option></option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}"
                                    {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                    {{ $project->project_name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">يرجى اختيار المشروع</div>
                    </div>

                    <!-- الدعاوى -->
                    <div class="col-md-6 mb-4 hide-elements" id="lawsuits_container">
                        <label for="lawsuits_id" class="form-label">اختر الدعوى</label>
                        <select id="lawsuits_id" name="lawsuits_id" class="form-select select2"
                            data-placeholder="اختر الدعوى">
                            <option></option>
                        </select>
                        <div class="invalid-feedback">يرجى اختيار الدعوى</div>
                    </div>



                    <!-- نقاط الاجتماع -->
                    <div class="col-md-12 mb-4">
                        <label for="meeting_points" class="form-label">نقاط الاجتماع</label>
                        <textarea id="meeting_points" name="meeting_points" class="form-control" rows="4">{{ old('meeting_points') }}</textarea>
                        <div class="invalid-feedback">يرجى إدخال نقاط الاجتماع.</div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">إنشاء الاجتماع</button>
                </div>
            </form>
        </div>
    </div>




    <script>
        $(document).ready(function() {
            // دالة عامة لتفعيل Select2
            function initializeSelect2(selector, options = {}) {
                $(selector).select2({
                    placeholder: options.placeholder || "اختر قيمة",
                    allowClear: true,
                    width: "100%",
                    language: "ar",
                    dir: "rtl",
                    ...options,
                });
            }


            // دالة لإظهار أو إخفاء الحقول
            function toggleField(selector, show) {
                if (show) {
                    $(selector).removeClass("hide-elements");
                } else {
                    $(selector).addClass("hide-elements");
                    $(selector).find("select").val(null).trigger("change");
                }
            }

            // دالة لجلب البيانات وتحديث الحقول
            function fetchAndUpdateField(url, targetField, emptyMessage = "لا توجد بيانات") {
                $(targetField).empty().append('<option></option>').trigger("change");
                $.ajax({
                    url: url,
                    type: "GET",
                    success: function(response) {
                        if (response.length > 0) {
                            response.forEach(function(item) {
                                $(targetField).append(
                                    `<option value="${item.id}">${item.name}</option>`
                                );
                            });
                            $(targetField).trigger("change");
                        } else {
                            toastr.info(emptyMessage, "معلومة");
                        }
                    },
                    error: function() {
                        toastr.error("حدث خطأ أثناء جلب البيانات. يرجى المحاولة مرة أخرى.");
                    },
                });
            }

            // تهيئة Select2 لجميع الحقول
            initializeSelect2(".select2");

            // التحكم في عرض حقول المشاريع والدعاوى بناءً على مجال الاجتماع
            $("#meeting_field").change(function() {
                const selectedField = $(this).val();
                toggleField("#projects_container", selectedField === "projects");
                toggleField("#lawsuits_container", false); // إخفاء الدعاوى دائمًا عند تغيير المجال
            });

            // جلب الدعاوى عند اختيار المشروع
            $("#project_id").change(function() {
                const projectId = $(this).val();
                if (projectId) {
                    toggleField("#lawsuits_container", true);
                    fetchAndUpdateField(
                        `/projects/${projectId}/lawsuits/json`,
                        "#lawsuits_id",
                        "لا توجد دعاوى مرتبطة بهذا المشروع."
                    );
                } else {
                    toggleField("#lawsuits_container", false);
                }
            });

            // تهيئة الحقول الديناميكية للمشاركين
            var fields = {
                employees: '#field-employees',
                customers: '#field-customers',
                opponents: '#field-opponents',
                additional: '#field-additional',
            };

            $('#meeting_participants').on('change', function() {
                // إعادة تعيين جميع الحقول: إخفاؤها وإفراغها
                Object.values(fields).forEach(function(fieldSelector) {
                    if (!$('#meeting_participants').val().includes(fieldSelector.replace('#field-',
                            ''))) {
                        $(fieldSelector).hide();
                        $(fieldSelector).find('select').removeAttr('required').val([]).trigger(
                            'change'); // استخدام val([]) لإفراغ الحقول
                    }
                });

                // إظهار الحقول المختارة فقط
                $(this).val().forEach(function(value) {
                    var field = fields[value];
                    if (field) {
                        $(field).show();
                        $(field).find('select').attr('required', 'required');
                    }
                });
            });

            const now = new Date().toISOString().slice(0, 16);
            $('#start_time').attr('min', now);

            // تحديث الحد الأدنى لوقت الانتهاء بناءً على وقت البدء
            $('#start_time').on('change', function() {
                const startTime = $(this).val();
                if (startTime) {
                    $('#end_time').attr('min', startTime); // تعيين الحد الأدنى لوقت الانتهاء
                } else {
                    $('#end_time').removeAttr('min'); // إذا لم يتم اختيار وقت البدء، إزالة القيد
                }
            });

            // تحقق عند الإرسال
            $('#meeting-form').on('submit', function(e) {
                var isValid = true;


                // التحقق من وقت البدء
                const startTime = $('#start_time').val();
                const now = new Date().toISOString().slice(0, 16);
                if (!startTime || startTime < now) {
                    isValid = false;
                    $('#start_time').addClass('is-invalid');
                    $('#start_time').closest('.form-group').find('.invalid-feedback')
                        .text('يرجى اختيار وقت بدء الاجتماع بعد الوقت الحالي.');
                } else {
                    $('#start_time').removeClass('is-invalid');
                }

                // التحقق من وقت الانتهاء
                const endTime = $('#end_time').val();
                if (!endTime || endTime <= startTime) {
                    isValid = false;
                    $('#end_time').addClass('is-invalid');
                    $('#end_time').closest('.form-group').find('.invalid-feedback')
                        .text('يرجى اختيار وقت انتهاء الاجتماع بعد وقت البدء.');
                } else {
                    $('#end_time').removeClass('is-invalid');
                }
                // التحقق من حقل أطراف الاجتماع
                if (!$('#meeting_participants').val() || $('#meeting_participants').val().length === 0) {
                    isValid = false;
                    $('#meeting_participants').addClass('is-invalid');
                    $('#meeting_participants').closest('.form-group').find('.invalid-feedback')
                        .text('يرجى اختيار أطراف الاجتماع.');
                } else {
                    $('#meeting_participants').removeClass('is-invalid');
                }


                // التحقق من "اختر المشروع" إذا كان مجال الاجتماع هو "مشاريع"
                if ($('#meeting_field').val() === 'projects') {
                    if (!$('#project_id').val() || $('#project_id').val().length === 0) {
                        isValid = false;
                        $('#project_id').addClass('is-invalid');
                        $('#project_id').closest('.form-group').find('.invalid-feedback')
                            .text('يرجى اختيار المشروع.');
                    } else {
                        $('#project_id').removeClass('is-invalid');
                    }
                }



                // التحقق من الحقول المرئية
                $('.hide-elements:visible').each(function() {
                    var field = $(this).find('select');
                    if (!field.val() || field.val().length === 0) {
                        isValid = false;
                        field.addClass('is-invalid');
                        field.closest('.hide-elements').find('.invalid-feedback').text(
                            'يرجى إدخال قيمة في هذا الحقل.');
                    } else {
                        field.removeClass('is-invalid');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    toastr.error('يرجى ملء الحقول الإلزامية.', 'خطأ');
                }
            });

            // للتحقق من البريد الإلكتروني الإضافي
            $('#additional_emails').select2({
                placeholder: "أدخل بريد إلكتروني جديد أو اختر من الموجود",
                tags: true, // تمكين إدخال القيم اليدوية
                tokenSeparators: [',', ' '], // السماح بفصل القيم باستخدام الفاصلة أو المسافة
                allowClear: true, // السماح بإزالة القيم
                width: '100%',
                language: 'ar',
                dir: 'rtl',
                createTag: function(params) {
                    // التعبير المنتظم للتحقق من البريد الإلكتروني
                    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                    // التحقق من صحة القيمة المدخلة
                    if (!emailPattern.test(params.term)) {
                        // إظهار رسالة خطأ إذا كانت القيمة غير صالحة
                        toastr.error('يرجى إدخال بريد إلكتروني صالح.', 'خطأ');
                        return null; // منع إضافة القيمة غير الصالحة
                    }

                    // السماح بإضافة القيمة الصالحة
                    return {
                        id: params.term,
                        text: params.term
                    };
                },
                // وظيفة للبحث في القيم المدخلة (إذا أردت تعطيل الإضافة اليدوية فقط)
                insertTag: function(data, tag) {
                    // إضافة القيم إلى الحقل إذا كانت جديدة وصحيحة
                    if (data.indexOf(tag) === -1) {
                        data.push(tag);
                    }
                }
            });

            // منع إضافة القيم غير الصحيحة باستخدام Event
            $('#additional_emails').on('select2:select', function(e) {
                var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                var selectedEmail = e.params.data.text;

                if (!emailPattern.test(selectedEmail)) {
                    // إذا كانت القيمة غير صحيحة، قم بإزالتها
                    var $select = $(this);
                    var values = $select.val(); // جميع القيم الحالية
                    values = values.filter(function(value) {
                        return value !== selectedEmail; // إزالة القيمة غير الصحيحة
                    });

                    $select.val(values).trigger('change'); // تحديث القيم
                    toastr.error('يرجى إدخال بريد إلكتروني صالح.', 'خطأ'); // رسالة خطأ
                }
            });
        });
    </script>


@endsection
