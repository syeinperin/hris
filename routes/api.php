<?php

use App\Http\Controllers\Api\FingerPrintController;
use Illuminate\Http\Request;
<<<<<<< HEAD
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


=======
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

>>>>>>> 27eeb7528e4de2414f2ae262330df00fd42afdbc
Route::post('/fingerprint/register', FingerPrintController::class);
