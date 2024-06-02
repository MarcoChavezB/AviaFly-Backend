<?php

use App\Http\Controllers\AnalyticController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TurnController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\PendingController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FlightHistoryController;
use App\Http\Controllers\InfoFlightController;
use App\Http\Controllers\PaymentsController;

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

Route::any('/unauthorized', function () {
    return response()->json([
        'message' => 'Unauthorized'
    ], 401);
})->name('unauthorized');


Route::prefix('/avia')->group(function () {

    Route::post('login', [UserController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/authenticatetoken', function () {
            return response()->json([
                'status' => true
            ]);
        });


        Route::post('logout', [UserController::class, 'logout']);

        Route::prefix('/instructor')->group(function () {
            Route::get('/get/periods', [InstructorController::class, 'getPeriods']);
        });
    });
});

Route::prefix('/analitics')->group(function () {
    Route::get('/get/principal', [AnalyticController::class, 'getCardData']);
    Route::get('/get/enrollments/year', [AnalyticController::class, 'getEnrollmentsYear']);
    Route::get('/get/activity/week', [AnalyticController::class, 'getWeekActivity']);
});

Route::prefix('/pendings')->middleware('auth:sanctum')->group(function () {
    Route::get('/get/all', [PendingController::class, 'index']);
    Route::delete('/destroy/{id}', [PendingController::class, 'destroy']);
    Route::post('/create', [PendingController::class, 'create']);
    Route::post('/update', [PendingController::class, 'update']);
});

Route::prefix('/student')->middleware('auth:sanctum')->group(function () {
    Route::get('/index', [StudentController::class, 'index']);
    Route::get('/index/{name}', [StudentController::class, 'indexByName']);
    Route::post('/enroll', [CourseController::class, 'create']);
    Route::post('/create', [StudentController::class, 'create']); // Esto puede hacerlo: root, admin
    Route::get('/show/{id}', [StudentController::class, 'show'])->where('id', '[0-9]+');
    Route::put('/update/grade', [StudentController::class, 'updateGrade']);

    Route::middleware('auth:sanctum')->group(function (){
        Route::get('/get', [StudentController::class, 'getStudents']); // Esto puede hacerlo: root, admin
    });
});

Route::prefix('/base')->group(function () {
    Route::post('/create', [BaseController::class, 'create']);
    Route::get('/get', [BaseController::class, 'getBases']);
});

Route::prefix('/instructor')->group(function () {
    Route::post('/create', [InstructorController::class, 'create']);
    Route::get('/get', [InstructorController::class, 'getInstructors']);

    Route::middleware('auth:sanctum')->group(function (){
        //Endpoints solo para rol instructor.
        Route::get('/get/careers', [InstructorController::class, 'getInstructorCareers']);
        Route::get('/get/students', [InstructorController::class, 'getStudentsByInstructor']);
    });
});

Route::prefix('/career')->group(function () {
    Route::post('/create', [CareerController::class, 'create']);
    Route::get('/get', [CareerController::class, 'getCareers']);
    Route::get('/get-with-subjects', [CareerController::class, 'getCareersWithSubjects']);
});



Route::prefix('/subject')->group(function () {
    Route::post('/create', [SubjectController::class, 'create']);
    Route::get('/get', [SubjectController::class, 'getSubjects']);
    Route::get('/get-info-calendar/{id_career}', [SubjectController::class, 'getSubjectsInfoCalendar']);
});

Route::prefix('/employes')->middleware('auth:sanctum')->group(function () {
    Route::get('/get/tasks', [UserController::class, 'getEmployes']);
});

Route::prefix('/contact')->group(function () {
    Route::post('/create', [ContactController::class, 'create']);
    Route::get('/get', [ContactController::class, 'index2']);
    Route::get('/show/{id}', [ContactController::class, 'show'])->where('id', '[0-9]+');
    Route::delete('/destroy/{id}', [ContactController::class, 'destroy'])->where('id', '[0-9]+');
    Route::put('/update/{id}', [ContactController::class, 'update'])->where('id', '[0-9]+');
    Route::get('/index', [ContactController::class, 'index2']);
});

Route::prefix('/turn')->group(function () {
    Route::get('/get', [TurnController::class, 'index']);
});

Route::prefix('/student')->middleware('auth:sanctum')->group(function () {
    Route::get('/flight/index', [StudentController::class, 'indexSimulator']);
    Route::get('/flight/index/{name}', [StudentController::class, 'getStudentSimulatorByName']);
    Route::get('/flight/report/{id}', [StudentController::class, 'getInfoVueloAlumno']);
    Route::get('/flight/employees/bystudent/{id}', [StudentController::class, 'getEmployeesByStudent']);
    Route::post('/flight/store', [StudentController::class, 'storeFlight']);
});

Route::prefix('/flights')->middleware('auth:sanctum')->group(function () {
    Route::get('/get', [InfoFlightController::class, 'index']);
   Route::get('/get/flight/data/{id_student}', [FlightHistoryController::class, 'flightsData']); 
   Route::get('/get/flight/report/{id_flight}', [FlightHistoryController::class, 'reportDataById']);
});

Route::prefix('/payments')->middleware('auth:sanctum')->group(function () {
    Route::post('/amount', [PaymentsController::class, 'addPayment']);
});

Route::prefix('/flights/histroy')->middleware('auth:sanctum')->group(function () {
    Route::post('/already/date/reserved', [FlightHistoryController::class, 'isDateReserved']);
});
