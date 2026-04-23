"use strict";

(function () {
    function setupTour(tour) {
        const backBtnClass =
                "btn btn-sm btn-label-secondary md-btn-flat waves-effect waves-light",
            nextBtnClass =
                "btn btn-sm btn-primary btn-next waves-effect waves-light";

        tour.addStep({
            title: "شريط التنقل",
            text: "يوضح لك مسار تنقلك داخل النظام ويساعدك على معرفة موقعك الحالي.",
            attachTo: { element: ".breadcrumb", on: "bottom" },
            buttons: [
                {
                    text: "تخطي",
                    classes: backBtnClass,
                    action: function () {
                        markTourComplete();
                        tour.cancel();
                    },
                },

                {
                    text: "التالي",
                    classes: nextBtnClass,
                    action: tour.next,
                },
            ],
        });

        tour.addStep({
            title: "لوحة إحصائيات المشاريع",
            text: "هنا يتم عرض إحصائيات المشاريع والأرقام الهامة حسب صلاحياتك ومسماك الوظيفي.",
            attachTo: { element: "#top_card", on: "bottom" },
            buttons: [
                {
                    text: "تخطي",
                    classes: backBtnClass,
                     action: function () {
                        markTourComplete();
                        tour.cancel();
                    },
                },
                {
                    text: "السابق",
                    classes: backBtnClass,
                    action: tour.back,
                },
                {
                    text: "التالي",
                    classes: nextBtnClass,
                    action: tour.next,
                },
            ],
        });

        tour.addStep({
            title: "فلترة المشاريع",
            text: "هنا يمكنك تصفية وترتيب المشاريع حسب المعايير المختلفة.",
            attachTo: { element: ".filters", on: "bottom" },
            buttons: [
                {
                    text: "تخطي",
                    classes: backBtnClass,
                     action: function () {
                        markTourComplete();
                        tour.cancel();
                    },
                },
                {
                    text: "السابق",
                    classes: backBtnClass,
                    action: tour.back,
                },
                {
                    text: "التالي",
                    classes: nextBtnClass,
                    action: tour.next,
                },
            ],
        });

        tour.addStep({
            title: "عدد الصفوف",
            text: "من  هنا يمكنك تحديد عدد الصفوف التي تظهر في كل صفحة.",
            attachTo: { element: ".dt-toolbar-left", on: "left" },
            buttons: [
                {
                    text: "تخطي",
                    classes: backBtnClass,
                     action: function () {
                        markTourComplete();
                        tour.cancel();
                    },
                },
                {
                    text: "السابق",
                    classes: backBtnClass,
                    action: tour.back,
                },
                {
                    text: "التالي",
                    classes: nextBtnClass,
                    action: tour.next,
                },
            ],
        });

        tour.addStep({
            title: "البحث",
            text: "من هنا يمكنك البحث عن المشاريع بشكل سريع.",
            attachTo: { element: "#projects-table_filter", on: "right" },
            buttons: [
            {
                text: "تخطي",
                classes: backBtnClass,
                 action: function () {
                        markTourComplete();
                        tour.cancel();
                    },
            },
            {
                text: "السابق",
                classes: backBtnClass,
                action: tour.back,
            },
            {
                text: "التالي",
                classes: nextBtnClass,
                action: tour.next,
            },
            ],
        });

        tour.addStep({

            title: "الاجراءات الخاصة بالمشروع",
            text: "هنا يمكنك الوصول إلى الإجراءات المختلفة المتعلقة بالمشروع مثل الطباعة حذف المحدد يجب عليك تحديد العناصر اولا اذا اردت حذف المحدد.",
            attachTo: { element: ".buttons-collection", on: "top" },
            buttons: [
            {
                text: "تخطي",
                classes: backBtnClass,

                 action: function () {
                        markTourComplete();
                        tour.cancel();
                    },
            },
            {
                text: "السابق",
                classes: backBtnClass,
                action: tour.back,
            },
            {
                text: "التالي",
                classes: nextBtnClass,
                action: tour.next,
            },
            ],
        });

        tour.addStep({
            title: "إضافة مشروع جديد",
            text: "هنا يمكنك إضافة مشروع جديد إلى النظام من خلال تعبئة البيانات المطلوبة.",
            attachTo: { element: ".btn-add", on: "right" },
            scrollTo: true,
            buttons: [
            {
                text: "تخطي",
                classes: backBtnClass,
                 action: function () {
                        markTourComplete();
                        tour.cancel();
                    },
            },
            {
                text: "السابق",
                classes: backBtnClass,
                action: tour.back,
            },
            {
                text: "التالي",
                classes: nextBtnClass,
                action: tour.next,
            },
            ],
        });


        tour.addStep({
            title: "تحديد الكل",
            text: "هنا يمكنك تحديد كل العناصر أو يمكنك تحديد كل عنصر على بشكل منفرد.",
            attachTo: { element: "#select-all", on: "top" },
            scrollTo: true,
            buttons: [
            {
                text: "تخطي",
                classes: backBtnClass,
                 action: function () {
                        markTourComplete();
                        tour.cancel();
                    },
            },
            {
                text: "السابق",
                classes: backBtnClass,
                action: tour.back,
            },
            {
                text: "التالي",
                classes: nextBtnClass,
                action: tour.next,
            },
            ],
        });

        tour.addStep({
            title: "أزرار الإجراءات",
            text: "هنا يمكنك الوصول إلى أزرار الإجراءات المختلفة المتعلقة بالمشروع، والتي تظهر حسب الصلاحيات الممنوحة لك.",
            attachTo: { element: "td:last-child", on: "top" },
            scrollTo: true,
            buttons: [
            {
                text: "تخطي",
                classes: backBtnClass,
                 action: function () {
                        markTourComplete();
                        tour.cancel();
                    },
            },
            {
                text: "السابق",
                classes: backBtnClass,
                action: tour.back,
            },
            {
                text: "التالي",
                classes: nextBtnClass,
                action: tour.next,
            },
            ],
        });


        tour.addStep({
            title: "الدعم الفني",
            text: "   هنا يمكنك الحصول على الدعم الفني والمساعدة. ",

            attachTo: { element: "#footer-support", on: "top" },
            scrollTo: true,
            buttons: [
                {
                    text: "تخطي",
                    classes: backBtnClass,
                     action: function () {
                        markTourComplete();
                        tour.cancel();
                    },
                },
                {
                    text: "السابق",
                    classes: backBtnClass,
                    action: tour.back,
                },
                {
                    text: "التالي",
                    classes: nextBtnClass,
                    action: tour.next,
                },
            ],
        });

        tour.addStep({
            title: " إتمام لتقنية نظم المعلومات ❤️ ",
            text: "شكرًا لاستخدامك نظامنا! نتمنى لك تجربة فريدة و مميزة.",
            attachTo: { element: ".footer-link", on: "top" },
            buttons: [
                {
                    text: "السابق",
                    classes: backBtnClass,
                    action: tour.back,
                },
                {
                    text: "إنهاء الجولة",
                    classes: nextBtnClass,
                     action: function () {
                        markTourComplete();
                        tour.cancel();
                    },
                },
            ],
        });

        return tour;
    }

    function markTourComplete() {
        const userId = "USER_ID_PLACEHOLDER"; // ضع هنا معرف المستخدم الحالي
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content'); // جلب CSRF Token

        fetch(`/mark-tour-complete`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken, // تضمين CSRF Token في الهيدر
            },
            body: JSON.stringify({ userId: userId }),
        })
        .then(response => {
            if (!response.ok) {
                console.error("Failed to mark the tour as complete.");
            } else {
                console.log("Tour marked as complete successfully.");
            }
        })
        .catch(error => console.error("Error:", error));
    }


    window.onload = function () {
        const userId = "USER_ID_PLACEHOLDER"; // ضع هنا معرف المستخدم الحالي
        fetch(`/check-tour-status?userId=${userId}`)
            .then(response => response.json())
            .then(data => {
                if (!data.tourCompleted) {
                    const tourVar = new Shepherd.Tour({
                        defaultStepOptions: {
                            scrollTo: false,
                            cancelIcon: {
                                enabled: true,
                            },
                        },
                        useModalOverlay: true,
                    });
                    setupTour(tourVar).start();
                }
            });
    };


})();
