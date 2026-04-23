/**
 * Page User List
 */

"use strict";

// Datatable (jquery)
$(function () {

    let borderColor, bodyBg, headingColor ,route ="settings-departments-contracts";
    var fields = table.data('fields');
    
    if (isDarkStyle) {
        borderColor = config.colors_dark.borderColor;
        bodyBg = config.colors_dark.bodyBg;
        headingColor = config.colors_dark.headingColor;
    } else {
        borderColor = config.colors.borderColor;
        bodyBg = config.colors.bodyBg;
        headingColor = config.colors.headingColor;
    }

    // Variable declaration for table
    var dt_user_table = $(".datatables-users"),
        select2 = $(".select2"),
        userView = baseUrl + "app/user/view/account";
    if (select2.length) {
        var $this = select2;
        $this.wrap('<div class="position-relative"></div>').select2({
            placeholder: "Select Country",
            dropdownParent: $this.parent(),
        });
    }

    // Users datatable
    if (dt_user_table.length) {
        var dt_user = dt_user_table.DataTable({
            ajax: "settings-departments-contracts", // تأكد من أن هذا المسار يعيد created_at
            columns: [
                { data: "id" }, // عمود التحكم (Control)
                { data: "id" }, // عمود الاختيار (Checkboxes)
                { data: "name" }, // عمود الاسم
                { data: "created_at" }, // عمود تاريخ الإنشاء
                { data: "action" }, // عمود الإجراءات
            ],
            columnDefs: [
                {
                    // عمود التحكم
                    className: "control",
                    searchable: true,
                    orderable: true,
                    responsivePriority: 2,
                    targets: 0,
                    render: function (data, type, full, meta) {
                        return "";
                    },
                },
                {
                    // عمود الاختيار
                    targets: 1,
                    orderable: false,
                    checkboxes: {
                        selectAllRender:
                            '<input type="checkbox" class="form-check-input">',
                    },
                    render: function () {
                        return '<input type="checkbox" class="dt-checkboxes form-check-input" >';
                    },
                    searchable: false,
                },
                {
                    // عمود الاسم
                    targets: 2,
                    render: function (data, type, full, meta) {
                        return '<span class="text-heading">' + data + "</span>";
                    },
                },
                {
                    // عمود تاريخ الإنشاء
                    targets: 3,
                    title: "تاريخ الإضافة",
                    render: function (data, type, full, meta) {
                        return moment(data).format("YYYY-MM-DD"); // تنسيق التاريخ حسب الحاجة
                    },
                },
                {
                    // عمود الإجراءات
                    // عمود الإجراءات
                    targets: -1,
                    title: "الإجراء",
                    searchable: false,
                    orderable: false,
                    render: function (data, type, full, meta) {
                         return (
                            '<div class="d-flex align-items-center">' +
                            // زر الحذف
                            '<a onclick="confirmDelete(' + full["id"] + ')" href="javascript:;" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill delete-record">' +
                            '<i class="ti ti-trash ti-md"></i></a>' +
                            // زر التعديل
                            '<a href="#" class="btn btn-sm text-secondary" data-bs-toggle="modal" data-bs-target="#editPermissionModal-' + full["id"] + '"><i class="ti ti-edit"></i></a>' +
                            '</div>' +
                            // نموذج الحذف مع رمز CSRF
                            '<form id="delete-form-' + full.id + '" action="' + baseRoute + '/' + full.id + '" method="POST" style="display: none;">' +
                            '<input type="hidden" name="_token" value="' + csrfToken + '">' +
                            '<input type="hidden" name="_method" value="DELETE">' +
                            '</form>' +
                            // مودال التعديل
                            '<div class="modal fade" id="editPermissionModal-' + full.id + '" tabindex="-1" aria-hidden="true">' +
                            '  <div class="modal-dialog modal-dialog-centered modal-simple">' +
                            '    <div class="modal-content">' +
                            '      <div class="modal-body">' +
                            '        <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>' +
                            '        <div class="text-center mb-6">' +
                            '          <h4 class="mb-2">تعديل بيانات حالة الموارد البشرية</h4>' +
                            '          <p>حالات الموارد البشرية</p>' +
                            '        </div>' +
                            '        <form id="editSettingForm-' + full.id + '" class="needs-validation row" action="' + baseRoute + '/' + full.id + '" method="POST">' +
                            '          <input type="hidden" name="_token" value="' + csrfToken + '">' +
                            '          <input type="hidden" name="_method" value="PUT">' +
                            '          <div class="col-12 mb-4">' +
                            '            <label class="form-label" for="modalSettingNameEdit-' + full.id + '">اسم حالة الموارد البشرية</label>' +
                            '            <input type="text" id="modalSettingNameEdit-' + full.id + '" name="name" class="form-control" value="' + full.name + '" placeholder="اسم حالة الموارد البشرية" required />' +
                            '            <div class="valid-feedback"></div>' +
                            '            <div class="invalid-feedback">اسم حالة الموارد البشرية مطلوبة</div>' +
                            '          </div>' +
                            '          <div class="col-12 text-center demo-vertical-spacing">' +
                            '            <button type="submit" class="btn btn-primary me-4">تحديث</button>' +
                            '            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">إلغاء</button>' +
                            '          </div>' +
                            '        </form>' +
                            '      </div>' +
                            '    </div>' +
                            '  </div>' +
                            '</div>'
                        );


                    },
                },
            ],
            order: [
                [3, "desc"], // ترتيب بناءً على تاريخ الإنشاء بشكل تنازلي
            ],
            dom:
                '<"row"' +
                '<"col-md-2"<"ms-n2"l>>' +
                '<"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-6 mb-md-0 mt-n6 mt-md-0"fB>>' +
                ">t" +
                '<"row"' +
                '<"col-sm-12 col-md-6"i>' +
                '<"col-sm-12 col-md-6"p>' +
                ">",
            language: {
                sLengthMenu: "_MENU_",
                search: "",
                searchPlaceholder: "البحث",
                paginate: {
                    next: '<i class="ti ti-chevron-right ti-sm"></i>',
                    previous: '<i class="ti ti-chevron-left ti-sm"></i>',
                },

                sProcessing: "جاري التحميل...",
                sLengthMenu: "أظهر _MENU_ سجلات",
                sZeroRecords: "لم يعثر على أي نتائج",
                sInfo: "إظهار _START_ إلى _END_ من أصل _TOTAL_ سجل",
                sInfoEmpty: "يعرض 0 إلى 0 من أصل 0 سجل",
                sInfoFiltered: "(منتقاة من مجموع _MAX_ مُدخل)",

                oPaginate: {
                    sFirst: "الأول",
                    sPrevious: "السابق",
                    sNext: "التالي",
                    sLast: "الأخير",
                },
                sLoadingRecords: "جارٍ التحميل...",
                sEmptyTable: "لا توجد بيانات متاحة في الجدول",
                oAria: {
                    sSortAscending: ": تفعيل لفرز العمود تصاعديًا",
                    sSortDescending: ": تفعيل لفرز العمود تنازليًا",
                },
            },
            // Buttons with Dropdown
            buttons: [
                {
                    extend: "collection",
                    className:
                        "btn btn-label-secondary dropdown-toggle mx-4 waves-effect waves-light",
                    text: '<i class="ti ti-upload me-2 ti-xs"></i>تصدير',
                    buttons: [
                        {
                            extend: "print",
                            text: '<i class="ti ti-printer me-2" ></i>طباعة ',
                            className: "dropdown-item",
                            exportOptions: {
                                columns: [1, 2, 3, 4], // تحديث الفهارس لتشمل تاريخ الإنشاء
                                // prevent avatar to be print
                                format: {
                                    body: function (inner, coldex, rowdex) {
                                        if (inner.length <= 0) return inner;
                                        var el = $.parseHTML(inner);
                                        var result = "";
                                        $.each(el, function (index, item) {
                                            if (
                                                item.classList !== undefined &&
                                                item.classList.contains(
                                                    "user-name",
                                                )
                                            ) {
                                                result =
                                                    result +
                                                    item.lastChild.firstChild
                                                        .textContent;
                                            } else if (
                                                item.innerText === undefined
                                            ) {
                                                result =
                                                    result + item.textContent;
                                            } else
                                                result =
                                                    result + item.innerText;
                                        });
                                        return result;
                                    },
                                },
                            },
                            customize: function (win) {
                                // customize print view for dark
                                $(win.document.body)
                                    .css("color", headingColor)
                                    .css("border-color", borderColor)
                                    .css("background-color", bodyBg);
                                $(win.document.body)
                                    .find("table")
                                    .addClass("compact")
                                    .css("color", "inherit")
                                    .css("border-color", "inherit")
                                    .css("background-color", "inherit");
                            },
                        },

                        {
                            extend: "excel",
                            text: '<i class="ti ti-file-spreadsheet me-2"></i>اكسل ',
                            className: "dropdown-item",
                            exportOptions: {
                                columns: [1, 2, 3, 4], // تحديث الفهارس لتشمل تاريخ الإنشاء
                                // prevent avatar to be display
                                format: {
                                    body: function (inner, coldex, rowdex) {
                                        if (inner.length <= 0) return inner;
                                        var el = $.parseHTML(inner);
                                        var result = "";
                                        $.each(el, function (index, item) {
                                            if (
                                                item.classList !== undefined &&
                                                item.classList.contains(
                                                    "user-name",
                                                )
                                            ) {
                                                result =
                                                    result +
                                                    item.lastChild.firstChild
                                                        .textContent;
                                            } else if (
                                                item.innerText === undefined
                                            ) {
                                                result =
                                                    result + item.textContent;
                                            } else
                                                result =
                                                    result + item.innerText;
                                        });
                                        return result;
                                    },
                                },
                            },
                        },
                        {
                            extend: "pdf",
                            text: '<i class="ti ti-file-code-2 me-2"></i>Pdf',
                            className: "dropdown-item",
                            exportOptions: {
                                columns: [1, 2, 3, 4], // تحديث الفهارس لتشمل تاريخ الإنشاء
                                // prevent avatar to be display
                                format: {
                                    body: function (inner, coldex, rowdex) {
                                        if (inner.length <= 0) return inner;
                                        var el = $.parseHTML(inner);
                                        var result = "";
                                        $.each(el, function (index, item) {
                                            if (
                                                item.classList !== undefined &&
                                                item.classList.contains(
                                                    "user-name",
                                                )
                                            ) {
                                                result =
                                                    result +
                                                    item.lastChild.firstChild
                                                        .textContent;
                                            } else if (
                                                item.innerText === undefined
                                            ) {
                                                result =
                                                    result + item.textContent;
                                            } else
                                                result =
                                                    result + item.innerText;
                                        });
                                        return result;
                                    },
                                },
                            },
                        },
                        {
                            extend: "copy",
                            text: '<i class="ti ti-copy me-2" ></i>نسخ ',
                            className: "dropdown-item",
                            exportOptions: {
                                columns: [1, 2, 3, 4], // تحديث الفهارس لتشمل تاريخ الإنشاء
                                // prevent avatar to be display
                                format: {
                                    body: function (inner, coldex, rowdex) {
                                        if (inner.length <= 0) return inner;
                                        var el = $.parseHTML(inner);
                                        var result = "";
                                        $.each(el, function (index, item) {
                                            if (
                                                item.classList !== undefined &&
                                                item.classList.contains(
                                                    "user-name",
                                                )
                                            ) {
                                                result =
                                                    result +
                                                    item.lastChild.firstChild
                                                        .textContent;
                                            } else if (
                                                item.innerText === undefined
                                            ) {
                                                result =
                                                    result + item.textContent;
                                            } else
                                                result =
                                                    result + item.innerText;
                                        });
                                        return result;
                                    },
                                },
                            },
                        },
                    ],
                },
                {
                    text: '<i class="ti ti-plus me-0 me-sm-1 ti-xs"></i><span class="d-none d-sm-inline-block">إضافة مستخدم </span>',
                    className:
                        "add-new btn btn-primary waves-effect waves-light",
                    attr: {
                        "data-bs-toggle": "modal",
                        "data-bs-target": "#addSectionModal",
                    },
                },
            ],
            // For responsive popup
            responsive: {
                details: {
                    display: $.fn.dataTable.Responsive.display.modal({
                        header: function (row) {
                            var data = row.data();
                            return "تفاصيل " + data["name"];
                        },
                    }),
                    type: "column",
                    renderer: function (api, rowIdx, columns) {
                        var data = $.map(columns, function (col, i) {
                            return col.title !== "" // لا تعرض الصف في النافذة المنبثقة إذا كان العنوان فارغاً (للاختيارات)
                                ? '<tr data-dt-row="' +
                                      col.rowIndex +
                                      '" data-dt-column="' +
                                      col.columnIndex +
                                      '">' +
                                      "<td>" +
                                      col.title +
                                      ":" +
                                      "</td> " +
                                      "<td>" +
                                      col.data +
                                      "</td>" +
                                      "</tr>"
                                : "";
                        }).join("");

                        return data
                            ? $('<table class="table"/><tbody />').append(data)
                            : false;
                    },
                },
            },
            // initComplete: function() {
            //     // يمكن تفعيل الفلاتر هنا إذا لزم الأمر
            // }
        });
    }

    // Delete Record

    // $(".datatables-users tbody").on("click", ".delete-record", function () {
    //     dt_user.row($(this).parents("tr")).remove().draw();
    // });

    // Filter form control to default size
    // ? setTimeout used for multilingual table initialization
    setTimeout(() => {
        $(".dataTables_filter .form-control").removeClass("form-control-sm");
        $(".dataTables_length .form-select").removeClass("form-select-sm");
    }, 300);
});
