<?php

namespace App\Traits\ApprovalWorkflow;

use App\Models\ApprovalSystem\ApprovalRequest;
use App\Models\Hr\Employees\Employees;
use App\Services\ApprovalWorkflow\ApprovalWorkflowInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait HasApprovalWorkflow
{

    /*
    |--------------------------------------------------------------------------
    | The "booting" method of the trait.
    |--------------------------------------------------------------------------
    | This method is called when the trait is used in a model.
    */
    protected static function bootHasApprovalWorkflow()
    {

        static::created(function (Model $model) {
            if (!property_exists($model, 'flowType')) {
                return;
            }

            // -- خاص بمسيرات الراوتب  لاضافة الحالة  الخاصة بها عند اضافة الطلب  -- //
            if ($model->flowType === 'wps') {
                return;
            }
            // -- خاص بمسيرات الراوتب  لاضافة الحالة  الخاصة بها عند اضافة الطلب  -- //

            $approvalService = app(ApprovalWorkflowInterface::class);
            $requestedByUserId = Auth::id() ?? $model->created_by;

            // إنشاء طلب الاعتماد
            $approvalRequest = $approvalService->createApprovalRequest($model, $model->flowType, $requestedByUserId);

            if ($approvalRequest && $requestedByUserId) {
                $creatorEmployee = Employees::where('user_id', $requestedByUserId)->first();

                if ($creatorEmployee) {
                    $firstLevel = $approvalRequest->requestLevels()->where('level', 1)->first();

                    if ($firstLevel && $firstLevel->employee_id === $creatorEmployee->id) {
                        $approvalService->approve($approvalRequest->id, $creatorEmployee->id);
                    }
                }
            }
        });

        static::updated(function (Model $model) {
            if (!property_exists($model, 'flowType')) {
                return;
            }

            // -- خاص بمسيرات الراوتب  لاضافة الحالة  الخاصة بها عند اضافة الطلب  -- //
            if ($model->flowType === 'wps') {
                return;
            }
            // -- خاص بمسيرات الراوتب  لاضافة الحالة  الخاصة بها عند اضافة الطلب  -- //

            $originalStatus = $model->getOriginal('status');
            $newStatus = $model->status;

            $wasCompleted = in_array($originalStatus, ['approved', 'rejected']);
            $isBecomingPending = ($newStatus === 'pending');
            if ($wasCompleted && !$isBecomingPending) {

                if ($model->approvalRequest) {
                    $model->approvalRequest()->delete();
                }

                $approvalService = app(ApprovalWorkflowInterface::class);
                $approvalService->createApprovalRequest($model, $model->flowType, Auth::id() ?? $model->updated_by);
            }
        });
    }


    /*
    |--------------------------------------------------------------------------
    | العلاقة العامة مع طلب الاعتماد (Polymorphic).
    |--------------------------------------------------------------------------
    */
    public function approvalRequest()
    {
        return $this->morphOne(ApprovalRequest::class, 'approvable');
    }

    /*
    |--------------------------------------------------------------------------
    | تحديث حالة النموذج بناءً على طلب الاعتماد.
    |--------------------------------------------------------------------------
    */
    public function updateStatusFromApproval()
    {
        $approvalRequest = $this->approvalRequest()->first();
        $newStatus = 'approved';

        if ($approvalRequest) {
            $newStatus = $approvalRequest->status;
        }

        if ($this->status !== $newStatus) {
            $this->status = $newStatus;
            $this->saveQuietly();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | الحصول على المعتمد التالي.
    |--------------------------------------------------------------------------
    */
    public function getNextApproverAttribute(): ?string
    {
        if (!$this->approvalRequest || !$this->approvalRequest->isPending()) {
            return null;
        }

        $currentApprover = $this->approvalRequest->getCurrentLevelApprover();
        return $currentApprover ? $currentApprover->name : null;
    }

    /*
    |--------------------------------------------------------------------------
    | الحصول على تفاصيل مراحل الاعتماد.
    |--------------------------------------------------------------------------
    */
    public function getApprovalStagesAttribute(): array
    {
        if (!$this->approvalRequest) {
            return [];
        }

        return $this->approvalRequest->getApprovalStages();
    }


    /*
    |--------------------------------------------------------------------------
    | التحقق من إمكانية اعتماد النموذج من قبل موظف معين.
    |--------------------------------------------------------------------------
    */
    public function canBeApprovedBy(int $employeeId): bool
    {
        if (!$this->approvalRequest || !$this->approvalRequest->isPending()) {
            return false;
        }

        $approvalService = app(ApprovalWorkflowInterface::class);
        return $approvalService->canApprove($this->approvalRequest->id, $employeeId);
    }


    /*
    |--------------------------------------------------------------------------
    | التحقق من إمكانية إلغاء اعتماد النموذج من قبل موظف معين.
    |--------------------------------------------------------------------------
    */
    public function canBeRevokedBy(int $employeeId): bool
    {
        if (!$this->approvalRequest) {
            return false;
        }

        $approvalService = app(ApprovalWorkflowInterface::class);
        return $approvalService->canRevoke($this->approvalRequest->id, $employeeId);
    }
}
