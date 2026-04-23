<?php

namespace App\Http\Controllers\LegalAffair\Lawsuit\Note;

use App\Http\Controllers\Controller;
use App\Models\LegalAffair\Lawsuit\Lawsuit;
use App\Models\LegalAffair\Lawsuit\Note\LawsuitNote;
use App\Models\LegalAffair\Lawsuit\Note\LawsuitNoteReply;
use App\Models\User;
use App\Notifications\NoteCommentMentioned;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LawsuitNoteController extends Controller
{
    public function store(Request $request, Lawsuit $lawsuit)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'text'          => 'required|string',
            'type'          => 'required|in:private,requires_manager_reply,public',
        ]);

        $lawsuit->notes()->create([
            'title'         => $request->input('title'),
            'text'          => $request->input('text'),
            'type'          => $request->input('type'),
            'user_id'       => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'تم إضافة الملاحظة بنجاح.')->withFragment('notes');
    }

    // عرض تفاصيل الملاحظة
    public function show(Lawsuit $lawsuit, LawsuitNote $note)
    {
        if ($note->lawsuit_id !== $lawsuit->id) {
            abort(404);
        }

        $note->load(['replies.user', 'user']);

        return response()->json([
            'id' => $note->id,
            'title' => $note->title,
            'text' => $note->text,
            'type' => $note->type,
            'replies' => $note->replies->map(function ($reply) {
                return [
                    'id' => $reply->id,
                    'reply_text' => $reply->reply_text,
                    'created_at' => $reply->created_at->toDateTimeString(),
                    'hijri_created_at' => $reply->hijri_created, // التاريخ الهجري
                    'user_id' => $reply->user_id, // إضافة user_id

                    'user' => [
                        'name' => $reply->user->name,
                    ],

                    'can_edit_delete' => $reply->user_id === Auth::id(), // تحقق ما إذا كان المستخدم الحالي هو صاحب الرد

                ];
            }),
        ]);
    }

    // تعديل الملاحظة
    public function update(Request $request, Lawsuit $lawsuit, LawsuitNote $note)
    {
        if ($note->lawsuit_id !== $lawsuit->id) {
            abort(404);
        }

        if (Auth::id() !== $note->user_id) {
            abort(403, 'غير مسموح لك بتعديل هذه الملاحظة.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'text' => 'required|string',
            'type' => 'required|in:private,requires_manager_reply,public',
        ]);

        $note->update($validated);

        // تسجيل نشاط مخصص
        activity()
            ->performedOn($note)
            ->causedBy(Auth::user())
            ->withProperties(['attributes' => $validated])
            ->log('تم تحديث ');

        return response()->json([
            'success' => true,
            'note' => $note->load('user'),
        ]);
    }

    public function destroy(Lawsuit $lawsuit, LawsuitNote $note)
    {
        if ($note->lawsuit_id !== $lawsuit->id) {
            abort(404);
        }

        $note->delete();

        return redirect()->back()->with('success', 'تم حذف الملاحظة بنجاح.')->withFragment('notes');
    }

    // الردود


    // إضافة رد جديد إلى الملاحظة
    public function storeReply(Request $request, Lawsuit $lawsuit, LawsuitNote $note)
    {
        $request->validate([
            'reply_text' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            $reply = $note->replies()->create([
                'reply_text' => $request->reply_text,
                'user_id' => Auth::id(),
            ]);

            preg_match_all('/@([\p{L}\s]+)#/u', $request->reply_text, $matches);

            Log::info('Matched mentions:', $matches);

            if (!empty($matches[1])) {
                $names = array_map('trim', $matches[1]); // إزالة المسافات الزائدة
                Log::info('Mentioned names:', ['names' => $names]);
                // العثور على المستخدمين بواسطة username
                $mentionedUsers = User::whereIn('name', $names)->get();
                Log::info('Mentioned Users found:', $mentionedUsers->toArray());



                foreach ($mentionedUsers as $mentionedUser) {
                    NoteCommentMention::create([
                        'note_comment_id' => $reply->id,
                        'mentioner_user_id' => Auth::id(),
                        'mentioned_user_id' => $mentionedUser->id,
                    ]);

                    Log::info("Mention created for user ID: {$mentionedUser->id}");

                    // إرسال الإشعار مع تمرير userId

                    $mentionedUser->notify(new NoteCommentMentioned($reply, Auth::user(), $mentionedUser->id, $note));
                }
            }




            DB::commit();
            return response()->json([
                'success' => true,
                'reply' => [
                    'id' => $reply->id,
                    'reply_text' => $reply->reply_text,
                    'created_at' => $reply->created_at->toDateTimeString(),
                    'hijri_created_at' => $reply->hijri_created, // التاريخ الهجري
                    'user' => [
                        'name' => $reply->user->name,
                    ],
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

    public function showReply(Lawsuit $lawsuit, LawsuitNote $note, LawsuitNoteReply $reply)
    {
        // التأكد من أن الرد ينتمي إلى الملاحظة
        if ($reply->lawsuit_note_id !== $note->id) {
            return response()->json(['success' => false, 'message' => 'الرد غير موجود في هذه الملاحظة.'], 404);
        }

        // التأكد من أن المستخدم هو صاحب الرد
        if ($reply->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'غير مصرح لك بعرض هذا الرد.'], 403);
        }

        return response()->json([
            'success' => true,
            'reply' => [
                'id' => $reply->id,
                'reply_text' => $reply->reply_text,
            ],
        ]);
    }


    // دالة تحديث الرد
    public function updateReply(Request $request, Lawsuit $lawsuit, LawsuitNote $note, LawsuitNoteReply $reply)
    {
        // التأكد من أن المستخدم هو صاحب الرد
        if ($reply->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'غير مصرح لك بتعديل هذا الرد.'], 403);
        }

        $request->validate([
            'reply_text' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $reply->update(['reply_text' => $request->reply_text]);

            preg_match_all('/@([\p{L}\s]+)#/u', $request->reply_text, $matches);
            $names = array_map('trim', $matches[1]); // إزالة المسافات الزائدة

            // حذف الإشارات القديمة
            NoteCommentMention::where('note_comment_id', $reply->id)->delete();

            $oldNotifications = DatabaseNotification::where('data->reply_id', $reply->id)->get();
            foreach ($oldNotifications as $notification) {
                $notification->delete();
            }

            // إضافة إشارات جديدة
            $mentionedUsers = User::whereIn('name', $names)->get();
            foreach ($mentionedUsers as $mentionedUser) {
                NoteCommentMention::create([
                    'note_comment_id' => $reply->id,
                    'mentioner_user_id' => Auth::id(),
                    'mentioned_user_id' => $mentionedUser->id,
                ]);

                $mentionedUser->notify(new NoteCommentMentioned($reply, Auth::user(), $mentionedUser->id, $note));
            }

            // if (!empty($matches[1])) {
            // }

            $oldText = $reply->reply_text; // النص القديم

            $reply->update(['reply_text' => $request->reply_text]); // تحديث النص


            DB::commit();
            return response()->json(['success' => true, 'reply' => $reply]);
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





        $reply->reply_text = $request->reply_text;
        $reply->save();

        return response()->json(['success' => true, 'reply' => $reply]);
    }

    // دالة حذف الرد
    public function destroyReply(Lawsuit $lawsuit, LawsuitNote $note, LawsuitNoteReply $reply)
    {
        // التأكد من أن المستخدم هو صاحب الرد
        if ($reply->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'غير مصرح لك بحذف هذا الرد.'], 403);
        }

        DB::beginTransaction();

        try {
            // حذف الإشارات القديمة
            NoteCommentMention::where('note_comment_id', $reply->id)->delete();

            $oldNotifications = DatabaseNotification::where('data->reply_id', $reply->id)->get();
            foreach ($oldNotifications as $notification) {
                $notification->delete();
            }
            // حذف التعليق
            $reply->delete();

            $deletedReply = [
                'reply_id' => $reply->id,
                'reply_text' => $reply->reply_text, // النص قبل الحذف
            ];

            $reply->delete();




            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error adding comment with mentions: ' . $e->getMessage());

            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'حدث خطأ ببلبب حذف التعليق.'], 500);
        }





        // return response()->json(['success' => true]);
    }

    // دالة عرض الرد

}
