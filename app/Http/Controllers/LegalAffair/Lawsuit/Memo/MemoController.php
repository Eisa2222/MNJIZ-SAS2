<?php

namespace App\Http\Controllers\LegalAffair\Lawsuit\Memo;


use App\Http\Controllers\Controller;
use App\Models\LegalAffair\Lawsuit\Lawsuit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MemoController extends Controller
{
    public function store(Request $request, $lawsuitId)
    {
        $lawsuit = Lawsuit::findOrFail($lawsuitId);

        $validated = $request->validate([
            'type'          => 'required|in:plaintiff,defendant',
            'text'          => 'required|string',
            'attachment'    => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
        ], [
            'type.required'         => 'نوع المذكرة مطلوب.',
            'type.in'               => 'نوع المذكرة غير صالح.',
            'text.required'         => 'نص المذكرة مطلوب.',
            'text.string'           => 'نص المذكرة يجب أن يكون نصًا صالحًا.',
            'attachment.file'       => 'المرفق يجب أن يكون ملفًا صالحًا.',
            'attachment.mimes'      => 'نوع المرفق غير مدعوم.',
            'attachment.max'        => 'حجم المرفق يجب ألا يتجاوز 2MB.',
        ]);

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('lawsuit/memos', 'public');
            $validated['attachment'] = $path;
        }

        $validated['lawsuit_id'] = $lawsuit->id;

        $memo = $lawsuit->memos()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'تمت إضافة المذكرة بنجاح.',
            'data' => [
                'id'                => $memo->id,
                'type'              => $memo->type,
                'text'              => $memo->text,
                'attachment'        => $memo->attachment,
                'attachment_url'    => $memo->attachment ? asset('storage/' . $memo->attachment) : null,
            ],
        ]);
    }


    public function update(Request $request, $lawsuitId, $memoId)
    {
        $lawsuit = Lawsuit::findOrFail($lawsuitId);
        $memo = $lawsuit->memos()->findOrFail($memoId);

        $validated = $request->validate([
            'type'              => 'required|in:plaintiff,defendant',
            'text'              => 'required|string',
            'attachment'        => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
        ], [
            'type.required'             => 'نوع المذكرة مطلوب.',
            'type.in'                   => 'نوع المذكرة غير صالح.',
            'text.required'             => 'نص المذكرة مطلوب.',
            'text.string'               => 'نص المذكرة يجب أن يكون نصًا صالحًا.',
            'attachment.file'           => 'المرفق يجب أن يكون ملفًا صالحًا.',
            'attachment.mimes'          => 'نوع المرفق غير مدعوم.',
            'attachment.max'            => 'حجم المرفق يجب ألا يتجاوز 2MB.',
        ]);


        if ($request->hasFile('attachment')) {
            // حذف المرفق القديم إذا وجد
            if ($memo->attachment) {
                Storage::disk('public')->delete($memo->attachment);
            }
            $path = $request->file('attachment')->store('lawsuit/memos', 'public');
            $validated['attachment'] = $path;
        }

        $memo->update($validated);


        return response()->json([
            'success' => true,
            'message' => 'تم تحديث المذكرة بنجاح.',
            'data' => [
                'id'                => $memo->id,
                'type'              => $memo->type,
                'text'              => $memo->text,
                'attachment'        => $memo->attachment,
                'attachment_url'    => $memo->attachment ? asset('storage/' . $memo->attachment) : null,
            ],
        ]);
    }


    public function destroy($lawsuitId, $memoId)
    {
        $lawsuit = Lawsuit::findOrFail($lawsuitId);
        $memo    = $lawsuit->memos()->findOrFail($memoId);

        if ($memo->attachment) {
            Storage::disk('public')->delete($memo->attachment);
        }
        $memo->delete();

        return response()->json(['message' => 'تم حذف المذكرة بنجاح.']);
    }
}
