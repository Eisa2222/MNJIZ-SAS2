"use strict";

(function () {
    const { userNewTask, userComplateTask, activeSessions, closedSessions, colorsUnique } = window.taskData;

    let cardColor, labelColor, borderColor, legendColor;

    if (isDarkStyle) {
        cardColor = config.colors_dark.cardColor;
        labelColor = config.colors_dark.textMuted;
        legendColor = config.colors_dark.bodyColor;
        borderColor = config.colors_dark.borderColor;
    } else {
        cardColor = config.colors.cardColor;
        labelColor = config.colors.textMuted;
        legendColor = config.colors.bodyColor;
        borderColor = config.colors.borderColor;
    }

    // مخطط المهام
    const taskChartEl = document.querySelector("#tasksChart"),
        taskChartConfig = {
            chart: {
                height: 125,
                width: 120,
                parentHeightOffset: 0,
                type: "donut",
            },
            labels: ["مهام جديدة", "مهام مكتملة"],
            series: [userNewTask, userComplateTask],
            colors: colorsUnique,
            stroke: {
                width: 0,
            },
            dataLabels: {
                enabled: false,
                formatter: function (val, opt) {
                    return parseInt(val) + "%";
                },
            },
            legend: {
                show: false,
            },
            tooltip: {
                theme: false,
            },
            grid: {
                padding: {
                    top: 15,
                    right: -20,
                    left: -20,
                },
            },
            states: {
                hover: {
                    filter: {
                        type: "none",
                    },
                },
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: "70%",
                        labels: {
                            show: true,
                            value: {
                                fontSize: "1.5rem",
                                fontFamily: "Public Sans",
                                color: colorsUnique[0],
                                fontWeight: 500,
                                offsetY: -15,
                                formatter: function (val) {
                                    return parseInt(val) + "%";
                                },
                            },
                            name: {
                                offsetY: 20,
                                fontFamily: "Public Sans",
                            },
                            total: {
                                show: true,
                                showAlways: true,
                                fontSize: ".8125rem",
                                label: "المجموع",
                                fontFamily: "Public Sans",
                                formatter: function (w) {
                                    return userNewTask + userComplateTask;
                                },
                            },
                        },
                    },
                },
            },
            responsive: [
                {
                    breakpoint: 1025,
                    options: {
                        chart: {
                            height: 172,
                            width: 160,
                        },
                    },
                },
                {
                    breakpoint: 769,
                    options: {
                        chart: {
                            height: 178,
                        },
                    },
                },
                {
                    breakpoint: 426,
                    options: {
                        chart: {
                            height: 147,
                        },
                    },
                },
            ],
        };

    if (taskChartEl !== null) {
        const taskChart = new ApexCharts(taskChartEl, taskChartConfig);
        taskChart.render();
    }

    // مخطط الجلسات
    const sessionsChartEl = document.querySelector("#sessionsChart"),
        sessionsChartConfig = {
            chart: {
                height: 125,
                width: 120,
                parentHeightOffset: 0,
                type: "donut",
            },
            labels: ["جلسات جديدة", "جلسات مكتملة"],
            series: [activeSessions, closedSessions],
            colors: colorsUnique, // استخدام ألوان مختلفة
            stroke: {
                width: 0,
            },
            dataLabels: {
                enabled: false,
                formatter: function (val, opt) {
                    return parseInt(val) + "%";
                },
            },
            legend: {
                show: false,
            },
            tooltip: {
                theme: false,
            },
            grid: {
                padding: {
                    top: 15,
                    right: -20,
                    left: -20,
                },
            },
            states: {
                hover: {
                    filter: {
                        type: "none",
                    },
                },
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: "70%",
                        labels: {
                            show: true,
                            value: {
                                fontSize: "1.5rem",
                                fontFamily: "Public Sans",
                                color: colorsUnique[0],
                                fontWeight: 500,
                                offsetY: -15,
                                formatter: function (val) {
                                    return parseInt(val) + "%";
                                },
                            },
                            name: {
                                offsetY: 20,
                                fontFamily: "Public Sans",
                            },
                            total: {
                                show: true,
                                showAlways: true,
                                fontSize: ".8125rem",
                                label: "المجموع",
                                fontFamily: "Public Sans",
                                formatter: function (w) {
                                    return activeSessions + closedSessions;
                                },
                            },
                        },
                    },
                },
            },
            responsive: [
                {
                    breakpoint: 1025,
                    options: {
                        chart: {
                            height: 172,
                            width: 160,
                        },
                    },
                },
                {
                    breakpoint: 769,
                    options: {
                        chart: {
                            height: 178,
                        },
                    },
                },
                {
                    breakpoint: 426,
                    options: {
                        chart: {
                            height: 147,
                        },
                    },
                },
            ],
        };

    if (sessionsChartEl !== null) {
        const sessionsChart = new ApexCharts(sessionsChartEl, sessionsChartConfig);
        sessionsChart.render();
    }
})();
