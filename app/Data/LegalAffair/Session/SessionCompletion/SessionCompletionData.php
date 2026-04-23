<?php

namespace App\Data\LegalAffair\Session\SessionCompletion;

use App\Enums\LegalAffair\Session\SessionCompletion\SummaryReportStatus;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

class SessionCompletionData
{
    public SummaryReportStatus       $summary_report_status;
    public ?\DateTime   $last_objection_deadline;
    public int          $session_type;
    public ?int         $rule_type;
    public ?string      $execution_format;
    public ?\DateTime   $expected_execution_date;
    public int          $execution_minutes;
    public ?string       $notes;
    public ?string      $user_confirmation;

    public ?UploadedFile       $session_control_attached;
    public ?UploadedFile       $rule_attached;

    public function __construct(array $data)
    {
        $this->summary_report_status    = SummaryReportStatus::tryFrom(Arr::get($data, 'summary_report_status'));

        $this->last_objection_deadline  = !empty($data['last_objection_deadline'])
            ? new \DateTime($data['last_objection_deadline'])
            : null;

        $this->session_type             = (int) $data['session_type'];
        $this->rule_type                = !empty($data['rule_type']) ? (int) $data['rule_type'] : null;
        $this->execution_format         = $data['execution_format'] ?? null;
        $this->expected_execution_date  = !empty($data['expected_execution_date'])
            ? new \DateTime($data['expected_execution_date'])
            : null;

        $this->execution_minutes        = (int) $data['execution_minutes'];
        $this->notes                    = $data['notes'] ?? null;

        // تحديد ما إذا كان يجب إغلاق الدعوى
        $this->user_confirmation        = $data['user_confirmation'] ?? null;

        $this->session_control_attached = $data['session_control_attached'] ?? null; // UploadedFile|null
        $this->rule_attached            = $data['rule_attached'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'summary_report_status'         => $this->summary_report_status,
            'last_objection_deadline'       => $this->last_objection_deadline?->format('Y-m-d'),
            'session_type'                  => $this->session_type,
            'rule_type'                     => $this->rule_type,
            'execution_format'              => $this->execution_format,
            'expected_execution_date'       => $this->expected_execution_date?->format('Y-m-d'),
            'execution_minutes'             => $this->execution_minutes,
            'notes'                         => $this->notes,
            'user_confirmation'             => $this->user_confirmation,
        ];
    }

    // تحديد ما إذا كان يجب إغلاق الدعوى



    public function getSuccessMessage(): string
    {
        if ($this->user_confirmation) {
            return 'تم استكمال ضبط الجلسة وإغلاق الدعوى بنجاح.';
        }

        return 'تم استكمال ضبط الجلسة بنجاح.';
    }
}
