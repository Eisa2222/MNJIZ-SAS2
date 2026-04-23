<?php

namespace App\Exports;

use App\Models\Hr\Payrolls\WPS\WpsPayroll;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\{Alignment, Fill, Color};

class WpsPayrollExport implements
    FromCollection,       // نجلب البيانات كمجموعة
    WithMapping,          // نرسم كل صفّ يدويًّا
    WithHeadings,         // صفّ عناوين عربى (يُكتب تلقائيًّا)
    WithCustomStartCell,  // نبدأ من A3
    WithEvents,            // نحقن صفّ EN ونطبّق تنسيقات
    ShouldAutoSize
{
    private WpsPayroll $WpsPayroll;

    public function __construct(WpsPayroll $WpsPayroll)
    {
        $this->WpsPayroll = $WpsPayroll;
    }

    /*-----------------------------------------*/
    /*  البيانات                              */
    /*-----------------------------------------*/
    public function collection(): Collection
    {
        return $this->WpsPayroll->wpsDetails()->get();
    }

    /*-----------------------------------------*/
    /*    رسم صفّ البيانات                     */
    /*-----------------------------------------*/
    public function map($row): array
    {
        return [
            $row->employee->bank_name->name         ?? '',
            $row->employee->iban                    ?? '',
            $row->employee->raw_name                ?? '',
            $row->employee->national_number         ?? '',
            $row->employee->id_number               ?? '',
            $row->getRawOriginal('net')             ?? 0 ,
            $row->getRawOriginal('basic')           ?? 0 ,
            $row->getRawOriginal('housing')         ?? 0 ,
            ($row->getRawOriginal('other')??0 ) + ($row->getRawOriginal('transport')??0)  ,
            $row->getRawOriginal('deductions')      ?? 0,
            $row->employee->remarks                 ?? '',
            $row->employee->department              ?? '',
        ];
    }

    /*-----------------------------------------*/
    /*  العناوين بالعربى (ستكون فى الصفّ 2)  */
    /*-----------------------------------------*/
    public function headings(): array
    {
        return [
            'اسم البنك',
            'رقم الحساب',
            'اسم الموظف',
            'الرقم الوظيفى',
            'رقم الهوية',
            'صـافى الراتب',
            'الراتب الأساسى',
            'بدل السكن',
            'بدلات أخرى',
            'الخصومات',
            'ملاحظات',
            'القسم',
        ];
    }

    /*-----------------------------------------*/
    /* ابدأ كتابة البيانات من الخلية A3      */
    /*-----------------------------------------*/
    public function startCell(): string
    {
        return 'A2';
    }

    /*-----------------------------------------*/
    /* 4) حقن الصفّ الإنجليزى + تنسيق          */
    /*-----------------------------------------*/
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                /* صفّ الإنجليزى */
                $english = [
                    'Bank Name',
                    'Account Number(34N)',
                    'Employee Name',
                    'Employee Number',
                    'National ID Number (15N)',
                    'Salary (15N)',
                    'Basic Salary',
                    'Housing Allowance',
                    'Other Earnings',
                    'Deductions',
                    'Employee Remarks',
                    'Employee Department',
                ];

                // اكتبه فى الصف A1
                $sheet->fromArray($english, null, 'A1', false, false);

                // تجميد الصفَّين 1 و 2
                $sheet->freezePane('A3');

                // تنسيق عام للصفَّين
                $sheet->getStyle('A1:L2')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF0B5394']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                ]);

                /* صفّ 2 بلون أفتح قليلاً (اختيارى) */
                $sheet->getStyle('A2:L2')->getFill()
                    ->setStartColor(new Color('FF1F4E78'));

                /*---  توسيط كل البيانات أسفل العناوين  ---*/
                $highestRow = $sheet->getHighestRow();          // آخر صف كتبناه
                $sheet->getStyle("A3:L{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
            }
        ];
    }
}
