
window.ApprovalConfig = {

    assets: {
        jsonPath: '/assets/json/',
        imagesPath: '/assets/img/',
    },

    dataTable: {
        language: {
            url: '/assets/json/ar.json'
        },
        pageLength: 25,
        responsive: true,
        processing: true,
        serverSide: true,
    },


    select2: {
        language: 'ar',
        dir: 'rtl',
        allowClear: true,
        width: '100%',
    },

    messages: {
        success: {
            approved: 'تم اعتماد الطلب بنجاح',
            rejected: 'تم رفض الطلب بنجاح',
            revoked: 'تم إلغاء الاعتماد بنجاح',
        },
        error: {
            general: 'حدث خطأ أثناء معالجة الطلب',
            permission: 'ليس لديك الصلاحية لتنفيذ هذا الإجراء',
            validation: 'البيانات المدخلة غير صحيحة',
            notFound: 'الطلب المطلوب غير موجود',
            server: 'خطأ في الخادم، يرجى المحاولة لاحقاً',
        },
        confirm: {
            approve: 'هل أنت متأكد من اعتماد هذا الطلب؟',
            reject: 'يرجى إدخال سبب الرفض',
            revoke: 'هل أنت متأكد من إلغاء اعتماد هذا الطلب؟',
        }
    }
};

window.routes = window.ApprovalConfig.routes;
window.assetPath = '/';


window.getMessage = function (type, key) {
    const messages = window.ApprovalConfig.messages[type];
    return messages ? messages[key] || '' : '';
};
