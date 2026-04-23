<?php

namespace App\Validators\MeetingRoom;

use App\Models\Hr\Employees\Employees;
use App\Models\OperationsCenter\Customer\Customers;
use Illuminate\Validation\ValidationException;

class ParticipantsValidator
{
    public function validateAndBuild(array $input): array
    {
        $types       = (array)($input['meeting_participants'] ?? []);
        $employeeIds = array_values(array_filter((array)($input['attendees_employee'] ?? []), 'strlen'));
        $customerIds = array_values(array_filter((array)($input['attendees_customer'] ?? []), 'strlen'));
        $addEmails   = array_values(array_filter((array)($input['additional_emails'] ?? []), 'strlen'));

        $errors = [];

        if (empty($types)) {
            $errors['meeting_participants'] = 'يجب اختيار نوع أو أكثر من أطراف الاجتماع.';
        }

        // الموظفون
        $employeesFound = [];
        if (in_array('employees', $types, true)) {
            if (empty($employeeIds)) {
                $errors['attendees_employee'] = 'عند اختيار الموظفين يجب اختيار موظف واحد على الأقل.';
            } else {
                $employeesFound = Employees::active()
                    ->whereIn('id', $employeeIds)
                    ->pluck('id', 'id')
                    ->toArray();

                $missing = array_values(array_diff($employeeIds, array_keys($employeesFound)));
                if (!empty($missing)) {
                    $errors['attendees_employee'] = 'معرّفات موظفين غير صالحة: ' . implode(', ', $missing);
                }
            }
        }

        // العملاء
        $customersFound = [];
        if (in_array('customers', $types, true)) {
            if (empty($customerIds)) {
                $errors['attendees_customer'] = 'عند اختيار العملاء يجب اختيار عميل واحد على الأقل.';
            } else {
                $customersFound = Customers::whereIn('id', $customerIds)
                    ->pluck('id', 'id')
                    ->toArray();

                $missing = array_values(array_diff($customerIds, array_keys($customersFound)));
                if (!empty($missing)) {
                    $errors['attendees_customer'] = 'معرّفات عملاء غير صالحة: ' . implode(', ', $missing);
                }
            }
        }

        // بريد إضافي
        if (in_array('additional', $types, true) && empty($addEmails)) {
            $errors['additional_emails'] = 'عند اختيار بريد آخر يجب إدخال بريد إلكتروني واحد على الأقل.';
        }

        if (count($employeeIds) !== count(array_unique($employeeIds))) {
            $errors['attendees_employee'] = 'تم تكرار نفس الموظف أكثر من مرة.';
        }
        if (count($customerIds) !== count(array_unique($customerIds))) {
            $errors['attendees_customer'] = 'تم تكرار نفس العميل أكثر من مرة.';
        }
        if (count($addEmails) !== count(array_unique($addEmails))) {
            $errors['additional_emails'] = 'تم تكرار بعض عناوين البريد.';
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        $participants = [];
        foreach (array_keys($employeesFound) as $empId) {
            $participants['emp_' . $empId] = [
                'type'        => 'employees',
                'employee_id' => (int) $empId,
            ];
        }

        foreach (array_keys($customersFound) as $cusId) {
            $key = 'cus_' . $cusId;
            if (!isset($participants[$key])) {
                $participants[$key] = [
                    'type'        => 'customers',
                    'customer_id' => (int) $cusId,
                ];
            }
        }

        foreach ($addEmails as $email) {
            $participants['add_' . mb_strtolower($email)] = [
                'type'  => 'additional',
                'email' => $email,
            ];
        }

        return array_values($participants);
    }
}