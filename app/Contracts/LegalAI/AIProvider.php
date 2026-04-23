<?php

namespace App\Contracts\LegalAI;

interface AIProvider
{

    /*
    |--------------------------------------------------------------------------
    | Generate Response
    |--------------------------------------------------------------------------
    | This method is responsible for generating a response based on the provided messages and options.
    */
    public function generateResponse(array $messages, array $options = []): string;



    /*
    |--------------------------------------------------------------------------
    | يجهز بيانات طلب التدفق (URL, Headers, Payload) لإرسالها لاحقًا.
    |--------------------------------------------------------------------------
    */
    public function prepareStreamRequest(array $messages): array;
}
