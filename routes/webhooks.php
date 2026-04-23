<?php

declare(strict_types=1);

use App\Http\Controllers\Webhooks\MoyasarWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Webhook Routes (central — not tenant-resolved)
|--------------------------------------------------------------------------
|
| Moyasar posts JSON to /webhooks/moyasar. The endpoint is EXPLICITLY
| outside the CSRF group (payment gateways don't speak CSRF), so we add
| this file under 'api' middleware group (throttled, no session).
|
| Signature verification runs INSIDE the controller via
| MoyasarWebhookVerifier; tampered requests get 401.
|
*/

Route::middleware('api')->group(function () {
    Route::post('webhooks/moyasar', MoyasarWebhookController::class)
        ->name('webhooks.moyasar');
});
