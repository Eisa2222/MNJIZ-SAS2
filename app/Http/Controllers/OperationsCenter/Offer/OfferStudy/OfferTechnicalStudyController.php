<?php

namespace App\Http\Controllers\OperationsCenter\Offer\OfferStudy;

use App\DataTables\OperationsCenter\Offer\OfferTechnicalStudyDataTable;
use App\Http\Controllers\Controller;
use App\Models\OperationsCenter\Customer\Customers;
use App\Models\general_setting\SettingsStagePriceOffer;
use App\Models\Hr\Employees\Employees;
use App\Models\OperationsCenter\Offer\Offers;
use Illuminate\Http\Request;

class OfferTechnicalStudyController extends Controller
{
    /*
    |============================================================================
    | Index
    |============================================================================
    */
    public function index(OfferTechnicalStudyDataTable $dataTable, Request $request)
    {
        $route      = 'operations-center.offers';
        $baseQuery  =  Offers::query();


        try {
            if ($request->ajax()) return $dataTable->ajax();

            // Statistics
            $totalOffers                =  $baseQuery->count();
            $waitingClientApproval      =  (clone $baseQuery)->count();
            $contractStage              =  (clone $baseQuery)->count();
            $finishedOffers             =  (clone $baseQuery)->count();

            // Filters
            $stages_price_offer         = SettingsStagePriceOffer::select('id', 'name')->get();
            $customers                  = Customers::select('id', 'name')->get();
            $employees                  = Employees::active()->select('id', 'name', 'nickname')->get();

            // return $dataTable->render('qoyod.inventories.index');

            return $dataTable->render('operations_center.offers.offer_study.technical_study.index', compact(
                'route',
                'totalOffers',
                'waitingClientApproval',
                'contractStage',
                'finishedOffers',
                'stages_price_offer',
                'customers',
                'employees',
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }


    /*
    |============================================================================
    | create
    |============================================================================
    */
    public function create($id)
    {
        $technicalApprovalUsers = Employees::whereHas('user', function ($query) {
            $query->whereHas('roles', function ($q) {
                $q->whereHas('permissions', function ($p) {
                    $p->where('name', 'الإعتماد الفني للمشاريع');
                });
            })->orWhereHas('additionalPermissions', function ($q) {
                $q->where('name', 'الإعتماد الفني للمشاريع');
            });
        })->get();

        $validUsers = $technicalApprovalUsers->filter(function ($employee) {
            return $employee->user->hasPermissionTo('الإعتماد الفني للمشاريع');
        });

        if ($validUsers->isEmpty()) {
            return redirect()->route('projects.index')->with('warning', 'عفواً، لا يمكن إضافة مشروع جديد حالياً لعدم وجود مدير الشؤون الفنية للمشاريع.');
        }

        $technical_manager_id = $validUsers->map(function ($employee) {
            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'user_id' => $employee->user_id,
            ];
        });

        $employees = Employees::with('user')->select(['id', 'name', 'user_id', 'nickname'])->get();


        return view('operations_center.offers.offer_study.technical_study.create', compact('employees', 'technical_manager_id'));
    }


    /*
    |============================================================================
    | store
    |============================================================================
    */
    public function store(OfferRequest $request)
    {
        $dto = new OfferData($request->validated());

        $this->offerService->createOffer($dto);

        return redirect()->route('operations-center.offers.index')->with('success', 'تم إضافة العرض بنجاح!');
    }
}
