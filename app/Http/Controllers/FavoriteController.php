<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class FavoriteController extends Controller
{
    /**
     * تبديل حالة المفضلة (إضافة/إزالة)
     */
    /**
     * تبديل حالة المفضلة (إضافة/إزالة)
     */
    public function toggleFavorite(Request $request)
    {
        Cache::forget('user_favorites_' . auth()->id());
        // التحقق من البيانات
        $request->validate([
            'page_name' => 'required|string',
            'page_url' => 'required|string',
            'action' => 'nullable|string',
            'favorite_id' => 'nullable|integer',
        ]);

        $user = Auth::user();
        $pageName = $request->page_name;
        $pageUrl = $request->page_url;
        $action = $request->action;
        $favoriteId = $request->favorite_id;

       
        // إذا كان الطلب هو للإزالة
        if ($action === 'remove') {
            // البحث عن كل المفضلات بنفس URL للمستخدم الحالي
            $favorites = Favorite::where('user_id', $user->id)
                ->where('page_url', $pageUrl)
                ->get();

            if ($favorites->count() > 0) {
                foreach ($favorites as $favorite) {
                    $favorite->delete();
                }

                return response()->json([
                    'status' => 'removed',
                    'message' => 'تمت إزالة الصفحة من المفضلة',
                    'count' => $favorites->count()
                ]);
            }

            return response()->json([
                'status' => 'not_found',
                'message' => 'المفضلة غير موجودة'
            ]);
        }

        // البحث عن المفضلة الحالية إن وجدت (استخدام URL فقط)
        $favorite = Favorite::where('user_id', $user->id)
            ->where('page_url', $pageUrl)
            ->first();

        // إذا كانت موجودة، نقوم بتحديثها
        if ($favorite) {
            $favorite->page_name = $pageName;
            $favorite->save();

            return response()->json([
                'status' => 'updated',
                'message' => 'تم تحديث المفضلة',
                'favorite' => [
                    'id' => $favorite->id,
                    'page_name' => $favorite->page_name,
                    'page_url' => $favorite->page_url
                ]
            ]);
        }

        // إذا لم تكن موجودة، نقوم بإضافتها
        $favorite = Favorite::create([
            'user_id' => $user->id,
            'page_name' => $pageName,
            'page_url' => $pageUrl,
        ]);


        return response()->json([
            'status' => 'added',
            'message' => 'تمت إضافة الصفحة إلى المفضلة',
            'favorite' => [
                'id' => $favorite->id,
                'page_name' => $favorite->page_name,
                'page_url' => $favorite->page_url
            ]
        ]);
    }

    /**
     * التحقق مما إذا كانت الصفحة في المفضلة
     */
    /**
     * التحقق مما إذا كانت الصفحة في المفضلة
     */
    public function checkFavorite(Request $request)
    {
        // التحقق من المصادقة
        if (!Auth::check()) {
            return response()->json([
                'is_favorite' => false,
                'message' => 'يجب تسجيل الدخول أولا'
            ]);
        }

        $pageUrl = $request->input('page_url', '');
        $pageName = $request->input('page_name', '');

        if (empty($pageUrl) && empty($pageName)) {
            return response()->json([
                'is_favorite' => false,
                'message' => 'لم يتم تحديد معلومات الصفحة'
            ]);
        }

        $user = Auth::user();
        $query = Favorite::where('user_id', $user->id);

        // البحث بالـ URL أولاً إذا كان متوفراً
        if (!empty($pageUrl)) {
            $query->where('page_url', $pageUrl);
        } else {
            // وإلا البحث بالاسم
            $query->where('page_name', $pageName);
        }

        $favorite = $query->first();
        $isFavorite = !is_null($favorite);

        return response()->json([
            'is_favorite' => $isFavorite,
            'favorite' => $isFavorite ? [
                'id' => $favorite->id,
                'page_name' => $favorite->page_name,
                'page_url' => $favorite->page_url,
            ] : null
        ]);
    }

    /**
     * عرض كل المفضلات للمستخدم الحالي
     */
    public function index()
    {
        $user = Auth::user();
        $favorites = Favorite::where('user_id', $user->id)->get();

        return view('favorites.index', compact('favorites'));
    }
}