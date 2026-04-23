<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\X\XService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContentPublishController extends Controller
{
   public function testPost(Request $request, XService $x)
    {
        try {
            // التحقق من وجود النص
            $message = $request->input('message', 'مرحباً من Laravel! 🚀 اختبار API v2 - ' . now()->format('Y-m-d H:i:s'));
            
            if (empty(trim($message))) {
                return back()->withErrors(['error' => 'يرجى إدخال نص التغريدة']);
            }

            // نشر التغريدة باستخدام API v2
            $tweet = $x->tweet($message);

            return back()->with('success', 
                'تم النشر على منصة X بنجاح! ' . 
                'معرف التغريدة: ' . ($tweet['data']['id'] ?? 'غير معروف') .
                ' | النص: ' . ($tweet['data']['text'] ?? 'غير معروف')
            );

        } catch (\Exception $e) {
            Log::error('Twitter API v2 Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'فشل في النشر على X: ' . $e->getMessage()]);
        }
    }

    /**
     * اختبار الاتصال فقط
     */
    public function testConnection(XService $x)
    {
        try {
           
            return view('twitterXXXXX', [
               
            ]);
            // return response()->json([
            //     'success' => true,
            //     'message' => 'الاتصال ناجح!',
            //     'user' => [
            //         'name' => $credentials['name'] ?? 'غير معروف',
            //         'screen_name' => $credentials['screen_name'] ?? 'غير معروف',
            //         'followers_count' => $credentials['followers_count'] ?? 0,
            //     ]
            // ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل الاتصال: ' . $e->getMessage()
            ], 400);
        }
    }
}