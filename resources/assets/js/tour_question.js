"use strict";

(function () {
    function setupTour(tour) {
        const backBtnClass =
            "btn btn-sm btn-label-secondary md-btn-flat waves-effect waves-light",
            nextBtnClass =
                "btn btn-sm btn-primary btn-next waves-effect waves-light";

        tour.addStep({
            title: "ملاحظة توضيحية",
            text: "هنا يتم تقديم شرح توضيحي للعنصر المحدد في النظام.",
            attachTo: { element: ".fa-question-circle", on: "bottom" },
            buttons: [


                {
                    text: "فهمت",
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
