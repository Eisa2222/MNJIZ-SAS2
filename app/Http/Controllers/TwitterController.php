<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Social\Twitter\TwitterService;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;

class TwitterController extends Controller
{
    private TwitterService $twitter;

    public function __construct(TwitterService $twitter)
    {
        $this->twitter = $twitter;
    }

    /**
     * Show simple dashboard with tweet form and last user info.
     */
    public function index()
    // {
    //     $user = $this->twitter->getUser();


    //     return $user;
    //     // return view('social.twitter.index', compact('user'));
    // }
    {
        try {
            $tweet = $this->twitter->getUser();
            dd($tweet);
            return view('twitterXXXXX', []);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل الاتصال: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Handle posting a new tweet.
     */
    public function postTweet(Request $request)
    {
        // $request->validate([
        //     'text' => 'required|string|max:280',
        // ]);

        try {
            $tweet = $this->twitter->postTweet("hello world");
            Log::info('Tweet posted successfully', ['tweet' => $tweet]);
            return back()->with('success', 'Tweet posted successfully! ID: ' . ($tweet['id_str'] ?? ''));
        } catch (Exception $e) {
            Log::error('Failed to post tweet', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Failed to post tweet: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete a tweet by ID.
     */
    public function deleteTweet(string $id)
    {
        try {
            $this->twitter->deleteTweet($id);
            return back()->with('success', "Tweet {$id} deleted successfully.");
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete tweet: ' . $e->getMessage()]);
        }
    }
}
