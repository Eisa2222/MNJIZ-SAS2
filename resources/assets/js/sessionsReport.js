document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') {
        console.error('مكتبة Chart.js غير محملة.');
        return;
    }

    // وظيفة لتوليد مخطط doughnut
    function createDoughnutChart(ctx, labels, data, backgroundColors) {
        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: backgroundColors,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true
                    }
                },
            }
        });
    }

    // وظيفة لتوليد مخطط bar
    function createBarChart(ctx, labels, data, backgroundColors) {
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'عدد الجلسات',
                    data: data,
                    backgroundColor: backgroundColors,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'الشهر الهجري'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1, // جعل المسافة بين العلامات 1
                            callback: function(value) {
                                return Number.isInteger(value) ? value : ''; // عرض الأرقام الصحيحة فقط
                            }
                        },
                        title: {
                            display: true,
                            text: 'عدد الجلسات'
                        }
                    }
                }
            }
        });
    }

    // دالة لتوليد المخططات وتعيين الشارات
    function initializeChart(chartId, dataKey, badgePrefix, downloadFilename, chartType = 'doughnut') {
        const chartData = window[dataKey] || [];
        const labels = chartData.map(item => item.name || item.month);
        const data = chartData.map(item => item.count);
        const backgroundColors = chartData.map((_, index) => window.colorsUnique[index % window.colorsUnique.length]);

        const chartElement = document.getElementById(chartId);
        if (!chartElement) {
            console.error(`العنصر بالمعرف "${chartId}" غير موجود.`);
            return;
        }

        const ctx = chartElement.getContext('2d');
        let chart;
        if (chartType === 'doughnut') {
            chart = createDoughnutChart(ctx, labels, data, backgroundColors);
        } else if (chartType === 'bar') {
            chart = createBarChart(ctx, labels, data, backgroundColors);
        }

        // تعيين ألوان الشارات
        document.querySelectorAll(`[id^="badge-${badgePrefix}-"]`).forEach((badge, index) => {
            if (backgroundColors[index]) {
                badge.style.backgroundColor = backgroundColors[index];
            }
        });

        // دالة لتبديل رؤية البيانات في المخطط
        window[`toggle${capitalizeFirstLetter(badgePrefix)}`] = function(index) {
            const meta = chart.getDatasetMeta(0);
            meta.data[index].hidden = !meta.data[index].hidden;
            chart.update();

            const badge = document.querySelector(`#badge-${badgePrefix}-${index}`);
            if (badge) {
                badge.style.opacity = meta.data[index].hidden ? 0.5 : 1;
            }
        };

        // تهيئة الشارات
        labels.forEach((label, index) => {
            const badgeId = `badge-${badgePrefix}-${index}`;
            const badge = document.getElementById(badgeId);
            if (badge) {
                badge.addEventListener('click', function() {
                    window[`toggle${capitalizeFirstLetter(badgePrefix)}`](index);
                });
            }
        });

        // وظيفة تصدير الكارد
        const exportBtnId = `export${capitalizeFirstLetter(badgePrefix)}Btn`;
        const cardId = `${badgePrefix}Card`;
        const exportBtn = document.getElementById(exportBtnId);
        const card = document.getElementById(cardId);

        if (exportBtn && card) {
            exportBtn.addEventListener('click', function() {
                // إخفاء زر التصدير أثناء عملية التصدير
                exportBtn.style.display = 'none';
                exportBtn.disabled = true;

                // البدء في تصدير الكارد
                html2canvas(card, {
                    scale: 2
                }).then(canvas => {
                    // تحويل الـ canvas إلى صورة
                    const base64Image = canvas.toDataURL('image/png');

                    // إنشاء رابط لتنزيل الصورة
                    const link = document.createElement('a');
                    link.href = base64Image;
                    link.download = downloadFilename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    // إظهار زر التصدير مرة أخرى
                    exportBtn.style.display = 'flex';
                    exportBtn.disabled = false;

                }).catch(err => {
                    console.error(`خطأ في تصدير الكارد ${downloadFilename}:`, err);

                    exportBtn.style.display = 'flex';
                    exportBtn.disabled = false;
                });
            });
        } else {
            console.error(`زر التصدير أو الكارد بالمعرف "${exportBtnId}" أو "${cardId}" غير موجود.`);
        }

        return chart;
    }

    // دالة لتصغير الحرف الأول
    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    // تهيئة جميع المخططات
    initializeChart('sessionsByStatusChart', 'sessionsByStatusData', 'sessionsByStatus', 'الجلسات_حسب_الحالة.png');
    initializeChart('sessionsByImportanceChart', 'sessionsByImportanceData', 'sessionsByImportance', 'الجلسات_حسب_الأهمية.png');
    initializeChart('sessionsByTypeChart', 'sessionsByTypeData', 'sessionsByType', 'الجلسات_حسب_نوع_الجلسة.png');
    initializeChart('sessionsByEntityRankChart', 'sessionsByEntityRankData', 'sessionsByEntityRank', 'الجلسات_حسب_درجة_الجهة.png');
    initializeChart('sessionsByMonthChart', 'sessionsByMonthData', 'sessionsByMonth', 'الجلسات_حسب_شهر_الجلسة_هجري.png', 'bar');
    initializeChart('sessionsByAssignedUserChart', 'sessionsByAssignedUserData', 'sessionsByAssignedUser', 'الجلسات_حسب_المكلفين.png');

});
