// resources/js/customersReport.js

document.addEventListener('DOMContentLoaded', function() {
    // بيانات المخطط الأول: العملاء حسب الحالة
    const Offers = window.charstatusData;
    const labelsOffers = Offers.map(cat => cat.name);
    const dataOffers = Offers.map(cat => cat.count);
    const colorsOffers = window.colorsUnique;
    const backgroundColorOffers = labelsOffers.map((_, index) => colorsOffers[index % colorsOffers.length]);

    // إنشاء المخطط الأول
    const ctxOffers = document.getElementById('statusChart').getContext('2d');
    const offersChart = new Chart(ctxOffers, {
        type: 'doughnut',
        data: {
            labels: labelsOffers,
            datasets: [{
                data: dataOffers,
                backgroundColor: backgroundColorOffers,
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

    // تعيين ألوان الـ badges بناءً على ألوان المخطط
    document.querySelectorAll('[id^="offersBadge"]').forEach((badge, index) => {
        badge.style.backgroundColor = backgroundColorOffers[index];
    });

    // دالة لتبديل رؤية البيانات في المخطط الأهمي
    window.toggleDatasetOffers = function(index) {
        const meta = offersChart.getDatasetMeta(0);
        meta.data[index].hidden = !meta.data[index].hidden;
        offersChart.update();

        const badge = document.querySelector(`#offersBadge${index}`);
        badge.style.opacity = meta.data[index].hidden ? 0.5 : 1;
    };

    // وظيفة تصدير المخطط الأول
    document.getElementById('exportStatusChartBtn').addEventListener('click', function() {
        const exportBtn = document.getElementById('exportStatusChartBtn');
        const card = document.getElementById('statusCard');

        // إخفاء زر التصدير وتعطيله أثناء عملية التصدير
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
            link.download = 'العملاء_حسب_الحالة.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // إظهار زر التصدير مرة أخرى
            exportBtn.style.display = 'flex'; // أو 'inline-flex' بناءً على التصميم
            exportBtn.disabled = false;

        }).catch(err => {
            console.error('خطأ في تصدير الكارد:', err);

            exportBtn.style.display = 'flex';
            exportBtn.disabled = false;
        });
    });

    // بيانات المخطط الثاني: العملاء حسب قنوات التسويق
    const marketingChannelChartData = window.charMarketingChannel;
    const labelsMarketingChannel = marketingChannelChartData.map(cat => cat.name);
    const dataMarketingChannel = marketingChannelChartData.map(cat => cat.count);
    const backgroundColorMarketingChannel = labelsMarketingChannel.map((_, index) => window.colorsUnique[index % window.colorsUnique.length]);

    // إنشاء المخطط الثاني
    const ctxMarketingChannel = document.getElementById('marketingChannelChart').getContext('2d');
    const marketingChannelChart = new Chart(ctxMarketingChannel, {
        type: 'doughnut',
        data: {
            labels: labelsMarketingChannel,
            datasets: [{
                data: dataMarketingChannel,
                backgroundColor: backgroundColorMarketingChannel,
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

    // تعيين ألوان الـ badges بناءً على ألوان المخطط الثاني
    document.querySelectorAll('[id^="marketingChannelChartBadge"]').forEach((badge, index) => {
        badge.style.backgroundColor = backgroundColorMarketingChannel[index];
    });

    // دالة لتبديل رؤية البيانات في المخطط الثاني
    window.toggleDatasetMarketingChannelChart = function(index) {
        const meta = marketingChannelChart.getDatasetMeta(0);
        meta.data[index].hidden = !meta.data[index].hidden;
        marketingChannelChart.update();

        const badge = document.querySelector(`#marketingChannelChartBadge${index}`);
        badge.style.opacity = meta.data[index].hidden ? 0.5 : 1;
    };

    // وظيفة تصدير المخطط الثاني
    document.getElementById('exportmarketingChartBtn').addEventListener('click', function() {
        const exportBtn = document.getElementById('exportmarketingChartBtn');
        const card = document.getElementById('marketingChannelCard');

        // إخفاء زر التصدير وتعطيله أثناء عملية التصدير
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
            link.download = 'العملاء_حسب_قنوات_التسويق.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // إظهار زر التصدير مرة أخرى
            exportBtn.style.display = 'flex'; // أو 'inline-flex' بناءً على التصميم
            exportBtn.disabled = false;

        }).catch(err => {
            console.error('خطأ في تصدير الكارد:', err);

            exportBtn.style.display = 'flex';
            exportBtn.disabled = false;
        });
    });

    // بيانات المخطط الثالث: توزيع الجنسيات
    const nationalityCtx = document.getElementById('nationalityChart').getContext('2d');
    const nationalityChart = new Chart(nationalityCtx, {
        type: 'bar', // يمكنك تغيير النوع إلى pie إذا رغبت
        data: {
            labels: window.nationalityLabels, // أسماء الجنسيات
            datasets: [{
                data: window.nationalityCounts, // عدد العملاء حسب الجنسية
                backgroundColor: window.colorsUnique, // ألوان القطاعات
                hoverOffset: 4 // التأثير عند التحويم على القطاعات
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1, // جعل المسافة بين العلامات 1
                        callback: function(value) {
                            return Number.isInteger(value) ? value : ''; // عرض الأرقام الصحيحة فقط
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false,
                }, // وضع التسمية أسفل المخطط
                title: {
                    display: false,
                    text: 'توزيع الجنسيات'
                } // عنوان المخطط
            }
        }
    });

    // وظيفة تصدير المخطط الثالث
    document.getElementById('exportnationalityChartBtn').addEventListener('click', function() {
        const exportBtn = document.getElementById('exportnationalityChartBtn');
        const card = document.getElementById('exportnationalityCard');

        // إخفاء زر التصدير وتعطيله أثناء عملية التصدير
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
            link.download = 'العملاء_حسب_الجنسيات.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // إظهار زر التصدير مرة أخرى
            exportBtn.style.display = 'flex'; // أو 'inline-flex' بناءً على التصميم
            exportBtn.disabled = false;

        }).catch(err => {
            console.error('خطأ في تصدير الكارد:', err);

            exportBtn.style.display = 'flex';
            exportBtn.disabled = false;
        });
    });

    // بيانات المخطط الرابع: القطاعات
    const sectorCtx = document.getElementById('sectorChart').getContext('2d');
    const sectorChart = new Chart(sectorCtx, {
        type: 'bar',
        data: {
            labels: window.sectorLabels,
            datasets: [{
                label: 'عدد العملاء',
                data: window.sectorCounts,
                backgroundColor: window.colorsUnique, // ألوان القطاعات
                hoverOffset: 4 // التأثير عند التحويم على القطاعات
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1, // جعل المسافة بين العلامات 1
                        callback: function(value) {
                            return Number.isInteger(value) ? value : ''; // عرض الأرقام الصحيحة فقط
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false,
                }, // وضع التسمية أسفل المخطط
                title: {
                    display: false,
                    text: 'توزيع القطاعات'
                } // عنوان المخطط
            }
        },
    });

    // وظيفة تصدير المخطط الرابع
    document.getElementById('exportsectorChartBtn').addEventListener('click', function() {
        const exportBtn = document.getElementById('exportsectorChartBtn');
        const card = document.getElementById('exportsectorCard');

        // إخفاء زر التصدير وتعطيله أثناء عملية التصدير
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
            link.download = 'العملاء_حسب_القطاعات.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // إظهار زر التصدير مرة أخرى
            exportBtn.style.display = 'flex'; // أو 'inline-flex' بناءً على التصميم
            exportBtn.disabled = false;

        }).catch(err => {
            console.error('خطأ في تصدير الكارد:', err);

            exportBtn.style.display = 'flex';
            exportBtn.disabled = false;
        });
    });

    // تهيئة DataTables
    // $('#customersTable').DataTable({
    //     responsive: true,
    //     language: {
    //         url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Arabic.json"
    //     },
    //     order: [
    //         [0, "desc"]
    //     ]
    // });
});
