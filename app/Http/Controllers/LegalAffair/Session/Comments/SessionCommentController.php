<?php

namespace App\Http\Controllers\LegalAffair\Session\Comments;


use App\Events\NotificationSent;
use App\Http\Controllers\Controller;
use App\Models\Hr\Employees\Employees;
use App\Models\LegalAffair\Session\Session;
use App\Models\LegalAffair\Session\SessionCommentMention;
use App\Models\User;
use App\Notifications\CommentMentioned;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SessionCommentController extends Controller
{
    public function index(Session $session)
    {
        $comments = $session->comments()->with('user')->get();

        $commentsData = $comments->map(function ($comment) {
            return [
                'id' => $comment->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                ],
                'can_edit_delete' => $comment->user_id === Auth::id(),
            ];
        });

        return response()->json([
            'success' => true,
            'comments' => $commentsData,
        ]);
    }


    public function store(Request $request, Session $session)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            // إنشاء التعليق
            $comment = $session->comments()->create([
                'content' => $request->content,
                'user_id' => Auth::id(),
            ]);

            // تحميل العلاقة مع المستخدم
            $comment->load('user');

            preg_match_all('/@([\p{L}\s]+)#/u', $request->content, $matches);

            Log::info('Matched mentions:', $matches);

            if (!empty($matches[1])) {
                $names = array_map('trim', $matches[1]); // إزالة المسافات الزائدة
                Log::info('Mentioned names:', ['names' => $names]);
                // العثور على المستخدمين بواسطة username
                $mentionedUsers = Employees::whereIn('name', $names)->orWhere('nickname', $names)->get();
                Log::info('Mentioned Users found:', $mentionedUsers->toArray());



                foreach ($mentionedUsers as $mentionedUser) {
                    SessionCommentMention::create([
                        'session_comment_id' => $comment->id,
                        'mentioner_user_id'  => Auth::id(),
                        'mentioned_user_id'  => $mentionedUser->user->id,
                    ]);

                    Log::info("Mention created for user ID: {$mentionedUser->id}");


                    $mentionedUser->user->notify(new CommentMentioned($comment, Auth::user(), $mentionedUser->user->id, $session));
                }
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'comment' => [
                    'id'            => $comment->id,
                    'content'       => $comment->content,
                    'created_at'    => $comment->created_at->format('Y-m-d H:i:s'),

                    'user' => [
                        'id'        => $comment->user->id,
                        'name'      => $comment->user->name,

                    ],

                    'can_edit_delete' => true,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            // تسجيل الخطأ (اختياري)
            Log::error('Error adding comment with mentions: ' . $e->getMessage());

            // إعادة استجابة الخطأ
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة التعليق. يرجى المحاولة مرة أخرى.',
            ], 500);
        }
    }

    // دالة لعرض تعليق واحد
    // public function show(Session $session, SessionComment $comment)
    // {
    //     // التأكد من أن التعليق ينتمي إلى الجلسة
    //     if ($comment->session_id !== $session->id) {
    //         return response()->json(['success' => false, 'message' => 'التعليق غير موجود في هذه الجلسة.'], 404);
    //     }

    //     // التأكد من أن المستخدم هو صاحب التعليق
    //     if ($comment->user_id !== Auth::id()) {
    //         return response()->json(['success' => false, 'message' => 'غير مصرح لك بعرض هذا التعليق.'], 403);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'comment' => [
    //             'id' => $comment->id,
    //             'content' => $comment->content,
    //         ],
    //     ]);
    // }

    // // دالة لتحديث التعليق
    // public function update(Request $request, Session $session, SessionComment $comment)
    // {
    //     // التأكد من أن التعليق ينتمي إلى الجلسة
    //     if ($comment->session_id !== $session->id) {
    //         return response()->json(['success' => false, 'message' => 'التعليق غير موجود في هذه الجلسة.'], 404);
    //     }

    //     // التأكد من أن المستخدم هو صاحب التعليق
    //     if ($comment->user_id !== Auth::id()) {
    //         return response()->json(['success' => false, 'message' => 'غير مصرح لك بتعديل هذا التعليق.'], 403);
    //     }

    //     $request->validate([
    //         'content' => 'required|string|max:1000',
    //     ]);

    //     DB::beginTransaction();
    //     try {
    //         $comment->update(['content' => $request->content]);

    //         preg_match_all('/@([\p{L}\s]+)#/u', $request->content, $matches);
    //         $names = array_map('trim', $matches[1]);

    //         // حذف الإشارات القديمة
    //         SessionCommentMention::where('session_comment_id', $comment->id)->delete();

    //         $oldNotifications = DatabaseNotification::where('data->comment_id', $comment->id)->get();
    //         foreach ($oldNotifications as $notification) {
    //             $notification->delete();
    //         }

    //         // إضافة إشارات جديدة
    //         $mentionedUsers = User::whereIn('name', $names)->get();
    //         foreach ($mentionedUsers as $mentionedUser) {
    //             SessionCommentMention::create([
    //                 'session_comment_id' => $comment->id,
    //                 'mentioner_user_id' => Auth::id(),
    //                 'mentioned_user_id' => $mentionedUser->id,
    //             ]);

    //             $mentionedUser->notify(new CommentMentioned($comment, Auth::user(), $mentionedUser->id, $session));
    //         }

    //         DB::commit();



    //         return response()->json(['success' => true, 'comment' => $comment]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء تعديل التعليق.'], 500);
    //     }

    //     // $comment->content = $request->content;
    //     // $comment->save();

    //     // return response()->json(['success' => true, 'message' => 'تم تحديث التعليق بنجاح.']);
    // }

    // دالة لحذف التعليق
    // public function destroy(Session $session, SessionComment $comment)
    // {
    //     // التأكد من أن التعليق ينتمي إلى الجلسة
    //     if ($comment->session_id !== $session->id) {
    //         return response()->json(['success' => false, 'message' => 'التعليق غير موجود في هذه الجلسة.'], 404);
    //     }

    //     // التأكد من أن المستخدم هو صاحب التعليق
    //     if ($comment->user_id !== Auth::id()) {
    //         return response()->json(['success' => false, 'message' => 'غير مصرح لك بحذف هذا التعليق.'], 403);
    //     }

    //     $comment->delete();

    //     return response()->json(['success' => true, 'message' => 'تم حذف التعليق بنجاح.']);
    // }



    // public function destroy(Session $session, $commentId)
    // public function destroy(Session $session, SessionComment $comment)
    // {
    //     // التأكد من أن التعليق ينتمي إلى الجلسة
    //     if ($comment->session_id !== $session->id) {
    //         return response()->json(['success' => false, 'message' => 'التعليق غير موجود في هذه الجلسة.'], 404);
    //     }

    //     // التأكد من أن المستخدم هو صاحب التعليق
    //     if ($comment->user_id !== Auth::id()) {
    //         return response()->json(['success' => false, 'message' => 'غير مصرح لك بحذف هذا التعليق.'], 403);
    //     }
    //     DB::beginTransaction();

    //     try {
    //         // $comment = $session->comments()->findOrFail($comment->id);

    //         // حذف الإشارات المرتبطة بالتعليق
    //         SessionCommentMention::where('session_comment_id', $comment->id)->delete();

    //         // حذف الإشعارات المرتبطة بالتعليق
    //         $notifications = DatabaseNotification::where('data->comment_id', $comment->id)->get();
    //         foreach ($notifications as $notification) {
    //             $notification->delete();
    //         }
    //         // حذف التعليق
    //         $comment->delete();

    //         DB::commit();

    //         return response()->json(['success' => true, 'message' => 'تم حذف التعليق بنجاح.']);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء حذف التعليق.'], 500);
    //     }
    // }
}
