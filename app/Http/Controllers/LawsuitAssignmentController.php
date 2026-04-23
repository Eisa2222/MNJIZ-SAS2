<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\judicial_affairs\Lawsuit;

// class LawsuitAssignmentController extends Controller
// {
//     public function assignTeamsToLawsuits()
//     {
//         try {
//             DB::beginTransaction();

//             $lawsuits = Lawsuit::all();
//             $totalAssigned = 0;

//             foreach ($lawsuits as $lawsuit) {
//                 $projectId = $lawsuit->project_id;

//                 if (!$projectId) {
//                     Log::warning("الدعوى ID {$lawsuit->id} لا تحتوي على project_id");
//                     continue;
//                 }

//                 $projectTeam = DB::table('project_employee')
//                     ->where('project_id', $projectId)
//                     ->pluck('employee_id');

//                 foreach ($projectTeam as $employeeId) {
//                     $existing = DB::table('assigned_lawsuits')
//                         ->where('lawsuit_id', $lawsuit->id)
//                         ->where('assigned_to', $employeeId)
//                         ->first();

//                     if (!$existing) {
//                         DB::table('assigned_lawsuits')->insert([
//                             'lawsuit_id' => $lawsuit->id,
//                             'assigned_to' => $employeeId,
//                             'status' => 'accepted',
//                             'created_at' => now(),
//                             'updated_at' => now()
//                         ]);

//                         Log::info("تم تعيين الموظف ID {$employeeId} للدعوى ID {$lawsuit->id}");
//                         $totalAssigned++;
//                     } else {
//                         Log::info("الموظف ID {$employeeId} معين مسبقًا للدعوى ID {$lawsuit->id}");
//                     }
//                 }
//             }

//             DB::commit();

//             return response()->json([
//                 'message' => "تم ربط فرق الدعاوى بنجاح",
//                 'total_assigned' => $totalAssigned
//             ]);
//         } catch (\Exception $e) {
//             DB::rollBack();
//             Log::error("فشل في عملية ربط فرق الدعاوى: " . $e->getMessage());

//             return response()->json([
//                 'message' => "حدث خطأ أثناء عملية الربط",
//                 'error' => $e->getMessage()
//             ], 500);
//         }
//     }
// }
