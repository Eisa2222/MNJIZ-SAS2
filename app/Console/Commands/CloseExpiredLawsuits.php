<?php

namespace App\Console\Commands;

use App\Models\judicial_affairs\Lawsuit as Judicial_affairsLawsuit;
use App\Models\judicial_affairs\Session;
use Illuminate\Console\Command;
use App\Models\Lawsuit;
use Carbon\Carbon;

// class CloseExpiredLawsuits extends Command
// {
//     /**
//      * The name and signature of the console command.
//      *
//      * @var string
//      */
//     protected $signature = 'lawsuits:close-expired';

//     /**
//      * The console command description.
//      *
//      * @var string
//      */
//     protected $description = 'Close lawsuits where the objection deadline has passed';

//     /**
//      * Execute the console command.
//      *
//      * @return int
//      */
//     public function handle()
//     {
//         // $now = Carbon::now();

//         // // جلب الجلسات التي انتهت مهلة الاعتراض عليها ولم يتم الاعتراض
//         // $expiredSessions = Session::where('objection_status', '=', null)
//         //     ->where('last_objection_deadline', '<', $now)
//         //     ->get();

//         // foreach ($expiredSessions as $session) {
//         //     // الحصول على الدعوى المرتبطة بالجلسة

//         //     $session->session_status='مغلقة';
//         //     $session->save();

//         //     $lawsuit = $session->lawsuit;

//         //     if ($lawsuit && $lawsuit->lawsuit_status != 'false') {
//         //         $lawsuit->lawsuit_status = 'false';
//         //         $lawsuit->save();

//         //         $this->info('Closed lawsuit ID: ' . $lawsuit->id);
//         //     }
//         // }

//         // $this->info('Expired lawsuits have been closed successfully.');

//         // return 0;
//     }
// }
