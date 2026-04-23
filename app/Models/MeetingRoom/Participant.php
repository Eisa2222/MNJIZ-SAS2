<?php

namespace App\Models\MeetingRoom;

use App\Models\Hr\Employees\Employees;
use App\Models\OperationsCenter\Customer\Customers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */
    protected $fillable = [
        'meeting_id',
        'email',
        'type',
        'employee_id',
        'customer_id',
    ];


    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */
    public function meeting()
    {
        return $this->belongsTo(MeetingRoom::class, 'meeting_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employees::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customers::class);
    }

    public function getNameAttribute(): ?string
    {
        return match ($this->type) {
            'employees' => $this->employee?->name,
            'customers' => $this->customer?->name,
            'additional' => null,
            default => null
        };
    }

    // public function getTypeDisplayAttribute(): string
    // {
    //     return match ($this->type) {
    //         'employees' => 'موظف',
    //         'customers' => 'عميل',
    //         'additional' => 'إضافي',
    //         default => $this->type
    //     };
    // }
}
