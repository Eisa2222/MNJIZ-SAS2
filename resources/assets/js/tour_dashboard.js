"use strict";

(function () {
    function setupTour(tour) {
        const backBtnClass =
                "btn btn-sm btn-label-secondary md-btn-flat waves-effect waves-light",
            nextBtnClass =
                "btn btn-sm btn-primary btn-next waves-effect waves-light";

        tour.addStep({
            title: `مرحبًا  بك ${userName}!`,
            text: "مرحبًا بك في النظام! سنقوم بجولة تعريفية لشرح أهم الميزات.",
            buttons: [
                {
                    text: "تخطي الجولة",
                    classes: backBtnClass,
                     action: function () {
                        markTourComplete();
                        tour.cancel();
                    },
                },
                {
                    text: "ابدأ الجولة",
                    classes: nextBtnClass,
                    action: tour.next,
                },
            ],
        });

        tour.addStep({
            title: "شريط البحث",
            text: "هنا يمكنك البحث عن أي شيء داخل النظام. يمكنك أيضًا استخدام الاختصار Ctrl + / للوصول السريع إلى شريط البحث.",
            attachTo: { element: ".navbar", on: "bottom" },
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
            title: "تغيير الوضع الليلي",
            text: "هنا يمكنك تغيير الوضع الليلي.",
            attachTo: { element: ".dropdown-style-switcher", on: "left" },
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
            title: "الإشعارات",
            text: "هنا يمكنك مشاهدة الإشعارات الخاصة بك.",
            attachTo: { element: ".dropdown-notifications", on: "left" },
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
            title: "الملف الشخصي",
            text: "من  هنا يمكنك الوصول للملف الشخصي الخاص بك",
            attachTo: { element: ".avatar-online", on: "left" },
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
            title: "الشريط الجانبي",
            text: "بإمكانك الوصول إلى جميع الوظائف من هنا.",
            attachTo: { element: "#layout-menu", on: "right" },
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
            title: "محتوىات الصفحة",
            text: "هنا تظهر محتوىات الصفحة والمعلومات المهمة التي تحتاجها.",
            attachTo: { element: "#content-area", on: "top" },
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
            title: "أداة تخصيص القالب",
            text: "هنا يمكنك تخصيص مظهر النظام بشكل كامل من خلال خيارات التخصيص المتاحة.",
            attachTo: { element: ".template-customizer-open-btn", on: "right" },
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
