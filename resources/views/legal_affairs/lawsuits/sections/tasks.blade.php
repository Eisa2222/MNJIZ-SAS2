<script>
    var currentUserId = "{{ Auth::user()->id }}";


    $(document).ready(function() {
        // تهيئة Select2 لحقول الفلترة
        $('.select2').select2({
            placeholder: function() {
                return $(this).data('placeholder');
            },
            allowClear: true,
            width: '100%',
            language: 'ar',
            dir: 'rtl',
            dropdownParent: $('body')
        });

        // تعريف متغير للتحقق من التحميل الأول
        var isFirstLoad = true;

        var table = $('#tasks-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                // url: "/lawsuits/{{ $lawsuit->id }}/tasks",
                url: "{{ url('lawsuits/' . $lawsuit->id . '/tasks') }}",

                data: function(d) {
                    d.priority = $('#filter-priority').val();
                    d.task_field = $('#filter-task-field').val();
                    d.status = $('#filter-status').val();
                }
            },
            language: {
                url: "{{ asset('assets/json/ar.json') }}"
            },
            columns: [{
                    data: 'complete_checkbox',
                    name: 'complete_checkbox',
                    orderable: false,
                    searchable: false,
                },
                {
                    className: 'dt-control',
                    orderable: false,
                    data: 'steps_data',
                    defaultContent: '',
                    render: function(data, type, row) {
                        if (data) {
                            return '<button class="btn btn-link p-0">خطوة</button>';
                        } else {
                            return '<p class=" p-0">مهمة</p>';
                        }
                    }
                },
                {
                    data: 'task_name',
                    name: 'task_name',
                },
                {
                    data: 'priority',
                    name: 'priority'
                },

                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'assigned_users',
                    name: 'assigned_users'
                },


                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    visible: function() {
                        return {!! auth()->user()->can('تعديل مهمة') || auth()->user()->can('حذف مهمة') ? 'true' : 'false' !!};
                    }()
                }
            ],

            dom: '<"d-flex justify-content-between align-items-center mb-3"' +
                '<"d-flex align-items-center gap-2"lf>' +
                // Length changing and search box together on the right
                '<"d-flex align-items-center"B>' + // Buttons (New Task) on the left

                '>' +
                'rt' +
                '<"d-flex justify-content-between flex-wrap mt-3"' +
                '<"d-flex"i>' + // Info about showing X of Y entries
                '<"d-flex"p>' + // Pagination
                '>',
            buttons: [

                @can('إضافة مهمة')
                    {
                        "data-bs-toggle": "offcanvas",
                        "data-bs-target": "#addTaskOffcanvas",
                        text: '<i class="fas fa-plus-circle me-1"></i> مهمة جديدة',
                        className: 'btn btn-primary btn-add',
                        action: function(e, dt, node, config) {
                            // استخدام offcanvas API من Bootstrap لعرض النموذج
                            var offcanvasElement = document.getElementById('addTaskOffcanvas');
                            var bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
                            if (!bsOffcanvas) {
                                bsOffcanvas = new bootstrap.Offcanvas(offcanvasElement);
                            }
                            bsOffcanvas.show();
                        }
                    },
                @endcan
            ],
            order: [
                [1, 'asc']
            ],

            // drawCallback: function(settings) {
            //     // فتح جميع الصفوف فقط عند التحميل الأول
            //     if (isFirstLoad) {
            //         var api = this.api();
            //         api.rows().every(function() {
            //             var tr = $(this.node());
            //             var row = this;
            //             if (row.data().steps_data) {
            //                 row.child(row.data().steps_data).show();
            //                 tr.addClass('shown');
            //                 tr.find('td.dt-control i')
            //                     .removeClass('ti-chevron-right')
            //                     .addClass('ti-chevron-down');
            //             }
            //         });
            //         isFirstLoad = false;
            //     }
            // },



            // إضافة معالجة الصفوف المفتوحة
            rowCallback: function(row, data) {
                $(row).attr('data-steps', data.steps_data ? 'true' : 'false');
            }
        });

        function openAllDetailRows(tableSelector) {
            var table = $(tableSelector).DataTable();

            table.rows().every(function() {
                var tr = $(this.node());
                var row = this;
                var data = row.data();

                // التحقق من وجود البيانات والخطوات بشكل أكثر تفصيلاً
                if (data && data.steps_data) {
                    // التحقق مما إذا كان الصف مغلقًا
                    if (!tr.hasClass('shown')) {
                        // فتح الصف التفصيلي
                        row.child(data.steps_data).show();

                        // إضافة class للصف المفتوح
                        tr.addClass('shown');

                        // تغيير زر التوسيع
                        tr.find('td.dt-control button')
                            .text('خطوات');
                    }
                }
            });
        }

        // اكمال المهمة
        $('#tasks-table tbody').on('change', '.task-complete-checkbox', function() {
            const $checkbox = $(this);
            const taskId = $checkbox.data('task-id');
            const isChecked = $checkbox.is(':checked');

            $.ajax({
                url: `/tasks/${taskId}/toggle-completion`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    status: isChecked ? 'completed' : 'in_progress'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#tasks-table').DataTable().draw(true);

                    } else {
                        toastr.error(response.message);
                        // إعادة تعيين حالة الـ checkbox بناءً على النتيجة
                        $checkbox.prop('checked', !isChecked);
                    }
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error("حدث خطأ أثناء تحديث الحالة.");
                    }
                    // إعادة تعيين حالة الـ checkbox عند حدوث خطأ
                    $checkbox.prop('checked', !isChecked);
                }
            });
        });



        // اكمال الخطوة
        $('#tasks-table tbody').on('change', '.step-complete-checkbox', function() {

            var checkbox = $(this);
            var stepId = checkbox.data("step-id");
            var isChecked = checkbox.is(":checked");
            var $stepRow = checkbox.closest('tr');


            $.ajax({
                url: "/tasks/steps/" + stepId + "/toggle-completion",
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    isChecked: isChecked,
                },
                beforeSend: function() {
                    checkbox.prop("disabled", true);
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        const newStatus = isChecked ?
                            '<span class="badge bg-success">مكتملة</span>' :
                            '<span class="badge bg-primary"> قيد التنفيذ	</span>';
                        $stepRow.find('td:nth-child(4)').html(newStatus);
                        // تحديث التواريخ والمدة
                        $stepRow.find('td:nth-child(5)').html(
                            `<small>${response.step_start_date}</small>`);
                        $stepRow.find('td:nth-child(6)').html(
                            `<small>${response.step_end_date}</small>`);
                        $stepRow.find('td:nth-child(7)').html(
                            `<small>${response.duration}</small>`);

                    } else {
                        toastr.error(response.message);
                        checkbox.prop("checked", !isChecked);
                    }
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error("حدث خطأ أثناء تحديث حالة الخطوة.");
                    }
                    checkbox.prop("checked", !isChecked);
                },
                complete: function() {
                    checkbox.prop("disabled", false);
                }
            });
        });


        // دالة اعتماد/رفض الخطوة
        // Step approval/rejection click handler
        $('#tasks-table tbody').on('click', '.step-approval-btn', function() {
            var btn = $(this);
            var action = btn.data("action");
            var stepId = btn.data("step-id");
            var $stepRow = btn.closest('tr');

            if (action === "reject") {
                // Store the button reference in the modal's data
                $("#rejectReasonModal")
                    .data("step-id", stepId)
                    .data("step-row", $stepRow)
                    .data("button", btn);
                var modal = new bootstrap.Modal(document.getElementById("rejectReasonModal"));
                modal.show();
            } else {
                processStepApproval(stepId, action, null, $stepRow, btn);
            }
        });

        // Confirm reject button handler
        $('#confirmRejectBtn').off('click').on('click', function() {
            var modalEl = document.getElementById("rejectReasonModal");
            var modal = bootstrap.Modal.getInstance(modalEl);
            var modalObj = $("#rejectReasonModal");
            var stepId = modalObj.data("step-id");
            var $stepRow = modalObj.data("step-row");
            var btn = modalObj.data("button");
            var rejectReason = $("#rejectReasonText").val();

            if ($.trim(rejectReason) === "") {
                toastr.warning("يرجى إدخال سبب الرفض.");
                return;
            }

            // Disable the button while processing
            $(this).prop('disabled', true);

            processStepApproval(stepId, "reject", rejectReason, $stepRow, btn);
            modal.hide();
            $("#rejectReasonText").val("");

            // Re-enable the button
            $(this).prop('disabled', false);
        });

        function processStepApproval(stepId, action, rejectReason, $stepRow, btn) {
            var data = {
                action: action
            };

            if (rejectReason) {
                data.reject_reason = rejectReason;
            }

            // Disable the relevant buttons
            if (btn) {
                btn.prop("disabled", true);
            }
            $stepRow.find('.step-approval-btn').prop("disabled", true);

            $.ajax({
                url: "/tasks/steps/" + stepId + "/toggle-approval",
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: data,
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);

                        // Update step status
                        const newStatus = action === "approve" ?
                            '<span class="badge bg-success">معتمدة</span>' :
                            '<span class="badge bg-danger">مرفوضة</span>';
                        $stepRow.find('td:nth-child(4)').html(newStatus);

                        // تحديث التواريخ والمدة
                        $stepRow.find('td:nth-child(5)').html(
                            `<small>${response.step_start_date}</small>`);
                        $stepRow.find('td:nth-child(6)').html(
                            `<small>${response.step_end_date}</small>`);
                        $stepRow.find('td:nth-child(7)').html(
                            `<small>${response.duration}</small>`);

                        // Update approval buttons
                        $stepRow.find('.step-approval-btn[data-action="approve"]')
                            .prop('disabled', action === "approve");
                        $stepRow.find('.step-approval-btn[data-action="reject"]')
                            .prop('disabled', action === "reject");

                        // Update status text if it exists
                        var $statusText = $stepRow.find('.step-status');
                        if ($statusText.length) {
                            $statusText.text(action === "approve" ? "معتمدة" : "مرفوضة");
                        }
                    } else {
                        toastr.error(response.message);
                        // Re-enable buttons on error
                        $stepRow.find('.step-approval-btn').prop("disabled", false);
                    }
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error("حدث خطأ أثناء تحديث حالة اعتماد الخطوة.");
                    }
                    // Re-enable buttons on error
                    $stepRow.find('.step-approval-btn').prop("disabled", false);
                }
            });
        }




        // معالج حدث النقر على زر التوسيع/الطي
        $('#tasks-table tbody').on('click', 'td.dt-control', function(e) {
            e.stopPropagation();
            var tr = $(this).closest('tr');
            var row = table.row(tr);
            var icon = $(this).find('i');

            if (row.child.isShown()) {
                // إغلاق الصف
                row.child.hide();
                tr.removeClass('shown');
                icon.removeClass('ti-chevron-down').addClass('ti-chevron-right');
            } else {
                // فتح الصف إذا كان يحتوي على خطوات
                if (row.data().steps_data) {
                    row.child(row.data().steps_data).show();
                    tr.addClass('shown');
                    icon.removeClass('ti-chevron-right').addClass('ti-chevron-down');
                }
            }
        });

        // معالجة إعادة تحميل الجدول عند تغيير الفلاتر
        $('#filter-priority, #filter-task-field, #filter-status').change(function() {
            isFirstLoad = false; // تأكد من عدم فتح الصفوف تلقائياً عند تصفية البيانات
            table.draw(false);
        });

        // معالجة إعادة تحميل الجدول
        table.on('draw', function() {
            // إعادة تطبيق حالة الإغلاق/الفتح للصفوف
            table.rows().every(function() {
                var tr = $(this.node());
                var icon = tr.find('td.dt-control i');

                if (tr.hasClass('shown')) {
                    // إذا كان الصف مفتوحاً، أعد فتحه
                    var row = this;
                    if (row.data().steps_data) {
                        row.child(row.data().steps_data).show();
                        icon.removeClass('ti-chevron-right').addClass('ti-chevron-down');
                    }
                } else {
                    // تأكد من أن الأيقونة في الحالة الصحيحة
                    icon.removeClass('ti-chevron-down').addClass('ti-chevron-right');
                }
            });
        });


        // عند النقر على زر "عرض الخطوات"
        $('#tasks-table tbody').on('click', '.toggle-steps', function() {
            let button = $(this);
            let rowId = button.data('id'); // رقم المهمة
            let tr = button.closest('tr'); // الصف الحالي
            let row = table.row(tr); // مرجع للصف في DataTables

            // إذا كانت الخطوات معروضة بالفعل
            if (row.child.isShown()) {
                row.child.hide(); // إخفاء الخطوات
                tr.removeClass('shown');
                button.text('عرض الخطوات'); // إعادة نص الزر
            } else {
                // في حال عدم العرض سابقًا، نقوم بجلب الخطوات من السيرفر وعرضها
                $.ajax({
                    url: '/tasks/' + rowId + '/steps', // تأكد من وجود راوت مناسب
                    method: 'GET',
                    success: function(response) {
                        // ضع محتوى الخطوات في child row
                        row.child(response).show();
                        tr.addClass('shown');
                        button.text('إخفاء الخطوات'); // تغيير نص الزر
                    },
                    error: function() {
                        toastr.error('حدث خطأ أثناء جلب خطوات المهمة.');
                    }
                });
            }
        });

        // تحديث الجدول عند تغيير الفلاتر
        $('#filter-priority, #filter-task-field, #filter-status').change(function() {
            table.draw();
        });
        $(document).on('click', '.edit-task-link', function(e) {
            // قراءة اسم المهمة من data attribute
            let taskName = $(this).data('task-name');
            // جعل الحقل readonly في حالة المهام المسبقة التعريف (اختياري)
            $('#task_name').prop('readonly', true);
            // عرض رسالة توضيحية في حال عدم السماح بتعديل الاسم
            $('#taskNameHint').text("هذه المهمة مُسبقة التعريف ولا يمكنك تعديل اسمها");
            // نقل الاسم إلى حقل عنوان المهمة
            $('#task_name').val(taskName);
        });


        $('#addTaskOffcanvas').on('hidden.bs.offcanvas', function() {
            $('#task_name').val('').prop('readonly', false);
            $('#taskNameHint').text("");
        });

    });
</script>


<div class=" ">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">

            <div class="d-flex flex-wrap gap-4 mb-5">
                <!-- المهام المسبقة التعريف -->
                @foreach ($predefinedTasks as $task)
                    @php
                        $isAssigned = isset($assignedTasksByName[$task]);
                        $taskModel = $isAssigned ? $assignedTasksByName[$task] : null;
                    @endphp
                    <div class="col">
                        <div class="card shadow-sm border-0 task-card position-relative {{ $isAssigned ? 'disabled' : '' }}"
                            data-task-name="{{ $task }}" data-task-id="{{ $taskModel->id ?? '' }}"
                            data-predefined="1">
                            @if ($isAssigned)
                                <a href="{{ route('organization-center.tasks.show', $taskModel->id) }}"
                                    class="p-5 d-flex align-items-center  fw-bold fs-6">

                                    <div class="d-flex">
                                        <i class="ti ti-checklist text-body me-1"></i>
                                        <span class="fs-7 nowrap "
                                            style="white-space: nowrap; overflow: hidden; ">{{ $task }}</span>


                                        <div class="text-success m-auto px-2">
                                            <i class="fas fa-check-circle"
                                                title="تم اسناد هذه المهمة من قبل اضغط هنا لعرض التفاصيل"></i>
                                        </div>
                                    </div>
                                </a>
                            @else
                                <a href="#" class="p-5 d-flex align-items-center edit-task-link  fw-bold fs-6"
                                    data-task-name="{{ $task }}" data-bs-toggle="offcanvas"
                                    data-bs-target="#addTaskOffcanvas" data-predefined="1">

                                    <div class="d-flex">
                                        <i class="ti ti-checklist text-body me-1"></i>
                                        <span class="fs-7 nowrap"
                                            style="white-space: nowrap; overflow: hidden;">{{ $task }}</span>

                                        <div class="text-danger m-auto px-2">
                                            <i class="fas fa-check-circle"
                                                title="لم يتم اسناد هذا المهمة بعد اضغط هنا لاضافتها لمهام المشروع"></i>
                                        </div>
                                    </div>
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <table id="tasks-table" class="datatables-basic table table-bordered table-responsive small">
                <thead>
                    <tr>
                        <th>
                        </th>
                        <th>النوع</th>
                        <th>المهمة</th>
                        <th>الأولوية</th>
                        <th>الحالة</th>
                        <th>المكلفين</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    {{-- لاضافة مهمة جديدة  --}}
    @include('legal_affairs.lawsuits.sections.partials._add_task')

</div>
