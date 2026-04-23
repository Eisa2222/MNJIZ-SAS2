// public/js/datatable-settings.js

$(document).ready(function() {
    $('.datatable').DataTable({
        // إعدادات عامة يمكنك تخصيصها هنا
        pageLength: 5, // تعيين عدد الصفوف التي سيتم عرضها في الصفحة الواحدة
        lengthMenu: [5, 10, 25, 50, 75, 100],
        language: {
            search: 'بحث:',
            lengthMenu: "عرض _MENU_ مدخلات",
            paginate: {
                next: '<i class="ti ti-chevron-right ti-sm"></i>',
                previous: '<i class="ti ti-chevron-left ti-sm"></i>'
            },
            info: "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخلات",
            infoEmpty: "عرض 0 إلى 0 من أصل 0 مدخلات",
            infoFiltered: "(تمت التصفية من _MAX_ إجمالي المدخلات)",
            zeroRecords: "لم يتم العثور على سجلات مطابقة",
            emptyTable: "لا توجد بيانات متاحة في الجدول"
        },
        responsive: true, // تفعيل التوافقية مع الشاشات المختلفة
        deferRender: true, // تحسين الأداء عند تحميل عدد كبير من البيانات
        scrollY: "1000px", // تعيين ارتفاع الجدول لتفعيل التمرير العمودي
        scrollCollapse: true, // تفعيل طي الجدول عند تقليل عدد السجلات
        scroller: true // تفعيل التمرير المستمر عند تحميل البيانات بشكل تدريجي
    });
});