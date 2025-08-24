<?php

use App\Http\Controllers\Api\FingerPrintController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::post('/fingerprint/register', FingerPrintController::class);
