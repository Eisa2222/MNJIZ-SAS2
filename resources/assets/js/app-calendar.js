import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import interactionPlugin from "@fullcalendar/interaction";
import moment from "moment";
import axios from "axios";

document.addEventListener("DOMContentLoaded", function () {
    const calendarEl = document.getElementById("calendar");
    if (calendarEl) {
        // متغير للتحكم في حالة التحميل
        let isLoadingEvent = false;

        // إظهار loader عند بداية تحميل الصفحة
        showLoader();

        // إضافة رسالة مختلفة للتحميل الأولي
        updateLoaderText("جاري تحميل الأحداث...");

        const calendar = new Calendar(calendarEl, {
            plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
            initialView: "dayGridMonth",
            timeZone: "UTC",
            locale: 'ar',
            headerToolbar: {
                left: "prev,next today",
                center: "title",
                right: "dayGridMonth,timeGridWeek,timeGridDay",
            },
            // لتخصيص النصوص الظاهرة في الأزرار
            buttonText: {
                today: 'اليوم',
                month: 'الشهر',
                week: 'الأسبوع',
                day: 'يوم'
            },
            events: {
                url: "/calendar/events",
                method: "GET",
                failure: function () {
                    hideLoader();
                    toastr.error("فشل في جلب الأحداث. تحقق من السجلات.");
                },
                error: function () {
                    hideLoader();
                    toastr.error("خطأ أثناء جلب الأحداث.");
                },
                success: function () {
                    // إخفاء loader بعد تحميل الأحداث بنجاح
                    hideLoader();
                },
                // استخدام eventDataTransform لإضافة فئات CSS مناسبة
                eventDataTransform: function(eventData) {

                    // تحويل العنوان إلى نص صغير (lowercase) لتسهيل البحث
                    if (eventData.title && typeof eventData.title === 'string') {
                        const title = eventData.title.toLowerCase();

                        if (title.includes('جلسة')) {
                            // إضافة فئة CSS للجلسات
                            eventData.classNames = ['fc-event-danger'];
                        }
                        else if (title.includes('مهمة')) {
                            // إضافة فئة CSS للمهام
                            eventData.classNames = ['fc-event-success'];
                        }
                        else if (title.includes('تكوين') || title.includes('تحرير')) {
                            // إضافة فئة CSS للتكوين
                            eventData.classNames = ['fc-event-info'];
                        }
                        else if (title.includes('عيد')) {
                            // إضافة فئة CSS للأعياد
                            eventData.classNames = ['fc-event-warning'];
                        }
                        else {
                            // فئة افتراضية
                            eventData.classNames = ['fc-event-primary'];
                        }
                    }

                    return eventData;
                }
            },
            loading: function(isLoading) {
                if (isLoading) {
                    // إظهار loader عند بدء تحميل الأحداث
                    showLoader();
                    updateLoaderText("جاري تحميل الأحداث...");
                } else {
                    // إخفاء loader عند الانتهاء من تحميل الأحداث
                    hideLoader();
                }
            },
            eventClick: function (info) {
                // تجنب فتح الحدث إذا كان هناك حدث آخر قيد التحميل
                if (isLoadingEvent) {
                    return;
                }

                // تعيين حالة التحميل إلى صحيح
                isLoadingEvent = true;

                // إظهار عنصر التحميل
                showLoader();
                updateLoaderText("جاري تحميل بيانات الحدث...");

                const eventId = info.event.id;
                axios
                    .get(`/calendar/events/${eventId}`)
                    .then((response) => {
                        // إخفاء عنصر التحميل
                        hideLoader();

                        // إعادة تعيين حالة التحميل إلى خطأ
                        isLoadingEvent = false;

                        // بعد النجاح، نخفي رسالة الانتظار
                        toastr.clear();

                        const event = response.data;
                        document.getElementById("detailEventId").value = event.id;
                        document.getElementById("detailEventTitle").value = event.title;
                        document.getElementById("detailEventStartDate").value = event.start_date;
                        document.getElementById("detailEventStartTime").value = event.start_time;
                        document.getElementById("detailEventEndDate").value = event.end_date;
                        document.getElementById("detailEventEndTime").value = event.end_time;
                        document.getElementById("detailEventDescription").value = event.body;

                        toggleFields(false);

                        document.getElementById("deleteEventButton").classList.remove("d-none");
                        document.getElementById("saveEditButton").classList.add("d-none");
                        document.getElementById("editEventButton").classList.remove("d-none");

                        var eventDetailsSidebar = new bootstrap.Offcanvas(
                            document.getElementById("eventDetailsSidebar"),
                        );
                        eventDetailsSidebar.show();
                    })
                    .catch((error) => {
                        // إخفاء عنصر التحميل في حالة حدوث خطأ
                        hideLoader();

                        // إعادة تعيين حالة التحميل إلى خطأ
                        isLoadingEvent = false;

                        toastr.clear();
                        toastr.error("خطأ أثناء جلب تفاصيل الحدث.");
                    });
            },
            selectable: true,
            select: function (info) {
                var addEventSidebar = new bootstrap.Offcanvas(
                    document.getElementById("addEventSidebar")
                );
                addEventSidebar.show();

                // إذا كنت لا تريد طرح يوم من التاريخ النهائي، احذف subtract(1, "days")
                document.getElementById("eventStartDate").value = moment(info.start).format("YYYY-MM-DD");
                document.getElementById("eventEndDate").value = moment(info.end)
                    // .subtract(1, "days") // تأكّد إن كنت ترغب حقًا في إنقاص يوم أم لا
                    .format("YYYY-MM-DD");
            },
            editable: false,
            dayMaxEvents: true,
        });

        calendar.render();

        if (window.hasSuccess) {
            showLoader();
            updateLoaderText("جاري تحديث الأحداث...");
            calendar.refetchEvents();
        }

        // وظائف لإظهار وإخفاء عنصر التحميل
        function showLoader() {
            // إذا كان لديك عنصر HTML موجود للـ loader
            const loaderElement = document.getElementById("calendarLoader");
            if (loaderElement) {
                loaderElement.classList.remove("d-none");
            } else {
                // إنشاء عنصر loader جديد إذا لم يكن موجوداً
                createAndShowLoader();
            }

            // إضافة طبقة تعتيم فوق التقويم لمنع التفاعل المزدوج
            addOverlay();
        }

        function hideLoader() {
            // إخفاء عنصر التحميل
            const loaderElement = document.getElementById("calendarLoader");
            if (loaderElement) {
                loaderElement.classList.add("d-none");
            }

            // إزالة طبقة التعتيم
            removeOverlay();
        }

        function createAndShowLoader() {
            // إنشاء عنصر loader جديد وإضافته للصفحة مع رسالة نصية واضحة تحته
            // مع z-index أعلى من الـ overlay لضمان ظهور الـ loader فوق الـ overlay
            const loaderHTML = `
                <div id="calendarLoader" class="position-fixed top-50 start-50 translate-middle text-center" style="z-index: 1060;">
                    <div class="spinner-border text-primary spinner-border-lg" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">جاري التحميل...</span>
                    </div>
                    <div id="loaderText" class="mt-2 fw-bold text-primary">جاري تحميل البيانات...</div>
                </div>
            `;

            // إضافة العنصر للـ body
            document.body.insertAdjacentHTML('beforeend', loaderHTML);
        }

        // وظيفة لتحديث نص الـ loader
        function updateLoaderText(text) {
            const loaderTextElement = document.getElementById("loaderText");
            if (loaderTextElement) {
                loaderTextElement.textContent = text;
            }
        }

        function addOverlay() {
            // إضافة طبقة تعتيم فوق التقويم بشفافية أكثر وضوحًا
            const overlayHTML = `
                <div id="calendarOverlay" class="position-fixed top-0 start-0 w-100 h-100"
                     style="background-color: rgba(0,0,0,0.7); z-index: 1050; backdrop-filter: blur(2px);"></div>
            `;

            // التحقق من عدم وجود طبقة تعتيم سابقة
            if (!document.getElementById("calendarOverlay")) {
                document.body.insertAdjacentHTML('beforeend', overlayHTML);
            }
        }

        function removeOverlay() {
            // إزالة طبقة التعتيم
            const overlay = document.getElementById("calendarOverlay");
            if (overlay) {
                overlay.remove();
            }
        }

        // أضف أيضًا loader عند تغيير الشهر أو العرض
        const prevButton = document.querySelector('.fc-prev-button');
        const nextButton = document.querySelector('.fc-next-button');
        const todayButton = document.querySelector('.fc-today-button');
        const viewButtons = document.querySelectorAll('.fc-dayGridMonth-button, .fc-timeGridWeek-button, .fc-timeGridDay-button');

        // إضافة مستمعي الأحداث لأزرار التنقل
        if (prevButton) {
            prevButton.addEventListener('click', function() {
                showLoader();
                updateLoaderText("جاري تحميل الأحداث...");
            });
        }

        if (nextButton) {
            nextButton.addEventListener('click', function() {
                showLoader();
                updateLoaderText("جاري تحميل الأحداث...");
            });
        }

        if (todayButton) {
            todayButton.addEventListener('click', function() {
                showLoader();
                updateLoaderText("جاري تحميل الأحداث...");
            });
        }

        // إضافة مستمعي الأحداث لأزرار العرض
        viewButtons.forEach(button => {
            button.addEventListener('click', function() {
                showLoader();
                updateLoaderText("جاري تحميل الأحداث...");
            });
        });

        // زر تعديل الحدث
        document.getElementById("editEventButton").addEventListener("click", function () {
            toggleFields(true);
            document.getElementById("saveEditButton").classList.remove("d-none");
            document.getElementById("editEventButton").classList.add("d-none");
        });

        // زر حفظ التعديلات
        document.getElementById("saveEditButton").addEventListener("click", function () {
            const eventId = document.getElementById("detailEventId").value;
            const form = document.getElementById("eventDetailsForm");
            const formData = new FormData(form);

            // إظهار loader أثناء حفظ التعديلات
            showLoader();
            updateLoaderText("جاري حفظ التعديلات...");

            axios
                .post(`/calendar/events/${eventId}/update`, formData)
                .then((response) => {
                    // إخفاء loader بعد الانتهاء
                    hideLoader();

                    toastr.success("تم تعديل الحدث بنجاح.");
                    var eventDetailsSidebar = bootstrap.Offcanvas.getInstance(
                        document.getElementById("eventDetailsSidebar"),
                    );
                    eventDetailsSidebar.hide();
                    calendar.refetchEvents();
                })
                .catch((error) => {
                    // إخفاء loader في حالة حدوث خطأ
                    hideLoader();

                    toastr.error("خطأ في تعديل الحدث.");
                });
        });

        // زر حذف الحدث
        const deleteEventButton = document.getElementById("deleteEventButton");
        if (deleteEventButton) {
            deleteEventButton.addEventListener("click", function (e) {
                e.preventDefault();

                Swal.fire({
                    title: "هل أنت متأكد من عملية الحذف؟",
                    text: "لا يمكن التراجع عن هذا الإجراء!",
                    icon: "warning",
                    showCancelButton: true,
                    showConfirmButton: true,
                    buttonsStyling: false,
                    customClass: {
                        popup: "custom-popup",
                        title: "custom-title",
                        text: "custom-text",
                        confirmButton: "btn btn-success custom-confirm",
                        cancelButton: "btn btn-danger custom-cancel",
                    },
                    confirmButtonText: "تأكيد",
                    cancelButtonText: "إلغاء",
                }).then((result) => {
                    if (result.isConfirmed) {
                        // إظهار loader أثناء حذف الحدث
                        showLoader();
                        updateLoaderText("جاري حذف الحدث...");

                        const eventId = document.getElementById("detailEventId").value;
                        axios
                            .delete(`/calendar/events/${eventId}/delete`)
                            .then((response) => {
                                // إخفاء loader بعد الانتهاء
                                hideLoader();

                                toastr.success("تم حذف الحدث بنجاح.");
                                var eventDetailsSidebar = bootstrap.Offcanvas.getInstance(
                                    document.getElementById("eventDetailsSidebar"),
                                );
                                eventDetailsSidebar.hide();
                                calendar.refetchEvents();
                            })
                            .catch((error) => {
                                // إخفاء loader في حالة حدوث خطأ
                                hideLoader();

                                toastr.error("خطأ في حذف الحدث.");
                            });
                    }
                });
            });
        }

        // وظيفة لتفعيل/تعطيل الحقول في الشريط الجانبي
        function toggleFields(enable) {
            const form = document.getElementById("eventDetailsForm");
            Array.from(form.elements).forEach((element) => {
                if (element.id === "detailEventId") return;

                // استثناء الأزرار
                if (["BUTTON", "SUBMIT", "RESET"].includes(element.tagName)) {
                    return;
                }
                if (enable) {
                    element.removeAttribute("readonly");
                    element.removeAttribute("disabled");
                } else {
                    element.setAttribute("readonly", "readonly");
                    element.setAttribute("disabled", "disabled");
                }
            });
        }
    }
});


document.addEventListener("DOMContentLoaded", function () {
    // تعيين عناصر التاريخ في نموذج الإضافة
    const eventStartDateEl = document.getElementById("eventStartDate");
    const eventEndDateEl = document.getElementById("eventEndDate");

    if (eventStartDateEl && eventEndDateEl) {
        // عندما يختار المستخدم تاريخ البداية، نجعل تاريخ النهاية لا يمكن أن يقل عنه
        eventStartDateEl.addEventListener("change", () => {
            eventEndDateEl.min = eventStartDateEl.value;
            // لو كان تاريخ النهاية أقل من البداية حاليًا، نسويه بالبداية مباشرة
            if (eventEndDateEl.value < eventStartDateEl.value) {
                eventEndDateEl.value = eventStartDateEl.value;
            }
        });

        // عندما يختار المستخدم تاريخ النهاية، نتأكد أنه ليس أقل من البداية
        eventEndDateEl.addEventListener("change", () => {
            if (eventEndDateEl.value < eventStartDateEl.value) {
                toastr.error("لا يمكن أن يكون تاريخ النهاية قبل تاريخ البداية.");
                // إعادة ضبط تاريخ النهاية لكي يساوي البداية
                eventEndDateEl.value = eventStartDateEl.value;
            }
        });
    }

    // يمكنك تكرار نفس الفكرة في شريط تعديل الحدث (detailEventStartDate / detailEventEndDate) إذا كان ذلك ضروريًا
});
