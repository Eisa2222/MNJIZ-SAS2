"use strict";

(function () {
    function setupTour(tour) {
        const backBtnClass =
                "btn btn-sm btn-label-secondary md-btn-flat waves-effect waves-light",
            nextBtnClass =
                "btn btn-sm btn-primary btn-next waves-effect waves-light";

        tour.addStep({
            title: `مرحبًا  بك ${userName}!`,
            text: "مرحبًا بك في النظام! سنقوم بجولة تعريفية لشرح نظام المهام.",
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
            title: "إجمالي المهام",
            text: "هنا يتم عرض إجمالي المهام التي قمت بإسنادها للآخرين والتي تم إسنادها لك.",
            attachTo: { element: ".allTasks", on: "bottom" },
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
            title: "المهام المسندة لك",
            text: "هنا يتم عرض المهام المسندة لك ويوضح المكتملة منها و غير المكتملة ",
            attachTo: { element: "#myTasks", on: "bottom" },
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
            title: "المهام التي قمت باسنادها ",
            text: "هنا يتم عرض المهام التي قمت باسنادها لاشخاص اخرين  ويوضح المكتملة منها و غير المكتملة ",
            attachTo: { element: "#assignedTasks", on: "bottom" },
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
            title: "كل المهام",
            text: "هنا يتم عرض كل المهام ويوضح المكتملة منها و غير المكتملة ",
            attachTo: { element: "#allTasks", on: "bottom" },
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
            title: "الشخص المسندة له المهمة",
            text: "هنا يتم عرض  الشخص المسند إليه المهمة مع الصورة الخاصة به مما يُعطيك فكرة عن من يتولى المهمة.",
            attachTo: { element: "#img_tasks", on: "bottom" },
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

        // خطوة 2: شرح تاريخ الاستحقاق (قسم date_tasks)
        tour.addStep({
            title: "تاريخ الاستحقاق",
            text: "هذا القسم يعرض تاريخ ووقت الاستحقاق المُحدد للمهمة",
            attachTo: { element: "#date_tasks", on: "top" },
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

        // خطوة 3: شرح رابط اسم المهمة (قسم link_tasks)
        tour.addStep({
            title: "رابط المهمة",
            text: "بالضغط هنا يمكنك الانتقال إلى صفحة تفاصيل المهمة لمزيد من المعلومات عنها.",
            attachTo: { element: "#link_tasks", on: "bottom" },
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

        // خطوة 4: شرح مجال المهمة (قسم field_tasks)
        tour.addStep({
            title: "مجال المهمة",
            text: "هذا العنصر يُوضح مجال المهمة، مما يساعدك في تصنيف المهمة حسب نوعها.",
            attachTo: { element: "#field_tasks", on: "right" },
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

        // خطوة 5: شرح أيقونة المرفق (قسم file_tasks)
        tour.addStep({
            title: "المرفق",
            text: "إذا كانت المهمة تحتوي على مرفق، ستُعرض هنا أيقونة المرفق التي تسمح لك بتنزيل الملف.",
            attachTo: { element: "#file_tasks", on: "top" },
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

        // خطوة 6: شرح دائرة الحالة أو الأولوية (قسم status_tasks)
        tour.addStep({
            title: "حالة المهمة / الأولوية",
            text: "تُستخدم الدائرة الملونة هنا لتحديد حالة المهمة أو أولويتها، حيث يختلف اللون حسب الحالة (عاجلة، متوسطة أو منخفضة).",
            attachTo: { element: "#status_tasks", on: "bottom" },
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

        // خطوة 7: شرح زر عرض تفاصيل المهمة (قسم detils_tasks)
        tour.addStep({
            title: "عرض تفاصيل المهمة",
            text: "بالضغط على هذا الزر ستظهر نافذة منبثقة تحتوي على بقية تفاصيل المهمة.",
            attachTo: { element: "#detils_tasks", on: "right" },
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

        // خطوة 8: شرح خانة إتمام المهمة (قسم complete_tasks)
        tour.addStep({
            title: "إتمام المهمة",
            text: "من خلال الضغط هنا يمكن للمستخدم تحديد المهمة كمكتملة، سواءً كان هو المُسنَد إليها أو من قام بإضافتها.",
            attachTo: { element: "#complete_tasks", on: "bottom" },
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

        // خطوة 9: شرح زر تعديل المهمة (قسم edit_tasks)
        tour.addStep({
            title: "تعديل المهمة",
            text: "هذا الزر يسمح للمستخدم بتعديل تفاصيل المهمة، وهو متاح عادةً لمن قام بإضافة المهمة.",
            attachTo: { element: "#edit_tasks", on: "top" },
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

        // خطوة 10: شرح زر حذف المهمة (قسم delete_tasks)
        tour.addStep({
            title: "حذف المهمة",
            text: "باستخدام هذا الزر يمكن حذف المهمة. يُنصح بالتأكد من رغبتك في الحذف لأن العملية لا يمكن التراجع عنها.",
            attachTo: { element: "#delete_tasks", on: "bottom" },
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
            title: "اضافة مهمة جديدة",
            text: "من هنا يمكنك اضافة مهمة جديدة و فلترة المهام حسب المهام المسندة لك او التي قمت باسنادها للاخرين",
            attachTo: { element: "#addTasks", on: "left" },
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
            title: "حالة المهام",
            text: "هنا يتم عرض المهام حسب حالتها مكتملة او غير مكتملة",
            attachTo: { element: "#tasksStatus", on: "left" },
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
            title: "اولوية المهام",
            text: "هنا يتم عرض المهام حسب الاولوية مرتفعة او متوسطة او منخفضة ",
            attachTo: { element: "#tasksPriority", on: "left" },
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
            title: "تصنيف المهام",
            text: "هنا يتم عرض المهام حسب التصنيف     ",
            attachTo: { element: "#tasksCategory", on: "left" },
            scrollTo: true,
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
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content"); // جلب CSRF Token

        fetch(`/mark-tour-tasks-complete`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken, // تضمين CSRF Token في الهيدر
            },
            body: JSON.stringify({ userId: userId }),
        })
            .then((response) => {
                if (!response.ok) {
                    console.error("Failed to mark the tour as complete.");
                } else {
                    console.log("Tour marked as complete successfully.");
                }
            })
            .catch((error) => console.error("Error:", error));
    }

    window.onload = function () {
        const userId = "USER_ID_PLACEHOLDER"; // ضع هنا معرف المستخدم الحالي
        fetch(`/check-tour-tasks-status?userId=${userId}`)
            .then((response) => response.json())
            .then((data) => {
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
