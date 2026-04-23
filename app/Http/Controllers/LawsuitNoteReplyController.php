<?php

namespace App\Http\Controllers;

use App\Models\LawsuitNote;
use App\Models\LawsuitNoteReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LawsuitNoteReplyController extends Controller
{
    /**
     * Store a newly created reply in storage.
     */
    // public function store(Request $request, $noteId)
    // {
    //     $request->validate([
    //         'reply_text' => 'required|string',
    //     ]);

    //     $note = LawsuitNote::findOrFail($noteId);

    //     $reply = $note->replies()->create([
    //         'reply_text' => $request->reply_text,
    //         'user_id' => Auth::id(),
    //     ]);

    //     return response()->json(['message' => 'رد تم إضافته بنجاح', 'reply' => $reply], 201);
    // }

    // باقي الدوال مثل show, update, destroy...
}
