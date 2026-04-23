<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // إضافة معالج خاص للاستثناء
        $this->renderable(function (PolicyValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'خطأ في التحقق',
                    'errors' => ['policy' => [$e->getMessage()]]
                ], 422);
            }

            // تحويله إلى استثناء تحقق لعرضه كـ toast
            throw $e->toValidationException();
        });
    }

    public function render($request, Throwable $exception)
    {
        if (app()->environment('production')) {
            // رسائل أخطاء عامة في بيئة الإنتاج
            if ($exception instanceof ValidationException) {
                return redirect()->back()->with('error', 'هناك بعض الأخطاء في البيانات المدخلة. يرجى تصحيحها والمحاولة مرة أخرى.');
            }
        }

        // رسائل أخطاء مفصلة في بيئات أخرى
        return parent::render($request, $exception);
    }
}
