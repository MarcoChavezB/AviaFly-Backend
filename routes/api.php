<?php

use App\Http\Controllers\CareerController;
use App\Http\Controllers\InstructorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\StudentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('/user')->group(function () {

    Route::post('login');


    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/authenticatetoken', function () {
            return response()->json([
                'status' => true
            ]);
        });

    });
});

Route::prefix('/student')->group(function () {
    Route::post('/enroll', [CourseController::class, 'create']);
    Route::post('/create', [StudentController::class, 'create']);
});

Route::prefix('/base')->group(function () {
    Route::post('/create', [BaseController::class, 'create']);
    Route::get('/get', [BaseController::class, 'getBases']);
});

Route::prefix('/instructor')->group(function () {
    Route::post('/create', [InstructorController::class, 'create']);
});

Route::prefix('/career')->group(function () {
    Route::post('/create', [CareerController::class, 'create']);
});
