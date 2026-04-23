<?php
// app/Contracts/ErrorHandlerInterface.php

namespace App\Contracts;

interface ErrorHandlerInterface
{
    /**
     * ينفّذ الـ callback ويعالج الأخطاء وفق الرسالة الافتراضية.
     *
     * @param  callable  $callback
     * @param  string    $errorMessage
     * @return mixed
     *
     * @throws \Exception|\Illuminate\Validation\ValidationException
     */
    public function execute(callable $callback, string $errorMessage = 'حدث خطأ في النظام');
}
