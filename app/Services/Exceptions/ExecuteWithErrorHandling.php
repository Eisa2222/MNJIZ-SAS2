<?php
namespace App\Services\Exceptions;
use App\Contracts\ErrorHandlerInterface;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;


class ExecuteWithErrorHandling implements ErrorHandlerInterface

{
    public function execute(callable $callback, string $errorMessage = 'حدث خطأ في النظام')
    {
        try {

            return $callback();
        } catch (ValidationException $e) {
            Log::error('ValidationException: ' . $e->getMessage(), ['exception' => $e]);

            throw $e;
        } catch (QueryException $e) {

            Log::error('QueryException: ' . $e->getMessage(), ['exception' => $e]);

            throw new \Exception($errorMessage . '. يرجى المحاولة لاحقاً.');
        } catch (\Exception $e) {

            Log::error('Exception: ' . $e->getMessage(), ['exception' => $e]);

            throw new \Exception('حدث خطأ غير متوقع. يرجى المحاولة لاحقاً.');
        }
    }
}
