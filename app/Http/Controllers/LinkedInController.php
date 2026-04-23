<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Social\LinkedInService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;

/**
 * LinkedIn API Controller
 * 
 * كنترولر للتعامل مع عمليات LinkedIn API
 * 
 * @package App\Http\Controllers\Api
 */
class LinkedInController extends Controller
{
    private LinkedInService $linkedInService;
    private LoggerInterface $logger;

    /**
     * @param LinkedInService $linkedInService
     * @param LoggerInterface $logger
     */
    public function __construct(LinkedInService $linkedInService, LoggerInterface $logger)
    {
        $this->linkedInService = $linkedInService;
        $this->logger = $logger;
    }



    public function index()
    {
        try {

            return view('twitterXXXXX', []);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل الاتصال: ' . $e->getMessage()
            ], 400);
        }
    }


    /**
     * اختبار الاتصال مع LinkedIn
     * 
     * @return JsonResponse
     */
    public function testConnection(): JsonResponse
    {
        try {
            $result = $this->linkedInService->testConnection();

            $statusCode = $result['success'] ? 200 : 400;

            return response()->json($result, $statusCode);
        } catch (\Exception $e) {
            $this->logger->error('LinkedIn connection test failed in controller', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء اختبار الاتصال'
            ], 500);
        }
    }

    /**
     * الحصول على معلومات المستخدم
     * 
     * @return JsonResponse
     */
    public function getUserInfo(): JsonResponse
    {
        try {
            $userInfo = $this->linkedInService->getUserInfo();

            if (!$userInfo) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن الحصول على معلومات المستخدم. تأكد من إعداد LinkedIn بشكل صحيح.'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم الحصول على معلومات المستخدم بنجاح',
                'data' => $userInfo
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to get LinkedIn user info in controller', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الحصول على معلومات المستخدم'
            ], 500);
        }
    }

    /**
     * نشر منشور نصي
     * 
     * @return JsonResponse
     */
    public function publishPost(Request $request): JsonResponse
    {
        try {
            $result = $this->linkedInService->publishPost("Advanced post contsent");

            $this->logger->info('LinkedIn post published successfully via controller', [
                'post_id' => $result['id'] ?? 'unknown',
                'content_length' => strlen($request->input('content'))
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم نشر المنشور بنجاح',
                'data' => $result
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\RuntimeException $e) {
            $this->logger->error('LinkedIn service error in publishPost', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'فشل في نشر المنشور: ' . $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            $this->logger->error('Unexpected error in publishPost controller', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ غير متوقع أثناء نشر المنشور'
            ], 500);
        }
    }

    /**
     * نشر منشور مع رابط
     * 
     * @return JsonResponse
     */
    public function publishPostWithLink(Request $request): JsonResponse
    {
        try {
            $result = $this->linkedInService->publishPostWithLink(
                "Content with link",
                "https://example.com",
                "This is a description of the link",
            );

            $this->logger->info('LinkedIn post with link published successfully via controller', [
                'post_id' => $result['id'] ?? 'unknown',
                'url' => $request->input('url')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم نشر المنشور مع الرابط بنجاح',
                'data' => $result
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\RuntimeException $e) {
            $this->logger->error('LinkedIn service error in publishPostWithLink', [
                'error' => $e->getMessage(),
                'url' => $request->input('url')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'فشل في نشر المنشور: ' . $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            $this->logger->error('Unexpected error in publishPostWithLink controller', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ غير متوقع أثناء نشر المنشور'
            ], 500);
        }
    }

    /**
     * حذف منشور
     * 
     * @return JsonResponse
     */
    public function deletePost(Request $request): JsonResponse
    {
        try {
            $result = $this->linkedInService->deletePost("urn:li:share:7342830572743872512");

            $statusCode = $result['success'] ? 200 : 400;

            if ($result['success']) {
                $this->logger->info('LinkedIn post deleted successfully via controller', [
                    'post_id' => "urn:li:share:7342830572743872512"
                ]);
            }

            return response()->json($result, $statusCode);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            $this->logger->error('Unexpected error in deletePost controller', [
                'post_id' => "urn:li:share:7342830572743872512",
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ غير متوقع أثناء حذف المنشور'
            ], 500);
        }
    }

    /**
     * تحديث منشور (حذف وإعادة إنشاء)
     * 
     * @return JsonResponse
     */
    public function updatePost(Request $request): JsonResponse
    {
        try {
            $result = $this->linkedInService->updatePostByRecreating(
                "urn:li:share:7342829631223341057",
                "New content for the post",
                
            );

            $statusCode = $result['success'] ? 200 : 400;

            if ($result['success']) {
                $this->logger->info('LinkedIn post updated successfully via controller', [
                    'old_post_id' => "urn:li:share:7342829631223341057",
                    'new_post_id' => $result['new_post_id'] ?? 'unknown'
                ]);
            }

            return response()->json($result, $statusCode);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            $this->logger->error('Unexpected error in updatePost controller', [
                'post_id' => "urn:li:share:7342829631223341057",
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ غير متوقع أثناء تحديث المنشور'
            ], 500);
        }
    }
}
