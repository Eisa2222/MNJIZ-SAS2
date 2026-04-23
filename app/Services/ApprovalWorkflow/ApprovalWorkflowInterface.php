<?php
// app/Services/ApprovalWorkflow/ApprovalWorkflowInterface.php

namespace App\Services\ApprovalWorkflow;

use App\Models\ApprovalSystem\ApprovalRequest;
use Illuminate\Database\Eloquent\Model;

interface ApprovalWorkflowInterface
{
    /**
     * إنشاء طلب اعتماد جديد
     *
     * @param Model $model النموذج المطلوب اعتماده
     * @param string $flowType نوع تدفق الاعتماد (offer, contract, leave, etc.)
     * @param int $requestedByUserId معرف المستخدم الذي طلب الاعتماد
     * @return ApprovalRequest|null
     */
    public function createApprovalRequest(Model $model, string $flowType, int $requestedByUserId): ?ApprovalRequest;

    /**
     * اعتماد المستوى الحالي
     *
     * @param int $requestId معرف طلب الاعتماد
     * @param int $employeeId معرف الموظف المعتمد
     * @return array نتيجة العملية
     */
    public function approve(int $requestId, int $employeeId): array;

    /**
     * رفض الاعتماد
     *
     * @param int $requestId معرف طلب الاعتماد
     * @param int $employeeId معرف الموظف
     * @param string $reason سبب الرفض
     * @return array نتيجة العملية
     */
    public function reject(int $requestId, int $employeeId, string $reason): array;

    /**
     * إلغاء الاعتماد
     *
     * @param int $requestId معرف طلب الاعتماد
     * @param int $employeeId معرف الموظف
     * @return array نتيجة العملية
     */
    public function revoke(int $requestId, int $employeeId): array;

    /**
     * التحقق من صلاحية المعتمد للاعتماد
     *
     * @param int $requestId معرف طلب الاعتماد
     * @param int $employeeId معرف الموظف
     * @return bool
     */
    public function canApprove(int $requestId, int $employeeId): bool;

    /**
     * التحقق من إمكانية الإلغاء
     *
     * @param int $requestId معرف طلب الاعتماد
     * @param int $employeeId معرف الموظف
     * @return bool
     */
    public function canRevoke(int $requestId, int $employeeId): bool;

    /**
     * الحصول على حالة الاعتماد للنموذج
     *
     * @param Model $model النموذج
     * @return array معلومات حالة الاعتماد
     */
    public function getApprovalStatus(Model $model): array;

    /**
     * الحصول على معلومات مراحل الاعتماد
     *
     * @param Model $model النموذج
     * @return array معلومات المراحل
     */
    public function getApprovalStagesInfo(Model $model): array;

    /**
     * الحصول على المعتمد التالي
     *
     * @param int $requestId معرف طلب الاعتماد
     * @return array|null معلومات المعتمد التالي
     */
    public function getNextApprover(int $requestId): ?array;

    /**
     * الحصول على إحصائيات الاعتمادات حسب النوع
     *
     * @param string $flowType نوع التدفق
     * @return array الإحصائيات
     */
    public function getApprovalStatistics(string $flowType): array;

    /**
     * الحصول على طلبات الاعتماد للموظف
     *
     * @param int $employeeId معرف الموظف
     * @param string|null $flowType نوع التدفق (اختياري)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingApprovalsForEmployee(int $employeeId, ?string $flowType = null);
}
