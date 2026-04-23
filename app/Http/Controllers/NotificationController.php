<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * عرض جميع الإشعارات للمستخدم.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $query = Auth::user()->notifications()->latest();

                return datatables()->of($query)
                    ->addColumn('checkbox', function ($row) {
                        return '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">';
                    })
                    ->addColumn('name', function ($row) {
                        // تحقق من وجود 'url' في بيانات الإشعار
                        $url = isset($row->data['url']) ? $row->data['url'] : '#';
                        $message = htmlspecialchars($row->data['message']);

                        // إذا كان الإشعار غير مقروء، اجعله بلون مختلف (مثلاً باستخدام الكلاس 'fw-bold')
                        if (is_null($row->read_at)) {
                            return '<a href="' . $url . '" class="fw-bold text-decoration-none text-dark">' . $message . '</a>';
                        }

                        return '<a href="' . $url . '" class="text-decoration-none text-dark">' . $message . '</a>';
                    })
                    ->addColumn('created_at', function ($row) {
                        return $row->created_at->diffForHumans();
                    })
                    ->addColumn('actions', function ($row) {
                        $readAction = '';
                        if (is_null($row->read_at)) {
                            $readAction = '<a href="javascript:void(0)" class="dropdown-notifications-read me-2" onclick="markNotificationAsRead(\'' . $row->id . '\')"><span class="badge bg-warning"></span></a>';
                        }
                        $deleteAction = '<a href="javascript:void(0)" class="dropdown-notifications-archive" onclick="deleteNotification(\'' . $row->id . '\')"><span class="ti ti-x"></span></a>';
                        return $readAction . ' ' . $deleteAction;
                    })
                    ->rawColumns(['checkbox', 'actions', 'name'])
                    ->make(true);
            }

            return view('notifications.index');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }

    /**
     * تحديد جميع الإشعارات كمقروءة.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        $unreadCount = Auth::user()->unreadNotifications->count();

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديد جميع الإشعارات كمقروءة.',
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * تحديد إشعار واحد كمقروء.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
            $unreadCount = Auth::user()->unreadNotifications->count();

            return response()->json([
                'status' => 'success',
                'message' => 'تم تحديد الإشعار كمقروءة.',
                'unread_count' => $unreadCount
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'الإشعار غير موجود.'], 404);
    }

    /**
     * حذف إشعار.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $notification = Auth::user()->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->delete();
            $unreadCount = Auth::user()->unreadNotifications->count();

            return response()->json([
                'status' => 'success',
                'message' => 'تم حذف الإشعار بنجاح.',
                'unread_count' => $unreadCount
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'الإشعار غير موجود.'], 404);
    }

    /**
     * حذف إشعارات محددة.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroySelected(Request $request)
    {
        $ids = $request->input('ids', []);
        dd($ids);
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'لم يتم تحديد إشعارات للحذف.'], 400);
        }

        // تأكد من أن الإشعارات تنتمي للمستخدم الحالي
        $notifications = Auth::user()->notifications()->whereIn('id', $ids)->get();

        if ($notifications->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'لم يتم العثور على إشعارات محددة للحذف.'], 404);
        }

        // حذف الإشعارات
        Auth::user()->notifications()->whereIn('id', $ids)->delete();

        // حساب العدد المحدث للإشعارات غير المقروءة
        $unreadCount = Auth::user()->unreadNotifications->count();

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف الإشعارات المحددة بنجاح.',
            'unread_count' => $unreadCount
        ]);
    }
}
