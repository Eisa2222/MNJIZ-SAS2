$(document).ready(function() {
    var selectedHijriDate = null;

    // تهيئة منتقي التاريخ الهجري
    $("#start_date").hijriDatePicker({
        hijri: true,
        showSwitcher: false,
        useCurrent: false,
        showClear: true,
    }).on('dp.change', function(e) {
        console.log('dp.change event fired');
        selectedHijriDate = e.date;
        formValidations[0].revalidateField('start_date');
    }).on('dp.clear', function(e) {
        console.log('dp.clear event fired');
        selectedHijriDate = null;
        formValidations[0].revalidateField('start_date');
    });

    // تحويل التاريخ الهجري إلى ميلادي قبل إرسال النموذج
    $('#power-form').on('submit', function(e) {
        if (selectedHijriDate) {
            var gregorianDate = selectedHijriDate.clone().locale('en').format('YYYY-MM-DD');
            // تحديث قيمة الحقل بالتاريخ الميلادي
            $('#start_date').val(gregorianDate);
        }
    });
});
