<?php

use App\Models\AIConversation;
use App\Models\LegalAI\AiChat;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });


// Broadcast::channel('chat.{receiver_id}', function ($user, $receiver_id) {
//     return (int) $user->id === (int) $receiver_id;
// });





Broadcast::channel('chat.{user1_id}.{user2_id}', function ($user, $user1_id, $user2_id) {
    $ids = [(int) $user1_id, (int) $user2_id];
    $canListen = in_array($user->id, $ids);

    // Log::info('Authenticating channel', [
    //     'user_id' => $user->id,
    //     'channel_users' => $ids,
    //     'can_listen' => $canListen
    // ]);

    return $canListen;
});

// التذكيرات


Broadcast::channel('user.{id}', function ($user, $id) {
    $authorized = (int) $user->id === (int) $id;
    // Log::info('Channel authorization attempt:', [
    //     'user_id' => $user->id,
    //     'channel_id' => $id,
    //     'authorized' => $authorized,
    // ]);
    return $authorized;
});





//==============================================================
//===[ قاعدة التصريح الصحيحة لمحادثات الذكاء الاصطناعي ]=========
//==============================================================
Broadcast::channel('ai-chat-channel.{chatId}', function ($user, $chatId) {
    return AiChat::where('id', $chatId)
        ->where('user_id', $user->id)
        ->exists();
});
