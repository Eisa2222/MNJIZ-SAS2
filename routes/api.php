<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::middleware([])->group(function () {


    Route::get('/me', function () {
        return Auth::user(); // أو request()->user()
    });
});
