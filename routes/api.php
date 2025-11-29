<?php

use Illuminate\Support\Facades\Route;

Route::middleware('api')
    ->prefix('v1')
    ->group(function () {
    });
