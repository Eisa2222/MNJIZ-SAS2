<?php

use App\Http\Controllers\Account_employee\AccountEmployeeComtroller;
use App\Http\Controllers\ApprovalWorkflow\Advances\AdvanceApprovalController;
use App\Http\Controllers\ApprovalWorkflow\ApprovalWorkflowController;
use App\Http\Controllers\ApprovalWorkflow\ClearanceCertificates\ClearanceCertificateApprovalController;
use App\Http\Controllers\ApprovalWorkflow\Content\ContentApprovalController;
use App\Http\Controllers\ApprovalWorkflow\Contracts\ContractApprovalController;
use App\Http\Controllers\ApprovalWorkflow\Custodies\CustodyRequestApprovalController;
use App\Http\Controllers\ApprovalWorkflow\Deductions\DeductionApprovalController;
use App\Http\Controllers\ApprovalWorkflow\LeaveRequests\LeaveRequestApprovalController;
use App\Http\Controllers\ApprovalWorkflow\Offers\OfferApprovalController;
use App\Http\Controllers\ApprovalWorkflow\Rewards\RewardApprovalController;
use App\Http\Controllers\ApprovalWorkflow\UnifiedApprovalController;
use App\Http\Controllers\ApprovalWorkflow\WpsPayrolls\WpsPayrollApprovalController;
use App\Http\Controllers\Hr\Attendance\AttendanceController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\Auth\MicrosoftController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\chat\ChatController;
use App\Http\Controllers\dashboard\DashboardController;
use App\Http\Controllers\ElectronicServices\CustodyRequests\EmployeeCustodyRequestController;
use App\Http\Controllers\ElectronicServices\EditRequest\EmployeeEditRequestController;
use App\Http\Controllers\ElectronicServices\LeaveRequests\EmployeeLeaveRequestController;
use App\Http\Controllers\ElectronicServices\PurchaseRequests\EmployeePurchaseRequestController;
use App\Http\Controllers\ElectronicServices\ViolationsPenalties\EmployeeViolationsController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\Financial\ContractPayment\ContractPaymentController;
use App\Http\Controllers\GeneralSetting\GeneralSettingController;
use App\Http\Controllers\GeneralSetting\HR\Custody\SettingsAssetCategoryController;
use App\Http\Controllers\GeneralSetting\HR\Custody\SettingsStorageLocationController;
use App\Http\Controllers\GeneralSetting\Marketing\SettingsCampaignSectionsController;
use App\Http\Controllers\GeneralSetting\Marketing\SettingsContentTypesController;
use App\Http\Controllers\GeneralSetting\Marketing\SettingsPublishingPatternsController;
use App\Http\Controllers\GeneralSetting\Marketing\SettingsContentPurposeController;
use App\Http\Controllers\GeneralSetting\Marketing\SettingsTargetAudienceController;
use App\Http\Controllers\GeneralSetting\SettingsBanksController;
use App\Http\Controllers\GeneralSetting\SettingsCategoriesController;
use App\Http\Controllers\GeneralSetting\SettingsClientStatusController;
use App\Http\Controllers\GeneralSetting\SettingsContractStatusController;
use App\Http\Controllers\GeneralSetting\SettingsCountryController;
use App\Http\Controllers\GeneralSetting\SettingsDepartmentContractCaseController;
use App\Http\Controllers\GeneralSetting\SettingsEntityRankController;
use App\Http\Controllers\GeneralSetting\SettingsHRClassificationController;
use App\Http\Controllers\GeneralSetting\SettingsHrStatusController;
use App\Http\Controllers\GeneralSetting\SettingsLawsuitsTypeController;
use App\Http\Controllers\GeneralSetting\SettingsLeaveTypeController;
use App\Http\Controllers\GeneralSetting\SettingsMainCourtController;
use App\Http\Controllers\GeneralSetting\SettingsMarketingChannelController;
use App\Http\Controllers\GeneralSetting\SettingsProductController;
use App\Http\Controllers\GeneralSetting\SettingsPurchaseCategoryController;
use App\Http\Controllers\GeneralSetting\SettingsRegionController;
use App\Http\Controllers\GeneralSetting\SettingsSectorController;
use App\Http\Controllers\GeneralSetting\SettingsSessionTypeController;
use App\Http\Controllers\GeneralSetting\SettingsSocialController;
use App\Http\Controllers\GeneralSetting\SettingsSubcategoriesController;
use App\Http\Controllers\GeneralSetting\SettingsTemplateController;
use App\Http\Controllers\GeneralSetting\SettingsTypeRulingsController;
use App\Http\Controllers\GeneralSetting\SettingsViolationCategoryController;
use App\Http\Controllers\GeneralSetting\SettingsViolationController;
use App\Http\Controllers\GeneralSetting\SystemSetting\SystemSettingsController;
use App\Http\Controllers\Hr\Advances\AdvanceController;
use App\Http\Controllers\Hr\Alerts\AlertController;
use App\Http\Controllers\Hr\Attendance\AttendanceViolationsController;
use App\Http\Controllers\Hr\Companypolicy\CompanypolicyController;
use App\Http\Controllers\Hr\Custody\Item\CustodyItemController;
use App\Http\Controllers\Hr\Custody\Request\CustodyRequestController;
use App\Http\Controllers\Hr\Deductions\DeductionController;
use App\Http\Controllers\Hr\EditRequest\EditRequestController;
use App\Http\Controllers\Hr\Employees\ArchiveEmployeesController;
use App\Http\Controllers\Hr\Employees\EmployeesController;
use App\Http\Controllers\Hr\Leave\LeaveBalanceController;
use App\Http\Controllers\reports\LeaveReportController;
use App\Http\Controllers\Hr\LeaveRequest\LeaveRequestController;
use App\Http\Controllers\Hr\Payrolls\WPS\WpsPayrollController;
use App\Http\Controllers\Hr\Payrolls\WPS\WpsPayrollDetailController;
use App\Http\Controllers\Hr\Payrolls\WPS\WpsPayrollRevisionController;
use App\Http\Controllers\Hr\Purchase\InvoiceController;
use App\Http\Controllers\Hr\Purchase\PurchaseRequestController;
use App\Http\Controllers\Hr\Rewards\RewardController;
use App\Http\Controllers\Hr\ViolationsPenalties\ViolationsPenaltiesController;
use App\Http\Controllers\LegalAffair\Lawsuit\LawsuitController;
use App\Http\Controllers\LegalAffair\Lawsuit\Memo\MemoController;
use App\Http\Controllers\LegalAffair\Lawsuit\Note\LawsuitNoteController;
use App\Http\Controllers\LegalAffair\Opponent\OpponentController;
use App\Http\Controllers\LegalAffair\PowerOfAttorney\PowerOfAttorneyController;
use App\Http\Controllers\LegalAffair\Session\Comments\SessionCommentController;
use App\Http\Controllers\LegalAffair\Session\SessionCompletion\SessionCompletionController;
use App\Http\Controllers\LegalAffair\Session\SessionController;
use App\Http\Controllers\LegalAI\AIChatController;
use App\Http\Controllers\LegalAI\DraftingController;
use App\Http\Controllers\LegalAI\ExportController;
use App\Http\Controllers\LegalAI\PrecedentController;
use App\Http\Controllers\LegalAI\SummarizeController;
use App\Http\Controllers\Marketing\CampaignManagement\CampaignManagementController;
use App\Http\Controllers\Marketing\CampaignManagement\CampaignResults\CampaignResultController;
use App\Http\Controllers\Marketing\ContentManagement\ContentManagementController;
use App\Http\Controllers\MassDeleteController;
use App\Http\Controllers\MeetingRoom\MeetingRoomController;
use App\Http\Controllers\MeetingRoom\PublicMeetingRoomController;
use App\Http\Controllers\MessageLogsController;
use App\Http\Controllers\microsoft\CalendarController;
use App\Http\Controllers\microsoft\EmailController;
use App\Http\Controllers\microsoft\OneDriveController;
use App\Http\Controllers\microsoft\TeamsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OperationsCenter\Contract\ContractController;
use App\Http\Controllers\OperationsCenter\Customer\CustomersController;
use App\Http\Controllers\OperationsCenter\ExceptionalContract\ExceptionalContractController;
use App\Http\Controllers\OperationsCenter\Offer\OfferController;
use App\Http\Controllers\OperationsCenter\Offer\OfferStudy\OfferTechnicalStudyController;
use App\Http\Controllers\OrganizationCenter\Tasks\TaskController;
use App\Http\Controllers\PolicyAgreement\PolicyAgreementController;
use App\Http\Controllers\ProjectManagement\ProjectController;
use App\Http\Controllers\Qoyod\Accounts\QAccountsController;
use App\Http\Controllers\Qoyod\Customers\QCustomersController;
use App\Http\Controllers\Qoyod\Inventories\QInventoriesController;
use App\Http\Controllers\Qoyod\Invoices\InvoicePayments\QInvoicePaymentsController;
use App\Http\Controllers\Qoyod\Invoices\QInvoicesController;
use App\Http\Controllers\Qoyod\JournalEntries\QJournalEntriesController;
use App\Http\Controllers\Qoyod\Notes\CreditNotes\QCreditNotesController;
use App\Http\Controllers\Qoyod\Notes\DebitNotes\QDebitNotesController;
use App\Http\Controllers\Qoyod\Products\Categories\QCategoriesController;
use App\Http\Controllers\Qoyod\Products\QProductsController;
use App\Http\Controllers\Qoyod\Products\Units\QUnitsController;
use App\Http\Controllers\Qoyod\Purchases\BillPayments\QBillPaymentsController;
use App\Http\Controllers\Qoyod\Purchases\Bills\QBillsController;
use App\Http\Controllers\Qoyod\Purchases\PurchaseOrders\QPurchaseOrdersController;
use App\Http\Controllers\Qoyod\Quotes\QQuotesController;
use App\Http\Controllers\Qoyod\Receipts\QReceiptsController;
use App\Http\Controllers\Qoyod\Test\QTestController;
use App\Http\Controllers\Qoyod\Vendors\QVendorsController;
use App\Http\Controllers\reports\AttendanceReportsController;
use App\Http\Controllers\reports\ReportsController;
use App\Http\Controllers\Self_services\ClearanceCertificateController;
use App\Http\Controllers\Self_services\SelfServicesController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\Survey\PublicSurveyController;
use App\Http\Controllers\Survey\SurveyController;
use App\Http\Controllers\SystemAdministration\ActivityLog\ActivityLogController;
use App\Http\Controllers\SystemAdministration\CredentialsManagement\ApprovalFlowController;
use App\Http\Controllers\SystemAdministration\Roles\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\whatsapp\WhatsAppController;
use Illuminate\Support\Facades\Route;


/*
|============================================================================
|============================================================================
|                            Start Public Routes
|============================================================================
|============================================================================
*/

Route::prefix('')->group(function () {

    Route::get('/{id}/public-profile', [AccountEmployeeComtroller::class, 'publicProfile'])->name('public-profile');
    Route::get('meeting-rooms-schedule/{meeting_rooms}', [PublicMeetingRoomController::class, 'show'])->whereIn('hall', ['big', 'small'])->name('public.meeting-rooms.show');


    Route::get('survey/{token}', [PublicSurveyController::class, 'show'])->name('survey.public');
    Route::post('survey/{token}', [PublicSurveyController::class, 'submit'])->name('survey.submit');


    Route::fallback(function () {
        return view('public-pages.not-found');
    });
});



/*
|============================================================================
|============================================================================
|                           Dashboard and Auth Routes
|============================================================================
|============================================================================
*/
Route::prefix('employees')->group(function () {

    Route::get('/', function () {
        return view('auth.login');
    });


    /*
    |--------------------------------------------------------------------------
    | dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified', 'policy.agreement'])->name('dashboard');




    // Microsoft SSO Authentication
    Route::prefix('auth/microsoft')->name('microsoft.')->group(function () {
        Route::get('login', [MicrosoftController::class, 'redirectToProvider'])->name('login');
        Route::get('callback', [MicrosoftController::class, 'handleProviderCallback'])->name('callback');
    });


    Route::middleware(['auth'])->group(function () {
        Route::prefix('policies')->name('policies.')->group(function () {
            Route::get('agreement', [PolicyAgreementController::class, 'index'])->name('agreement');
            Route::post('agree', [PolicyAgreementController::class, 'agree'])->name('agree');
        });
    });


    Route::middleware(['auth', 'active', 'policy.agreement'])->group(function () {

        Route::resource('meeting-rooms', MeetingRoomController::class)->parameters(['' => 'meeting_room']);

        // الاستبيانات
        Route::resource('surveys', SurveyController::class)->parameters(['' => 'survey']);

        Route::prefix('surveys')->name('surveys.')->group(function () {
            Route::patch('toggle-status/{survey}', [SurveyController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('{survey}/statistics', [SurveyController::class, 'statistics'])->name('statistics');
            Route::get('{survey}/question/{question}/details', [SurveyController::class, 'questionDetails'])->name('question.details');

            Route::resource('', SurveyController::class)->parameters(['' => 'survey']);
        });

        /*
        |--------------------------------------------------------------------------
        | حذف المحدد
        |--------------------------------------------------------------------------
        */
        Route::post('/mass-delete', [MassDeleteController::class, 'massDelete'])->name('mass.delete');


        /*
        |--------------------------------------------------------------------------
        | Password Management
        |--------------------------------------------------------------------------
        | لفتح شاشة تغيير كلمة المرور عند الدخول اول مرة
        */
        Route::prefix('password')->name('password.')->group(function () {
            Route::get('change', [PasswordChangeController::class, 'showChangeForm'])->name('change');
            Route::put('change', [PasswordChangeController::class, 'changePassword'])->name('change.post');
        });


        /*
        |--------------------------------------------------------------------------
        |   account of the employee
        |--------------------------------------------------------------------------
        */
        Route::prefix('account/employee')->name('account.employee.')->group(function () {
            // profile
            Route::get('/{id}/profile', [AccountEmployeeComtroller::class, 'profile'])->name('profile');
            Route::get('/{id}/profile/edit', [AccountEmployeeComtroller::class, 'edit'])->name('profile.edit');
            Route::post('/{id}/profile/store', [EmployeeEditRequestController::class, 'store'])->name('profile.store');
            Route::put('{id}/profile/update-profile-picture', [AccountEmployeeComtroller::class, 'updateProfilePicture'])->name('profile.picture.update');
            Route::put('{id}/profile/update-background-image', [AccountEmployeeComtroller::class, 'updateBackgroundImage'])->name('profile.background.update');

            // change password for employee
            Route::get('password/edit', [AccountEmployeeComtroller::class, 'showChangePasswordForm'])->name('password.edit');
            Route::post('/password/update', [AccountEmployeeComtroller::class, 'updatePassword'])->name('password.update');
        });






        // Route for fingerprints
        Route::post('users/{user}/fingerprints/capture', [UserController::class, 'captureFingerprint'])->name('users.fingerprints.capture');
        Route::get('fingerprints/{fingerprint}/edit', [UserController::class, 'editFingerprints'])->name('users.fingerprints.edit');
        Route::put('fingerprints/{fingerprint}', [UserController::class, 'updateFingerprints'])->name('users.fingerprints.update');
        Route::delete('fingerprints/{fingerprint}', [UserController::class, 'destroyFingerprints'])->name('users.fingerprints.destroy');


        Route::prefix('human-resources/attendances')->name('attendances.')->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('index');
            Route::get('/data', [AttendanceController::class, 'getData'])->name('data');
            Route::get('/{id}/edit', [AttendanceController::class, 'edit'])->name('edit');
            Route::put('/{id}', [AttendanceController::class, 'update'])->name('update');
            Route::get('/{id}/logs', [AttendanceController::class, 'show'])->name('logs');
            Route::get('/{id}/check-violation', [AttendanceController::class, 'checkViolation'])->name('check-violation');
            Route::post('attendance/check-in', [AttendanceController::class, 'check_in'])->name('check_in');
            Route::post('attendance/check-out', [AttendanceController::class, 'check_out'])->name('check_out');
            // violations
            Route::prefix('violations')->name('violations.')->group(function () {
                Route::get('/get', [AttendanceViolationsController::class, 'getViolations'])->name('get');
                Route::post('/create', [AttendanceViolationsController::class, 'store'])->name('create');
            });
        });

        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('get-employee-roles/{id}', [RoleController::class, 'getEmployeeRoles'])->name('getEmployeeRoles');
            Route::post('update-employee-roles/{id}', [RoleController::class, 'updateEmployeeRoles'])->name('updateEmployeeRoles');
            Route::patch('update-status/{employeeId}', [RoleController::class, 'updateStatus'])->name('updateStatus');
            Route::get('get-user-permissions/{user}', [RoleController::class, 'getUserPermissions'])->name('getUserPermissions');
            Route::post('update-user-permissions/{user}', [RoleController::class, 'updateUserPermissions'])->name('updateUserPermissions');
        });


        Route::resource('system-administration/roles', RoleController::class);




        /*
        |--------------------------------------------------------------------------
        | approval Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('system-administration/approval-flows')->name('approval-flows.')->group(function () {
            Route::get('/', [ApprovalFlowController::class, 'index'])->name('index');
            Route::post('/{flow}/add-level', [ApprovalFlowController::class, 'addLevel'])->name('add-level');
            Route::post('/{flow}/{level}', [ApprovalFlowController::class, 'approve'])->name('approve');
            Route::post('/{flow}/{level}/delete', [ApprovalFlowController::class, 'deleteLevel'])->name('delete-level');
        });


        /*
        |--------------------------------------------------------------------------
        | سجل النشاطات
        |--------------------------------------------------------------------------
        */
        Route::prefix('system-administration/activity-logs')->name('activity_logs.')->group(function () {
            Route::get('/', [ActivityLogController::class, 'index'])->name('index');
            Route::get('/{id}', [ActivityLogController::class, 'show'])->name('show');
        });


        /*
        |--------------------------------------------------------------------------
        | Message Logs Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('system-administration/message-logs')->name('messageLogs.')->group(function () {
            Route::get('/', [MessageLogsController::class, 'index'])->name('index');
            Route::get('{id}', [MessageLogsController::class, 'show'])->name('show');
            Route::get('{id}/recipients/{type}', [MessageLogsController::class, 'getRecipients'])->name('getRecipients');
        });



        // customers
        Route::prefix('operations-center/customers')->name('operations-center.customers.')->group(function () {
            Route::post('bulk-destroy', [CustomersController::class, 'bulkDestroy'])->name('bulkDestroy');
            Route::post('send-whatsapp', [CustomersController::class, 'sendWhatsapp'])->name('sendWhatsapp');
            Route::post('send-sms', [CustomersController::class, 'sendSms'])->name('sendSms');
            Route::get('trashed', [CustomersController::class, 'trashed'])->name('trashed');
            Route::put('{id}/restore', [CustomersController::class, 'restore'])->name('restore');
            Route::delete('{id}/forceDelete', [CustomersController::class, 'forceDelete'])->name('forceDelete');
            Route::get('{survey_response}/survey', [CustomersController::class, 'getSurvey'])->name('getSurvey');
            Route::resource('', CustomersController::class)->parameters(['' => 'customer']);
        });

        /*
        |============================================================================
        |============================================================================
        |                          Legal Affairs
        |============================================================================
        |============================================================================
        */
        // opponents
        Route::prefix('legal-affairs/opponents')->name('legal-affairs.opponents.')->group(function () {
            Route::post('send-sms', [OpponentController::class, 'sendSms'])->name('sendSms');
            Route::resource('', OpponentController::class)->parameters(['' => 'opponent']);
        });

        // الوكالات
        Route::prefix('legal-affairs/power-attorney')->name('legal-affairs.power-attorney.')->group(function () {
            Route::resource('', PowerOfAttorneyController::class)->parameters(['' => 'power-attorney']);
            Route::put('{id}/update-status', [PowerOfAttorneyController::class, 'updateStatus'])->name('update-status');
        });

        // الدعاوى
        Route::prefix('legal-affairs/lawsuits')->name('legal-affairs.lawsuits.')->group(function () {
            Route::resource('', LawsuitController::class)->parameters(['' => 'lawsuit']);

            Route::get('toggle-status/{id}', [LawsuitController::class, 'toggleStatus'])->name('toggleStatus'); // لتغيير حالة الدعوى

            // التصنيفات
            Route::get('get-subcategories/{categoryId}', [LawsuitController::class, 'getSubcategories'])->name('getSubcategories');
            Route::get('get-lawsuit-types/{subcategoryId}', [LawsuitController::class, 'getLawsuitTypes'])->name('getLawsuitTypes');


            // legal-affairs.lawsuits.lawsuit-section.
            Route::prefix('lawsuit-section/{lawsuit}')->name('lawsuit-section.')->group(function () {

                // موضوع الدعوى الخاصة بالقضية
                Route::put('/lawsuit_subject', [LawsuitController::class, 'lawsuit_subject'])->name('subject-update');

                // المذكرات
                Route::resource('memos', MemoController::class)->only(['store', 'update', 'destroy']);

                // الطلبات
                Route::put('/requests', [LawsuitController::class, 'requests'])->name('requests');

                // القرارات
                Route::put('/decisions', [LawsuitController::class, 'decisions'])->name('decisions');

                // المرفقات
                Route::post('/attachments', [LawsuitController::class, 'storeAttachments'])->name('attachments-store');

                // الملاحظات
                Route::prefix('notes')->name('notes.')->group(function () {
                    Route::post('store', [LawsuitNoteController::class, 'store'])->name('store');
                    Route::delete('{note}', [LawsuitNoteController::class, 'destroy'])->name('destroy');
                    Route::get('{note}', [LawsuitNoteController::class, 'show'])->name('show');
                    Route::put('{note}', [LawsuitNoteController::class, 'update'])->name('update');


                    // الردود
                    Route::post('{note}/replies', [LawsuitNoteController::class, 'storeReply'])->name('replies.store');
                    Route::delete('{note}/replies/{reply}', [LawsuitNoteController::class, 'destroyReply'])->name('replies.destroy');
                    Route::get('/{note}/replies/{reply}', [LawsuitNoteController::class, 'showReply'])->name('replies.show');
                    Route::put('{note}/replies/{reply}', [LawsuitNoteController::class, 'updateReply'])->name('replies.update');

                    // مسار لتحديث الملاحظة
                });
            });

            // لعرض مرفق الحكم
            Route::get('show-file/{filePath}', [LawsuitController::class, 'showFile'])->where('filePath', '.*')->name('show-file');


            // لحذ المرفقات الاضافية في الدعاوي
            Route::delete('/attachments/{id}', [LawsuitController::class, 'attachmentsDestroy'])->name('attachments-destroy');
            // لحذف مرفقات الدعاوى الخاصة بالدعوى
            Route::delete('/this_attachments/{id}', [LawsuitController::class, 'thisAttachmentsDestroy'])->name('attachments-delete');








            Route::get('trashed', [LawsuitController::class, 'trashed'])->name('trashed');




            // راوت خاص لاعتمادات المشاريع - استكمال المشاريع
            // Route::get('lawsuits/approvals', [LawsuitController::class, 'approvals'])->name('lawsuits.approvals');
            // عرض الدعاوى المحذوفةss
            // استعادة دعوى محذوفة
            Route::put('lawsuits/{id}/restore', [LawsuitController::class, 'restore'])->name('lawsuits.restore');
            // حذف دعوى نهائيًا
            Route::delete('lawsuits/{id}/forceDelete', [LawsuitController::class, 'forceDelete'])->name('lawsuits.forceDelete');

            // جلب البيانات للمهام
            Route::get('lawsuits/{id}/tasks', [LawsuitController::class, 'getLawsuitTask']);


            // جلب المدعى عليهم
            Route::post('/get-defendants', [LawsuitController::class, 'getDefendants'])->name('getDefendants');






            // أطراف الدعوى
            Route::get('/lawsuit/{lawsuit}/lawsuit_parties', [LawsuitController::class, 'lawsuitParties'])
                ->name('lawsuit_section.lawsuit_parties');
            // أطراف الدعوى
            Route::get('/lawsuit/{lawsuit}/sessions', [LawsuitController::class, 'getSessions'])
                ->name('lawsuit_section.sessions');

            // الأحكام
            Route::get('/lawsuit/{lawsuit}/judgments', [LawsuitController::class, 'judgments'])
                ->name('lawsuit_section.judgments');

            Route::put('/lawsuit/{lawsuit}/judgments', [LawsuitController::class, 'judgments'])
                ->name('lawsuit_section.judgments.update');





            // المرفقات
            Route::get('/lawsuit/{lawsuit}/attachments', [LawsuitController::class, 'attachments'])
                ->name('lawsuit_section.attachments');


            // اضافة مرفقات جديدة للدعوى


            //منشن على الملاحظات
            Route::get('/lawsuit/{lawsuit}/notes/{note}/{comment}', [LawsuitController::class, 'show_note_notification'])
                ->name('show.note.notification');


            // Route::post('/lawsuits/{lawsuit}/notes', [LawsuitNoteController::class, 'store'])->name('lawsuits.notes.store');

            // مسار لتخزين ملاحظة جديدة

            //لعرض الملاحظات و التعليقات التي فيها

            // مسار لإضافة رد جديد إلى الملاحظة

            // مسار لحذف الملاحظة


            //  مهام الدعوى
            Route::get('/lawsuit/{lawsuit}/tasks', [LawsuitController::class, 'getTasks'])
                ->name('lawsuit_section.tasks');




            // لاعتماد المكلفين
            // عرض المكلفين للدعوى
            Route::get('lawsuits/{lawsuit}/assigned-employees', [LawsuitController::class, 'assignedEmployeesPage'])->name('lawsuits.assignedEmployees');

            // قبول المكلفين المحددين عبر Ajax
            Route::post('lawsuits/{lawsuit}/assigned-employees/accept', [LawsuitController::class, 'acceptAssignedEmployees'])->name('lawsuits.acceptAssignedEmployees');

            // رفض المكلفين المحددين عبر Ajax
            Route::post('lawsuits/{lawsuit}/assigned-employees/reject', [LawsuitController::class, 'rejectAssignedEmployees'])->name('lawsuits.rejectAssignedEmployees');
        });

        // الجلسات
        Route::prefix('legal-affairs/sessions')->name('legal-affairs.sessions.')->group(function () {
            Route::get('get-lawsuits', [SessionController::class, 'getLawsuits'])->name('get-lawsuits');
            Route::get('get-lawsuit-details', [SessionController::class, 'getLawsuitDetails'])->name('get-lawsuit-details');

            Route::resource('', SessionController::class)->parameters(['' => 'session']);

            // اكمال ضبط الجلسة
            Route::prefix('session-completion')->name('session-completion.')->group(function () {
                Route::get('{session}/completion', [SessionCompletionController::class, 'completion'])->name('completion');
                Route::put('{session}/store', [SessionCompletionController::class, 'store'])->name('store');
            });

            // للاعتراض
            Route::post('{session}/objection', [SessionController::class, 'objection'])->name('objection');

            // التعليقات للجلسات
            Route::prefix('{session}/comments')->name('comments.')->group(function () {
                Route::resource('', SessionCommentController::class)->parameters(['' => 'comment']);
            });





            // Route::get('notification/sessions/{lawsuit}/{session}/{comment}', [SessionController::class, 'show_session_notification'])->name('show.session.notification');
            // Route::get('/sessions/{session}/comments', [SessionCommentController::class, 'index'])->name('sessions.comments.index');
            // Route::post('/sessions/{session}/comments', [SessionCommentController::class, 'store'])->name('sessions.comments.store');
            // Route::get('/sessions/{session}/comments/{comment}', [SessionCommentController::class, 'show'])->name('sessions.comments.show');
            // Route::put('/sessions/{session}/comments/{comment}', [SessionCommentController::class, 'update'])->name('sessions.comments.update');
            // Route::delete('/sessions/{session}/comments/{comment}', [SessionCommentController::class, 'destroy'])->name('sessions.comments.destroy');

            // Route::post('/close-lawsuit', [SessionController::class, 'closeLawsuit'])->name('lawsuit.close');
            Route::get('/chat/{with}', [SessionController::class, 'chatWith'])->name('chat.with');


            // يتحتاج للمراجعة
            Route::get('trashed', [SessionController::class, 'trashed'])->name('trashed');
            Route::put('{id}/restore', [SessionController::class, 'restore'])->name('restore');
            Route::delete('{id}/forceDelete', [SessionController::class, 'forceDelete'])->name('forceDelete');
        });


        /*
        |============================================================================
        |============================================================================
        |                                 Offers
        |============================================================================
        |============================================================================
        */
        Route::prefix('operations-center/offers')->name('operations-center.offers.')->group(function () {
            Route::get('{offer}/export-pdf-official', [OfferController::class, 'exportPdfOfficial'])->name('export-pdf.official');
            Route::get('{offer}/export-pdf-simple', [OfferController::class, 'exportPdfSimple'])->name('export-pdf.simple');
            Route::get('trashed', [OfferController::class, 'trashed'])->name('trashed');
            Route::put('{offer}/restore', [OfferController::class, 'restore'])->name('restore');
            Route::delete('{offer}/forceDelete', [OfferController::class, 'forceDelete'])->name('forceDelete');
            Route::get('/get-relationship-manager/{customer}', [OfferController::class, 'relationship_manager'])->name('get-relationship-manager');
            Route::resource('', OfferController::class)->parameters(['' => 'offer']);


            Route::prefix('offer-study')->name('offer-study.')->group(function () {
                // operations-center.offers.offer-study.technical-study.
                Route::get('technical-study/create/{id}', [OfferTechnicalStudyController::class, 'create'])->name('technical-study.create');
                Route::post('technical-study/store/{id}', [OfferTechnicalStudyController::class, 'store'])->name('technical-study.store');
                Route::resource('technical-study', OfferTechnicalStudyController::class)
                    ->except(['create', 'store'])
                    ->parameters(['' => 'offer']);
            });
        });

        // Contracts
        Route::prefix('operations-center/contracts')->name('operations-center.contracts.')->group(function () {
            Route::get('{contract}/export-pdf', [ContractController::class, 'exportPdf'])->name('export-pdf');

            Route::get('{contract}/export-pdf-official', [ContractController::class, 'exportPdfOfficial'])->name('export-pdf.official');
            Route::get('{contract}/export-pdf-simple', [ContractController::class, 'exportPdfSimple'])->name('export-pdf.simple');

            Route::get('trashed', [ContractController::class, 'trashed'])->name('trashed');
            Route::put('{id}/restore', [ContractController::class, 'restore'])->name('restore');
            Route::delete('{id}/forceDelete', [ContractController::class, 'forceDelete'])->name('forceDelete');
            Route::get('get-offer-details/{id}', [ContractController::class, 'getOfferDetails'])->name('getOfferDetails');
            Route::get('get-main-contract-details/{id}', [ContractController::class, 'getMainContractDetails'])->name('getMainContractDetails');
            Route::resource('', ContractController::class)->parameters(['' => 'contract']);
        });


        // Exceptional Contracts
        Route::prefix('operations-center')->name('operations-center.')->group(function () {

            Route::prefix('exceptional-contracts')->name('exceptional-contracts.')->group(function () {
                Route::get('{exceptional_contract}/export-pdf-official', [ExceptionalContractController::class, 'exportPdfOfficial'])->name('export-pdf.official');
                Route::get('{exceptional_contract}/export-pdf-simple', [ExceptionalContractController::class, 'exportPdfSimple'])->name('export-pdf.simple');

                Route::post('{exceptional_contract}/approve', [ExceptionalContractController::class, 'approve'])->name('approve');
                Route::post('{exceptional_contract}/reject', [ExceptionalContractController::class, 'reject'])->name('reject');


                Route::resource('', ExceptionalContractController::class)->parameters(['' => 'exceptional_contract']);
            });
        });


        // الموارد البشرية
        Route::prefix('human-resources')->name('hr.')->group(function () {

            // اللوائح والسياسات
            Route::prefix('company-policy')->name('company-policy.')->group(function () {
                Route::resource('', CompanypolicyController::class)->parameters(['' => 'company_policy']);
            });


            // الموظفين
            Route::prefix('employees')->name('employees.')->group(function () {
                // Soft delete operations
                Route::get('trashed', [EmployeesController::class, 'trashed'])->name('trashed');

                Route::put('{id}/restore', [EmployeesController::class, 'restore'])->name('restore');

                Route::delete('{id}/forceDelete', [EmployeesController::class, 'forceDelete'])->name('forceDelete');

                Route::post('send-sms', [EmployeesController::class, 'sendSms'])->name('sendSms');

                Route::post('{id}/send-password-reset', [EmployeesController::class, 'sendPasswordResetFirstNotification'])->name('send-password-reset');

                Route::get('{id}/download-attachments', [EmployeesController::class, 'downloadAttachments'])->name('downloadAttachments');

                Route::put('{id}/archive', [EmployeesController::class, 'archive'])->name('archive');


                Route::resource('/', EmployeesController::class)->except('show')->names([
                    'index'     => 'index',
                    'create'    => 'create',
                    'store'     => 'store',
                    'edit'      => 'edit',
                    'update'    => 'update',
                    'destroy'   => 'destroy'
                ])->parameters(['' => 'employee']);

                // Routes بطاقات العمل
                Route::get('/{employee}/business-card', [EmployeesController::class, 'showBusinessCard'])->name('business-card');
                Route::get('/{employee}/business-card/pdf', [EmployeesController::class, 'downloadBusinessCardPDF'])->name('business-card.pdf');
                Route::get('/{employee}/business-card/preview', [EmployeesController::class, 'previewBusinessCard'])->name('business-card.preview');

                // Arcchive employees
                Route::resource('archive', ArchiveEmployeesController::class)->parameters(['' => 'employee']);
                Route::put('archive/{id}/restore', [ArchiveEmployeesController::class, 'restore'])->name('archive.restore');
            });

            // الاجازات
            Route::prefix('leave-requests')->name('leave-requests.')->group(function () {
                Route::get('', [LeaveRequestController::class, 'index'])->name('index');
                Route::get('{id}', [LeaveRequestController::class, 'show'])->name('show');
            });

            // الانتهاكات
            Route::prefix('violations-penalties')->name('violations-penalties.')->group(function () {
                Route::get('get-occurrence-count', [ViolationsPenaltiesController::class, 'getOccurrenceCount'])->name('get-occurrence-count');
                Route::get('/{id}/details', [ViolationsPenaltiesController::class, 'details'])->name('details');

                Route::resource('', ViolationsPenaltiesController::class)->parameters(['' => 'violation']);

                Route::post('apply-penalty/{violation}', [ViolationsPenaltiesController::class, 'applyPenalty'])->name('apply-penalty');
                Route::post('cancel-penalty/{violation}', [ViolationsPenaltiesController::class, 'cancelPenalty'])->name('cancel-penalty');
                Route::post('/appeals/{id}/respond', [ViolationsPenaltiesController::class, 'respondToAppeal'])->name('appeal.respond');
            });

            // مسيرات الرواتب
            Route::prefix('payrolls/wps')->name('payrolls.wps.')->group(function () {
                Route::get('', [WpsPayrollController::class, 'index'])->name('index');
                Route::post('/generate', [WpsPayrollController::class, 'generateWpsPayroll'])->name('generate');
                Route::get('{wps_payroll}/approve', [WpsPayrollController::class, 'approve'])->name('approve'); // تصديق المسير
                Route::get('{wps_payroll}/export-excel', [WpsPayrollController::class, 'export_excel'])->name('export_excel');


                // تفاصيل مسيرات الرواتب
                Route::prefix('{wps_payroll}/details')->name('details.')->group(function () {
                    Route::get('employee/{employee}', [WpsPayrollDetailController::class, 'show'])->name('show');
                    Route::get('employee/{employee}/export-pdf', [WpsPayrollDetailController::class, 'export_pdf'])->name('export_pdf');
                    Route::resource('', WpsPayrollDetailController::class)->only(['index']);

                    // revision
                    Route::prefix('revision/employee')->name('revision.')->group(function () {
                        Route::get('{employee}', [WpsPayrollRevisionController::class, 'index'])->name('index');
                        Route::get('{employee}/create', [WpsPayrollRevisionController::class, 'create'])->name('create');
                        Route::post('{employee}/store', [WpsPayrollRevisionController::class, 'store'])->name('store');
                        Route::get('{employee}/show', [WpsPayrollRevisionController::class, 'show'])->name('show');
                        Route::get('{employee}/edit/{revision}/', [WpsPayrollRevisionController::class, 'edit'])->name('edit');
                        Route::put('{employee}/{revision}/update', [WpsPayrollRevisionController::class, 'update'])->name('update');
                        Route::delete('{employee}/destroy/{revision}', [WpsPayrollRevisionController::class, 'destroy'])->name('destroy');
                    });
                });
            });

            // ارصدة الاجازات
            Route::prefix('leave-balances')->name('leave-balances.')->group(function () {
                Route::get('', [LeaveBalanceController::class, 'index'])->name('index');
                // Route::get('{balance}/edit', [LeaveBalanceController::class, 'edit'])->name('edit');
                // Route::put('{balance}', [LeaveBalanceController::class, 'update'])->name('update');
                Route::get('{balance}', [LeaveBalanceController::class, 'show'])->name('show');
            });

            // السلف
            Route::prefix('advances')->name('advances.')->group(function () {
                Route::resource('', AdvanceController::class)->parameters(['' => 'advance']);
            });

            // المكافأت
            Route::prefix('rewards')->name('rewards.')->group(function () {
                Route::resource('', RewardController::class)->parameters(['' => 'reward']);
            });

            // الخصومات
            Route::prefix('deductions')->name('deductions.')->group(function () {
                Route::resource('', DeductionController::class)->parameters(['' => 'deduction']);
            });

            // العهد
            Route::prefix('custody')->name('custody.')->group(function () {
                // الاصول
                Route::prefix('items')->name('items.')->group(function () {
                    Route::get('/filter', [CustodyItemController::class, 'filter'])->name('filter');

                    Route::resource('', CustodyItemController::class)->parameters(['' => 'item']);
                    Route::post('assign', [CustodyItemController::class, 'assign_item'])->name('assign');
                    Route::post('return', [CustodyItemController::class, 'return_item'])->name('return');
                });

                // طلبات العهد
                Route::prefix('requests')->name('requests.')->group(function () {
                    Route::get('', [CustodyRequestController::class, 'index'])->name('index');


                    // طلب عهدة جديدة
                    Route::get('assign/create',   [CustodyRequestController::class, 'assign_create'])->name('assign.create');
                    Route::post('assign',         [CustodyRequestController::class, 'assign_store'])->name('assign.store');

                    Route::get('assign/{id}/edit',   [CustodyRequestController::class, 'assign_edit'])->name('assign.edit');
                    Route::put('assign/{id}',        [CustodyRequestController::class, 'assign_update'])->name('assign.update');

                    // طلب إرجاع عهدة
                    Route::get('return/create',   [CustodyRequestController::class, 'return_create'])->name('return.create');
                    Route::post('return',         [CustodyRequestController::class, 'return_store'])->name('return.store');

                    // جلب طلبات العهدة المعتمدة لموظف معين
                    Route::get('approved-requests', [CustodyRequestController::class, 'approvedRequests'])->name('return.approvedRequests');


                    Route::get('return/{id}/edit',   [CustodyRequestController::class, 'return_edit'])->name('return.edit');
                    Route::put('return/{id}',        [CustodyRequestController::class, 'return_update'])->name('return.update');

                    Route::get('{request}', [CustodyRequestController::class, 'show'])->name('show');
                    Route::delete('{custody_request}', [CustodyRequestController::class, 'destroy'])->name('destroy');
                });
            });


            // طلبات تعديل الملف الشخصي
            Route::prefix('modification/requests')->name('modification-requests.')->group(function () {
                Route::get('', [EditRequestController::class, 'index'])->name('index');
                Route::get('{request}', [EditRequestController::class, 'show'])->name('show');

                Route::post('fields/{field}/approve', [EditRequestController::class, 'approveField'])->name('fields.approve');
                Route::post('fields/{field}/reject', [EditRequestController::class, 'rejectField'])->name('fields.reject');
            });

            // التنبيهات
            Route::prefix('alerts')->name('alerts.')->group(function () {
                Route::get('', [AlertController::class, 'index'])->name('index');
                Route::patch('{id}/toggle-status', [AlertController::class, 'toggleStatus'])->name('toggle-status');

                // Route::post('fields/{field}/approve', [AlertController::class, 'approveField'])->name('fields.approve');
                // Route::post('fields/{field}/reject', [AlertController::class, 'rejectField'])->name('fields.reject');
            });
        });



        /*
        |============================================================================
        |============================================================================
        |                            Finances
        |============================================================================
        |============================================================================
        */
        Route::prefix('financial')->name('financial.')->group(function () {

            Route::prefix('contract-payments')->name('contract-payments.')->group(function () {
                Route::get('', [ContractPaymentController::class, 'index'])->name('index');
                Route::get('{contract}', [ContractPaymentController::class, 'show'])->name('show');

                Route::put('{contract}/{payment}/pay', [ContractPaymentController::class, 'pay'])->name('pay');
                Route::put('{contract}/{payment}/cancel', [ContractPaymentController::class, 'cancel'])->name('cancel');
            });
        });


        /*
        |============================================================================
        |============================================================================
        |                            Marketing
        |============================================================================
        |============================================================================
        */
        Route::prefix('marketing')->name('marketing.')->group(function () {
            Route::prefix('content-management')->name('content-management.')->group(function () {
                Route::resource('', ContentManagementController::class)->parameters(['' => 'content_management']);
            });

            Route::prefix('campaign-management')->name('campaign-management.')->group(function () {
                // Main campaign routes
                Route::resource('/', CampaignManagementController::class)->parameters(['' => 'campaign_management']);

                // Campaign results nested routes
                Route::prefix('{campaign_management}/campaign-results')->name('campaign-results.')->group(function () {

                    Route::get('/create', [CampaignResultController::class, 'create'])->name('create');
                    Route::post('/', [CampaignResultController::class, 'store'])->name('store');
                });
            });
        });




        /*
        |--------------------------------------------------------------------------
        |                   الخدمات الالكترونية
        |--------------------------------------------------------------------------
        */
        Route::prefix('account/electronic-services')->name('account.electronic-services.')->group(function () {

            //=====================================================================
            //          طلبات المشتريات للموظف
            //=====================================================================
            Route::resource('purchase-requests', EmployeePurchaseRequestController::class)
                ->names([
                    'index'     => 'purchase-requests.index',
                    'create'    => 'purchase-requests.create',
                    'store'     => 'purchase-requests.store',
                    'show'      => 'purchase-requests.show',
                    'edit'      => 'purchase-requests.edit',
                    'update'    => 'purchase-requests.update',
                    'destroy'   => 'purchase-requests.destroy'
                ]);


            //=====================================================================
            //          طلبات الإجازات للموظف
            //=====================================================================

            Route::prefix('leave-requests')->name('leave-requests.')->group(function () {
                Route::get('/', [EmployeeLeaveRequestController::class, 'index'])->name('index');
                Route::get('/create', [EmployeeLeaveRequestController::class, 'create'])->name('create');
                Route::post('/', [EmployeeLeaveRequestController::class, 'store'])->name('store');
                Route::get('/{id}', [EmployeeLeaveRequestController::class, 'show'])->name('show');
                Route::get('/{id}/edit', [EmployeeLeaveRequestController::class, 'edit'])->name('edit');
                Route::put('/{id}', [EmployeeLeaveRequestController::class, 'update'])->name('update');
                Route::delete('/{id}', [EmployeeLeaveRequestController::class, 'destroy'])->name('destroy');

                // Mass Delete Route
                Route::post('/mass-delete', [EmployeeLeaveRequestController::class, 'massDelete'])->name('massDelete');
            });


            //=====================================================================
            //          الانتهاكات الخاصة بالموظف
            //=====================================================================
            Route::prefix('violations-penalties')->name('violations-penalties.')->group(function () {
                Route::get('', [EmployeeViolationsController::class, 'index'])->name('index');
                Route::get('{violation}', [EmployeeViolationsController::class, 'show'])->name('show');
                Route::post('{violation}/appeal', [EmployeeViolationsController::class, 'submitAppeal'])->name('appeal');
            });


            // طلبات العهد
            Route::prefix('custody-requests')->name('custody-requests.')->group(function () {
                Route::get('/filter', [EmployeeCustodyRequestController::class, 'filter'])->name('filter');

                Route::get('', [EmployeeCustodyRequestController::class, 'index'])->name('index');

                // طلب عهدة جديدة
                Route::get('assign/create',   [EmployeeCustodyRequestController::class, 'assign_create'])->name('assign.create');
                Route::post('assign',         [EmployeeCustodyRequestController::class, 'assign_store'])->name('assign.store');

                Route::get('assign/{id}/edit',   [EmployeeCustodyRequestController::class, 'assign_edit'])->name('assign.edit');
                Route::put('assign/{id}',        [EmployeeCustodyRequestController::class, 'assign_update'])->name('assign.update');

                // طلب إرجاع عهدة
                Route::get('return/create',   [EmployeeCustodyRequestController::class, 'return_create'])->name('return.create');
                Route::post('return',         [EmployeeCustodyRequestController::class, 'return_store'])->name('return.store');

                Route::get('return/{id}/edit',   [EmployeeCustodyRequestController::class, 'return_edit'])->name('return.edit');
                Route::put('return/{id}',        [EmployeeCustodyRequestController::class, 'return_update'])->name('return.update');

                Route::get('{custody_request}', [EmployeeCustodyRequestController::class, 'show'])->name('show');
                Route::delete('{custody_request}', [EmployeeCustodyRequestController::class, 'destroy'])->name('destroy');
            });
        });


        /*
        |--------------------------------------------------------------------------
        |  الخدمات الذاتية
        |--------------------------------------------------------------------------
        */
        Route::prefix('account/self-services')->name('account.self-services.')->group(function () {
            // تعريف بالراتب
            Route::get('salary-definition', [SelfServicesController::class, 'salary_definition_index'])->name('salary-definition.index');
            Route::post('salary-definition', [SelfServicesController::class, 'salary_definition_submit'])->name('salary-definition.submit');

            // تثبيت راتب
            Route::get('salary-fixation', [SelfServicesController::class, 'salary_fixation_index'])->name('salary-fixation.index');
            Route::post('salary-fixation', [SelfServicesController::class, 'salary_fixation_submit'])->name('salary-fixation.submit');

            // تقرير تدريب
            Route::get('training-certificate', [SelfServicesController::class, 'training_certificate_index'])->name('training-certificate.index');
            Route::post('training-certificate', [SelfServicesController::class, 'training_certificate_submit'])->name('training-certificate.submit');

            // إخلاء الطرف
            Route::resource('clearance-certificate', ClearanceCertificateController::class)->except(['show']);
            Route::get('clearance-certificate/export-pdf', [ClearanceCertificateController::class, 'exportPdf'])->name('clearance-certificate.exportPdf');
        });



        /*
        |--------------------------------------------------------------------------
        | مركز المشتريات
        |--------------------------------------------------------------------------
        */
        Route::prefix('purchasing-center/purchase-requests')->name('purchasing-center.purchase-requests.')->group(function () {
            // طلبات المشتريات
            Route::get('', [PurchaseRequestController::class, 'index'])->name('index');

            Route::post('update-status', [PurchaseRequestController::class, 'updateStatus'])->name('update-status');

            //  المشتريات - الفواتير
            Route::get('invoices/pdf/create-by-item/{id}', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
            Route::get('invoices/create-by-item/{id}', [InvoiceController::class, 'createByItem'])->name('invoices.createByItem');
            Route::post('invoices/create-by-item/{id}', [InvoiceController::class, 'storeByItem'])->name('invoices.storeByItem');
            Route::resource('invoices', InvoiceController::class);
        });




        // API
        Route::prefix('get')->name('get.')->group(function () {
            Route::get('projects/json', [ProjectController::class, 'getProjectsJson'])->name('projects.json');
            Route::get('lawsuits/json', [LawsuitController::class, 'getLawsuitsJson'])->name('lawsuits.json');
        });



        // chat
        Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
        Route::get('/messages/{id}', [ChatController::class, 'getMessages'])->name('messages');
        Route::post('/messages', [ChatController::class, 'sendMessage'])->name('message.send');

        // راوت خاص لاعتمادات المشاريع - استكمال المشاريع
        Route::get('projects/approvals', [ProjectController::class, 'approvals'])->name('projects.approvals');
        // عرض المشاريع المحذوفة
        Route::get('projects/trashed', [ProjectController::class, 'trashed'])->name('projects.trashed');
        // استعادة مشروع محذوف
        Route::put('projects/{id}/restore', [ProjectController::class, 'restore'])->name('projects.restore');
        // حذف مشروع نهائيًا
        Route::delete('projects/{id}/forceDelete', [ProjectController::class, 'forceDelete'])->name('projects.forceDelete');
        Route::resource('projects', ProjectController::class);
        // لاكمال المشروع
        Route::get('/projects/{id}/complete', [ProjectController::class, 'complete'])->name('projects.complete');
        Route::put('/projects/{id}/complete', [ProjectController::class, 'completeStore'])->name('projects.complete.update');

        Route::put('/projects/{id}/update-status', [ProjectController::class, 'updateStatus'])->name('projects.updateStatus');

        // التحقق  هل هناك دعاوى نشطة داخل المشروه
        Route::get('/projects/{id}/check-active-cases', [ProjectController::class, 'checkActiveCases']);

        // جلب فريق المشروع
        Route::get('/projects/{id}/team-members', [ProjectController::class, 'getTeamMembers'])->name('projects.team-members');


        // لجلب تاريخ البدء و الاغلاق التعاقدي بعد اختباري العقد
        Route::get('/contracts/{id}/detils', [ProjectController::class, 'getContractDetails']);
        // دعاوى المشروع
        Route::get('/projects/{project}/lawsuits', [ProjectController::class, 'getLawsuits'])->name('projects.lawsuits');

        // دعاوى المشروع json
        Route::get('/projects/{projectId}/lawsuits/json', [ProjectController::class, 'getRelatedLawsuits']);

        // فريق المشروع
        Route::get('/projects/{project}/teams', [ProjectController::class, 'getTeams'])->name('projects.teams');
        // انشاء خطة للمشروع
        Route::get('/projects/{project_name}/{project_id}/{type_send}/create_plan', [ProjectController::class, 'createPlannerPlan'])->name('projects.createPlannerPlan');
        // جلب مهام المشروع
        Route::get('/project/{project_id}/tasks', [ProjectController::class, 'getProjectTask'])->name('project.tasks');
        // جلب اجتماعات المشروع
        Route::get('/project/{project_id}/meeting', [ProjectController::class, 'getProjectMeeting'])->name('project.meeting');



        /*
        |--------------------------------------------------------------------------
        | settings
        |--------------------------------------------------------------------------
        */
        Route::prefix('general-settings')->name('general-settings.')->group(function () {
            Route::get('', [GeneralSettingController::class, 'index'])->name('index');
            Route::get('system', [GeneralSettingController::class, 'system'])->name('system');  // إعدادات النظام
            Route::get('operations-center', [GeneralSettingController::class, 'operations'])->name('operations'); // مركز العمليات
            Route::get('human-resources', [GeneralSettingController::class, 'hr'])->name('hr'); // الموارد البشرية
            Route::get('marketing', [GeneralSettingController::class, 'marketing'])->name('marketing'); // التسويق
            Route::get('legal-affairs', [GeneralSettingController::class, 'legal'])->name('legal'); // الشؤون القانونية
            Route::get('purchasing-center', [GeneralSettingController::class, 'purchasing'])->name('purchasing'); // المشتريات



            // اعدادات النظام
            Route::prefix('system-settings')->name('system-settings.')->group(function () {
                Route::get('', [SystemSettingsController::class, 'index'])->name('index');
                Route::put('', [SystemSettingsController::class, 'update'])->name('update');

                //
                Route::put('company_attachments', [SystemSettingsController::class, 'companyAttachments'])->name('company_attachments');
            });
        });




        Route::resource('settings-storage-location', SettingsStorageLocationController::class);
        Route::resource('settings-asset-category', SettingsAssetCategoryController::class);

        Route::resource('settings-violation-categories', SettingsViolationCategoryController::class);
        Route::resource('settings-violations', SettingsViolationController::class);
        Route::resource('settings-departments-contracts', SettingsDepartmentContractCaseController::class);
        Route::resource('settings-contract-statuses', SettingsContractStatusController::class);
        Route::resource('settings-products', SettingsProductController::class);
        Route::resource('settings-hr-statuses', SettingsHrStatusController::class);
        // Route::resource('settings-stage-priceOffer', SettingsStagePriceOfferController::class);
        Route::resource('settings-client-status', SettingsClientStatusController::class);
        Route::resource('settings-marketing-channel', SettingsMarketingChannelController::class);
        Route::resource('settings-sector', SettingsSectorController::class);
        Route::get('settings-social/disconnect/{settings_social}', [SettingsSocialController::class, 'disconnect'])->name('settings-social.disconnect');
        Route::resource('settings-social', SettingsSocialController::class);
        Route::resource('settings-main-court', SettingsMainCourtController::class);
        Route::resource('settings-entity_rank', SettingsEntityRankController::class);
        Route::resource('settings-region', SettingsRegionController::class);
        Route::resource('settings-hr_classification', SettingsHRClassificationController::class);
        Route::resource('settings-country', SettingsCountryController::class);
        Route::resource('settings-banks', SettingsBanksController::class);

        // اعدادات التسويق
        Route::resource('settings-content-types', SettingsContentTypesController::class);
        Route::resource('settings-publishing-patterns', SettingsPublishingPatternsController::class);
        Route::resource('settings-content-purpose', SettingsContentPurposeController::class);
        Route::resource('settings-campaign-sections', SettingsCampaignSectionsController::class);
        Route::resource('settings-target-audience', SettingsTargetAudienceController::class);


        Route::resource('settings-category', SettingsCategoriesController::class)->except(['show']);
        Route::resource('settings-purchase-category', SettingsPurchaseCategoryController::class);
        Route::resource('settings-templates', SettingsTemplateController::class);
        Route::post('settings-templates/mass-delete', [SettingsTemplateController::class, 'massDelete'])->name('settings-templates.massDelete');
        Route::get('templates/edit-status/{id}', [SettingsTemplateController::class, 'editStatus'])->name('settings-templates.editStatus');
        // لجلب المتغيرات حسب النموذج -- عروض ام عقود
        Route::get('/templates/variables', [SettingsTemplateController::class, 'getVariablesByType'])->name('templates.getVariables');

        // للسحب والاافلات
        Route::post('/settings-category/reorder', [SettingsCategoriesController::class, 'reorder'])->name('settings-category.reorder');
        Route::post('/settings-subcategory/reorder', [SettingsSubcategoriesController::class, 'reorder'])->name('settings-subcategory.reorder');
        Route::post('/settings-lawsuits-types/reorder', [SettingsLawsuitsTypeController::class, 'reorder'])->name('settings-lawsuits-types.reorder');
        Route::resource('settings-session-type', SettingsSessionTypeController::class);
        Route::resource('settings-rule-type', SettingsTypeRulingsController::class);
        Route::get('settings-category/{id}/toggle-status', [SettingsCategoriesController::class, 'toggleStatus'])->name('settings-category.toggleStatus');
        Route::post('settings-category/massDelete', [SettingsCategoriesController::class, 'massDelete'])->name('settings-category.massDelete');
        // مسارات التصنيفات الفرعية
        Route::resource('settings-subcategory', SettingsSubcategoriesController::class);
        // مسار لتغيير حالة التصنيف الفرعي
        Route::get('settings-subcategory/{id}/toggle-status', [SettingsSubcategoriesController::class, 'toggleStatus'])->name('settings-subcategory.toggleStatus');
        Route::post('settings-subcategory/massDelete', [SettingsSubcategoriesController::class, 'massDelete'])->name('settings-subcategory.massDelete');
        // end of setings
        Route::get('settings-subcategories/by-category', [SettingsSubcategoriesController::class, 'getSubcategoriesByCategory'])->name('settings-subcategories.byCategory');

        Route::resource('settings-lawsuits-types', SettingsLawsuitsTypeController::class);
        Route::get('settings-lawsuits-types/get-subcategories/{category_id}', [SettingsLawsuitsTypeController::class, 'getSubcategories'])->name('settings-lawsuits-types.getSubcategories');
        Route::get('settings-lawsuits-types/toggle-status/{id}', [SettingsLawsuitsTypeController::class, 'toggleStatus'])->name('settings-lawsuits-types.toggleStatus');
        Route::post('settings-lawsuits-types/mass-delete', [SettingsLawsuitsTypeController::class, 'massDelete'])->name('settings-lawsuits-types.massDelete');

        // مسار الموارد لنوع الإجازات
        Route::resource('settings-leave-types', SettingsLeaveTypeController::class);
        // مسار لتغيير الحالة (toggle status)
        Route::get('settings-leave-types/toggle-status/{id}', [SettingsLeaveTypeController::class, 'toggleStatus'])->name('settings-leave-types.toggle-status');
        // مسار الحذف الجماعي لنوع الإجازات
        Route::post('settings-leave-types/mass-delete', [SettingsLeaveTypeController::class, 'massDelete'])->name('settings-leave-types.mass-delete');













        /*
        |--------------------------------------------------------------------------
        | Chat Routes
        |--------------------------------------------------------------------------
        | Chat functionality routes with authentication middleware.
        */
        Route::middleware(['auth'])->group(function () {
            // Chat main page
            Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');

            // Messages routes
            Route::get('/messages/{userId}', [ChatController::class, 'getMessages'])->name('chat.messages.get');
            Route::post('/messages', [ChatController::class, 'sendMessage'])->name('chat.messages.send');
            Route::patch('/messages/{messageId}/read', [ChatController::class, 'markAsRead'])->name('chat.messages.read');
            Route::get('/messages/{userId}/read-status', [ChatController::class, 'getReadStatus'])->name('chat.messages.read-status');
        });


        /*
        |--------------------------------------------------------------------------
        | Task Management Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('organization-center')->name('organization-center.')->group(function () {
            Route::prefix('tasks')->name('tasks.')->group(function () {
                Route::get('my-tasks', [TaskController::class, 'indexMyTasks'])->name('my-tasks'); // مهامي (المهام المسندة إليّ)
                Route::get('assigned-tasks', [TaskController::class, 'indexAssignedTasks'])->name('assigned-tasks'); // المهام التي قمت بإسنادها
                Route::delete('attachments/{attachmentId}', [TaskController::class, 'deleteAttachment'])->name('attachment.delete'); // حذف مرفق المهمة

                Route::resource('', TaskController::class)->parameters(['' => 'task']);

                Route::post('/toggle-completion/{task}', [TaskController::class, 'toggleTaskCompletion'])->name('toggle-completion');
                Route::post('/return/{task}', [TaskController::class, 'returnTask'])->name('return');
                Route::post('/reassign/{task}', [TaskController::class, 'reassignTask'])->name('reassign');

                // Task Archiving and Restoration
                Route::get('/trashed', [TaskController::class, 'trashed'])->name('trashed');
                Route::put('/{id}/restore', [TaskController::class, 'restore'])->name('restore');
                Route::delete('/{id}/forceDelete', [TaskController::class, 'forceDelete'])->name('forceDelete');

                Route::post('/steps/{stepId}/toggle-completion', [TaskController::class, 'toggleStepCompletion'])->name('steps.toggle-completion');
                Route::post('/steps/{stepId}/toggle-approval', [TaskController::class, 'toggleStepApproval'])->name('steps.toggle-approval');
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Approval Workflow Routes
        |--------------------------------------------------------------------------
        | Routes for handling offer approvals, rejections, revocations, and details.
        */
        Route::prefix('approval-workflow')->name('approval-workflow.')->group(function () {

            Route::prefix('unified')->name('unified.')->group(function () {
                Route::get('/', [UnifiedApprovalController::class, 'index'])->name('index');
            });
            // مجموعة راوتات اعتمادات العروض
            Route::prefix('offers')->name('offers.')->group(function () {
                Route::get('/{id}', [OfferApprovalController::class, 'show'])->name('show');
                Route::post('/{id}/approve', [OfferApprovalController::class, 'approve'])->name('approve');
                Route::post('/{id}/reject', [OfferApprovalController::class, 'reject'])->name('reject');
                Route::post('/{id}/revoke', [OfferApprovalController::class, 'revoke'])->name('revoke');
            });


            Route::prefix('contracts')->name('contracts.')->group(function () {
                // Route::get('/', [ContractApprovalController::class, 'index'])->name('index');
                Route::get('{id}', [ContractApprovalController::class, 'show'])->name('show');
                Route::post('{id}/approve', [ContractApprovalController::class, 'approve'])->name('approve');
                Route::post('{id}/reject', [ContractApprovalController::class, 'reject'])->name('reject');
                Route::post('{id}/revoke', [ContractApprovalController::class, 'revoke'])->name('revoke');
            });


            Route::prefix('leave-requests')->name('leave-requests.')->group(function () {
                // Route::get('/', [LeaveRequestApprovalController::class, 'index'])->name('index');
                Route::get('{id}', [LeaveRequestApprovalController::class, 'show'])->name('show');
                Route::post('{id}/approve', [LeaveRequestApprovalController::class, 'approve'])->name('approve');
                Route::post('{id}/reject',  [LeaveRequestApprovalController::class, 'reject'])->name('reject');
                Route::post('{id}/revoke',  [LeaveRequestApprovalController::class, 'revoke'])->name('revoke');
            });

            Route::prefix('wps-payrolls')->name('wps-payrolls.')->group(function () {
                Route::get('/', [WpsPayrollApprovalController::class, 'index'])->name('index');
                Route::get('/{id}', [WpsPayrollApprovalController::class, 'show'])->name('show');
                Route::post('/{id}/approve', [WpsPayrollApprovalController::class, 'approve'])->name('approve');
                Route::post('/{id}/reject', [WpsPayrollApprovalController::class, 'reject'])->name('reject');
                Route::post('/{id}/revoke', [WpsPayrollApprovalController::class, 'revoke'])->name('revoke');
            });

            Route::prefix('clearance-certificates')->name('clearance-certificates.')->group(function () {
                // Route::get('/', [ClearanceCertificateApprovalController::class, 'index'])->name('index');
                Route::get('{id}', [ClearanceCertificateApprovalController::class, 'show'])->name('show');
                Route::post('{id}/approve', [ClearanceCertificateApprovalController::class, 'approve'])->name('approve');
                Route::post('{id}/reject',  [ClearanceCertificateApprovalController::class, 'reject'])->name('reject');
                Route::post('{id}/revoke',  [ClearanceCertificateApprovalController::class, 'revoke'])->name('revoke');
            });

            Route::prefix('advances')->name('advances.')->group(function () {
                // Route::get('/', [AdvanceApprovalController::class, 'index'])->name('index');
                Route::get('/{id}', [AdvanceApprovalController::class, 'show'])->name('show');
                Route::post('/{id}/approve', [AdvanceApprovalController::class, 'approve'])->name('approve');
                Route::post('/{id}/reject', [AdvanceApprovalController::class, 'reject'])->name('reject');
                Route::post('/{id}/revoke', [AdvanceApprovalController::class, 'revoke'])->name('revoke');
            });

            Route::prefix('rewards')->name('rewards.')->group(function () {
                // Route::get('/', [RewardApprovalController::class, 'index'])->name('index');
                Route::get('/{id}', [RewardApprovalController::class, 'show'])->name('show');
                Route::post('/{id}/approve', [RewardApprovalController::class, 'approve'])->name('approve');
                Route::post('/{id}/reject', [RewardApprovalController::class, 'reject'])->name('reject');
                Route::post('/{id}/revoke', [RewardApprovalController::class, 'revoke'])->name('revoke');
            });


            Route::prefix('deductions')->name('deductions.')->group(function () {
                // Route::get('/', [DeductionApprovalController::class, 'index'])->name('index');
                Route::get('/{id}', [DeductionApprovalController::class, 'show'])->name('show');
                Route::post('/{id}/approve', [DeductionApprovalController::class, 'approve'])->name('approve');
                Route::post('/{id}/reject', [DeductionApprovalController::class, 'reject'])->name('reject');
                Route::post('/{id}/revoke', [DeductionApprovalController::class, 'revoke'])->name('revoke');
            });


            Route::prefix('content')->name('content.')->group(function () {
                // Route::get('/', [ContentApprovalController::class, 'index'])->name('index');
                Route::get('/{id}', [ContentApprovalController::class, 'show'])->name('show');
                Route::post('/{id}/approve', [ContentApprovalController::class, 'approve'])->name('approve');
                Route::post('/{id}/reject', [ContentApprovalController::class, 'reject'])->name('reject');
                Route::post('/{id}/revoke', [ContentApprovalController::class, 'revoke'])->name('revoke');
            });

            Route::prefix('custodies')->name('custodies.')->group(function () {
                // Route::get('/', [CustodyRequestApprovalController::class, 'index'])->name('index');
                Route::get('/{id}', [CustodyRequestApprovalController::class, 'show'])->name('show');
                Route::post('/{id}/approve', [CustodyRequestApprovalController::class, 'approve'])->name('approve');
                Route::post('/{id}/reject', [CustodyRequestApprovalController::class, 'reject'])->name('reject');
                Route::post('/{id}/revoke', [CustodyRequestApprovalController::class, 'revoke'])->name('revoke');
            });
        });


        /*
        |--------------------------------------------------------------------------
        | Task Routes
        |--------------------------------------------------------------------------
        */
        Route::resource('supports', SupportController::class);
        Route::get('/support/show/user', [SupportController::class, 'userSuppports'])->name('userSuppports');
        Route::post('/supports/reply/{id}', [SupportController::class, 'reply'])->name('supports.reply');
        Route::get('/get-reply/{id}', [SupportController::class, 'getReply']);
        Route::get('/show-ticket/{id}', [SupportController::class, 'showTicket'])->name('supports.reply.show');
        Route::post('/supports/{support}/reply', [SupportController::class, 'addReply'])->name('add.reply');
        Route::post('/supports/{support}/technecal', [SupportController::class, 'addReplyTechnecal'])->name('add.reply.technecal');
        Route::get('/{support}/print', [SupportController::class, 'print'])->name('supports.print');
        Route::get('supports/{support}/export_pdf', [SupportController::class, 'exportSupportTicketToPDF'])->name('supports.pdf');

        /*
        |--------------------------------------------------------------------------
        | Report Generation Routes
        |--------------------------------------------------------------------------
        | Routes for generating various system reports including customers,
        */
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('customers', [ReportsController::class, 'customersReport'])->name('customers');
            Route::get('offers', [ReportsController::class, 'offersReport'])->name('offers');
            Route::get('contracts', [ReportsController::class, 'contractsReport'])->name('contracts');
            Route::get('powerOfAttorneyReport', [ReportsController::class, 'powerOfAttorneyReport'])->name('powerOfAttorney');
            Route::get('opponents', [ReportsController::class, 'opponentsReport'])->name('opponents');
            Route::get('lawsuits', [ReportsController::class, 'lawsuitsReport'])->name('lawsuits');
            Route::get('sessions', [ReportsController::class, 'sessionsReport'])->name('sessions');
            Route::get('projects', [ReportsController::class, 'projectsReport'])->name('projects');
            Route::get('employees', [ReportsController::class, 'employeesReport'])->name('employees');

            Route::prefix('leave-reports')->name('leave-reports.')->group(function () {
                Route::get('/', [LeaveReportController::class, 'index'])->name('index');
                Route::post('export', [LeaveReportController::class, 'exportSelected'])->name('export');
            });

            // تقارير الحضور
            Route::prefix('attendance')->name('attendance.')->group(function () {
                Route::get('/', [AttendanceReportsController::class, 'index'])->name('index');
                Route::get('table-data', [AttendanceReportsController::class, 'tableData'])->name('table-data');
                Route::get('preview', [AttendanceReportsController::class, 'preview'])->name('preview');
                Route::get('download', [AttendanceReportsController::class, 'downloadPDF'])->name('download');
                Route::get('employee/{employeeId?}', [AttendanceReportsController::class, 'employeeAttendance'])->name('employee');
                Route::get('department', [AttendanceReportsController::class, 'departmentAttendance'])->name('department');
            });
        });



        Route::get('someRouteForDataTable', [ReportsController::class, 'attendanceTableData'])
            ->name('someRouteForDataTable');


        /*
        |--------------------------------------------------------------------------
        | Microsoft OneDrive Integration Routes
        |--------------------------------------------------------------------------
        |
        | Routes for handling OneDrive file system operations including file/folder
        | management, viewing, uploading, and downloading functionality.
        */
        Route::prefix('onedrive')->name('onedrive.')->group(function () {
            Route::get('/', [OneDriveController::class, 'index'])->name('index');
            Route::get('/folder/{folderId}', [OneDriveController::class, 'viewFolder'])->name('viewFolder');
            Route::post('/upload', [OneDriveController::class, 'uploadFile'])->name('uploadFile');
            Route::post('/upload-large-file', [OneDriveController::class, 'uploadLargeFile'])->name('uploadLargeFile');
            Route::post('/create-file', [OneDriveController::class, 'createFile'])->name('createFile');
            Route::post('/create-folder', [OneDriveController::class, 'createFolder'])->name('createFolder');
            Route::get('/downloadFile/{fileId}', [OneDriveController::class, 'downloadFile'])->name('downloadFile');
            Route::get('/editFile/{fileId}', [OneDriveController::class, 'editFile'])->name('editFile');
            Route::get('/viewFile/{fileId}', [OneDriveController::class, 'viewFile'])->name('viewFile');
            Route::delete('/delete/{fileId}', [OneDriveController::class, 'deleteItem'])->name('deleteItem');
        });


        /*
        |--------------------------------------------------------------------------
        | Email Management Routes
        |--------------------------------------------------------------------------
        |
        | Handles email operations including viewing, sending, replying and deletion
        */
        Route::prefix('emails')->name('emails.')->group(function () {
            // Email viewing
            Route::get('/', [EmailController::class, 'index'])->name('index');
            Route::get('/{id}', [EmailController::class, 'read'])->name('read');
            Route::get('/folder/{folder}', [EmailController::class, 'getFolderEmails'])->name('folder');
            // Email actions
            Route::post('/send', [EmailController::class, 'send'])->name('send');
            Route::post('/reply', [EmailController::class, 'reply'])->name('reply');
            // Deletion operations
            Route::delete('/delete/{id}', [EmailController::class, 'delete'])->name('delete');
            Route::delete('/delete', [EmailController::class, 'bulkDelete'])->name('bulkDelete');
        });




        // عرض قائمة الاجتماعات
        Route::get('/teams', [TeamsController::class, 'index'])->name('microsoft.teams.index');
        Route::get('/teams/create', [TeamsController::class, 'create'])->name('teams.create');
        // إنشاء اجتماع جديد
        Route::post('/teams/store', [TeamsController::class, 'createMeeting'])->name('teams.store');

        // عرض صفحة تعديل اجتماع
        Route::get('/teams/{id}/edit', [TeamsController::class, 'edit'])->name('teams.edit');
        // تحديث اجتماع
        Route::put('/teams/{id}', [TeamsController::class, 'update'])->name('teams.update');
        // حذف اجتماع
        Route::delete('/teams/{id}', [TeamsController::class, 'destroy'])->name('teams.destroy');
        Route::post('/teams/{id}/outputs', [TeamsController::class, 'updateOutputs'])->name('teams.outputs.update');
        Route::get('/teams/{id}/show', [TeamsController::class, 'show'])->name('teams.show');


        /*
        |--------------------------------------------------------------------------
        | Calendar Management Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('calendar')->name('calendar.')->group(function () {
            Route::get('/', [CalendarController::class, 'index'])->name('index');
            Route::get('events', [CalendarController::class, 'getEvents'])->name('events');
            Route::post('event', [CalendarController::class, 'createEvent'])->name('createEvent');
        });

        /*
        |--------------------------------------------------------------------------
        | Calendar Event Management Routes
        |--------------------------------------------------------------------------
        |
        | Handles single event operations like viewing details, updating and deletion
        */
        Route::prefix('calendar/events')->name('calendar.event.')->group(function () {
            Route::get('{eventId}', [CalendarController::class, 'getEventDetails'])->name('details');
            Route::post('{eventId}/update', [CalendarController::class, 'updateEvent'])->name('update');
            Route::delete('{eventId}/delete', [CalendarController::class, 'deleteEvent'])->name('delete');
        });


        /*
        |--------------------------------------------------------------------------
        | Notification Management Routes
        |--------------------------------------------------------------------------
        |
        | Handles notification operations including viewing, marking as read,
        | and deletion (both single and bulk operations)
        */
        Route::prefix('notifications')->name('notifications.')->group(function () {
            // Core operations
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            // Read status management
            Route::post('mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('markAllAsRead');
            Route::post('{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('markAsRead');
            // Deletion operations
            Route::delete('{id}', [NotificationController::class, 'destroy'])->name('destroy');
            Route::delete('destroy-selected', [NotificationController::class, 'destroySelected'])->name('destroySelected');
        });



        // tour  التلميحات
        Route::get('/check-tour-status', [TourController::class, 'checkTourStatus'])->name('check.tour.status');
        Route::post('/mark-tour-complete', [TourController::class, 'markTourComplete'])->name('mark.tour.complete');

        // tour  التلميحات الخاصة بالمهام
        Route::get('/check-tour-tasks-status', [TourController::class, 'checkTourStatusTasks'])->name('check.tour.tasks.status');
        Route::post('/mark-tour-tasks-complete', [TourController::class, 'markTourCompleteTasks'])->name('mark.tour.tasks.complete');



        /*
        |============================================================================
        |============================================================================
        |                            Qoyod
        |============================================================================
        |============================================================================
        */
        Route::prefix('qoyod')->name('qoyod.')->group(function () {

            Route::get('/', [QTestController::class, 'index'])->name('tes');

            // 1-  qoyod.accounts.
            Route::prefix('accounts')->name('accounts.')->group(function () {
                Route::resource('', QAccountsController::class)->parameters(['' => 'account']);
            });


            // 2- qoyod.categories.
            Route::prefix('categories')->name('categories.')->group(function () {
                Route::resource('', QCategoriesController::class)->parameters(['' => 'type']);
            });


            // 3- qoyod.product-unit-types.
            Route::prefix('product-unit-types')->name('product-unit-types.')->group(function () {
                Route::resource('', QUnitsController::class);
            });


            // 4- qoyod.products.
            Route::prefix('products')->name('products.')->group(function () {
                Route::resource('', QProductsController::class)->parameters(['' => 'product']);
            });


            // 5- qoyod.inventories.
            Route::prefix('inventories')->name('inventories.')->group(function () {
                Route::resource('', QInventoriesController::class)->parameters(['' => 'inventory']);
            });


            // 6- qoyod.vendors.
            Route::prefix('vendors')->name('vendors.')->group(function () {
                Route::get('get-vendors', [QVendorsController::class, 'getVendors'])->name('get-vendors'); // get all vendors
                Route::resource('', QVendorsController::class)->parameters(['' => 'vendor']);
            });


            // 7- qoyod.purchase-orders.
            Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {
                Route::resource('', QPurchaseOrdersController::class)->parameters(['' => 'order']);
            });


            // 8- qoyod.bills.
            Route::prefix('bills')->name('bills.')->group(function () {
                Route::resource('', QBillsController::class)->parameters(['' => 'bill']);
                Route::get('/get-bills-by-vendor/{contact_id}', [QBillsController::class, 'getBillsByvendorName'])->name('get-bills-by-vendor');
            });


            // 9- qoyod.bill-payments.
            Route::prefix('bill-payments')->name('bill-payments.')->group(function () {
                Route::resource('', QBillPaymentsController::class);
            });


            // 10- qoyod.debit-notes.
            Route::prefix('debit-notes')->name('debit-notes.')->group(function () {
                Route::resource('', QDebitNotesController::class);
            });


            // 11- qoyod.customers.
            Route::prefix('customers')->name('customers.')->group(function () {
                Route::get('get-customers', [QCustomersController::class, 'getCustomers'])->name('get-customers'); // get all coustomer
                Route::resource('', QCustomersController::class)->parameters(['' => 'customer']);
            });


            // 12- qoyod.quotes.
            Route::prefix('quotes')->name('quotes.')->group(function () {
                Route::resource('', QQuotesController::class)->parameters(['' => 'quote']);
            });


            // 13- qoyod.invoices.
            Route::prefix('invoices')->name('invoices.')->group(function () {
                Route::resource('', QInvoicesController::class)->parameters(['' => 'invoice']);
                Route::get('/get-invoices-by-customer/{contact_id}', [QInvoicesController::class, 'getInvoicesByCustomerName'])->name('get-invoices-by-customer');
                Route::get('/{invoice}/line-items', [QInvoicesController::class, 'getInvoiceLineItems'])->name('get-invoice-line-items');
            });


            // 14- qoyod.invoice-payments.
            Route::prefix('invoice-payments')->name('invoice-payments.')->group(function () {
                Route::resource('', QInvoicePaymentsController::class);
            });


            // 15- qoyod.credit-notes.
            Route::prefix('credit-notes')->name('credit-notes.')->group(function () {
                Route::resource('', QCreditNotesController::class)->parameters(['' => 'credit-note']);
            });


            // 16- qoyod.receipts.
            Route::prefix('receipts')->name('receipts.')->group(function () {
                Route::resource('', QReceiptsController::class)->parameters(['' => 'receipt']);
            });


            // 17- qoyod.journal-entries.
            Route::prefix('journal-entries')->name('journal-entries.')->group(function () {
                Route::resource('', QJournalEntriesController::class);
            });
        });
    });



    Route::middleware(['auth', 'active', 'policy.agreement'])->prefix('legal-ai')->name('legal-ai.')->group(function () {
        // === مجموعة مسارات الدردشة ===
        Route::prefix('chat')->name('chat.')->group(function () {
            Route::post('/create', [AIChatController::class, 'create'])->name('create');
            Route::get('/load/{chat}', [AIChatController::class, 'loadChat'])->name('load');
            Route::get('/{chat?}', [AIChatController::class, 'dashboard'])->name('dashboard');
            Route::post('/save-response/{chat}', [AIChatController::class, 'saveAiResponse'])->name('save-response');
            Route::post('/send-stream/{chat}', [AIChatController::class, 'sendStreamedMessage'])->name('send-stream');
            Route::delete('/delete/{chat}', [AIChatController::class, 'destroy'])->name('destroy');
        });

        // === الأداة الثانية: تلخيص القضايا ===
        Route::prefix('summarize')->name('summarize.')->group(function () {
            Route::post('/create', [SummarizeController::class, 'create'])->name('create');
            Route::get('/load/{chat}', [SummarizeController::class, 'loadChat'])->name('load');
            Route::get('/{chat?}', [SummarizeController::class, 'dashboard'])->name('dashboard');
            Route::post('/save-response/{chat}', [SummarizeController::class, 'saveAiResponse'])->name('save-response');
            Route::post('/handle-stream/{chat}', [SummarizeController::class, 'handleStream'])->name('handle-stream');
            Route::delete('/delete/{chat}', [SummarizeController::class, 'destroy'])->name('destroy');
        });

        // === الأداة الثالثة: صياغة المذكرات ===
        Route::prefix('drafting')->name('drafting.')->group(function () {
            Route::get('/{chat?}', [DraftingController::class, 'dashboard'])->name('dashboard');
            Route::post('/create', [DraftingController::class, 'create'])->name('create');
            Route::get('/load/{chat}', [DraftingController::class, 'loadChat'])->name('load');
            Route::post('/handle-stream/{chat}', [DraftingController::class, 'handleStream'])->name('handle-stream');
            Route::post('/save-response/{chat}', [DraftingController::class, 'saveAiResponse'])->name('save-response');
            Route::delete('/delete/{chat}', [DraftingController::class, 'destroy'])->name('destroy');
        });

        // === الأداة الرابعة: بحث السوابق القضائية ===
        Route::prefix('precedents')->name('precedents.')->group(function () {
            Route::get('/{chat?}', [PrecedentController::class, 'dashboard'])->name('dashboard');
            Route::post('/create', [PrecedentController::class, 'create'])->name('create');
            Route::get('/load/{chat}', [PrecedentController::class, 'loadChat'])->name('load');
            Route::post('/handle-stream/{chat}', [PrecedentController::class, 'handleStream'])->name('handle-stream');
            Route::post('/save-response/{chat}', [PrecedentController::class, 'saveAiResponse'])->name('save-response');
            Route::delete('/delete/{chat}', [PrecedentController::class, 'destroy'])->name('destroy');
        });

        // === التصدير المشترك===
        Route::prefix('message')->name('message.')->group(function () {
            Route::get('export/{message}', [ExportController::class, 'exportMessage'])->name('export');
            Route::get('export-word/{message}', [ExportController::class, 'exportMessageToWord'])->name('export-word');
        });
    });



    Route::middleware(['auth', 'active', 'policy.agreement'])->group(function () {

        // الواتساب
        Route::get('/send-whatsapp', [WhatsAppController::class, 'sendMessage']);
    });

    Route::middleware(['auth', 'active', 'policy.agreement'])->group(function () {
        Route::prefix('favorites')->name('favorites.')->group(function () {
            Route::match(['get', 'post'], '/check', [FavoriteController::class, 'checkFavorite'])->name('check');
            Route::match(['get', 'post'], '/toggle', [FavoriteController::class, 'toggleFavorite'])->name('toggle');
            Route::get('/', [FavoriteController::class, 'index'])->name('index');
        });
    });


    require __DIR__ . '/auth.php';

    /*
    |--------------------------------------------------------------------------
    | إعادة تعين كلمة المرور
    |--------------------------------------------------------------------------
    */
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->middleware('guest')->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('guest')->name('password.email');

    /**
     * Error Handling Routes
     */
    Route::fallback(function () {
        return response()->view('errors.404', [], 404);
    });
});
