<?php

use App\Http\Controllers\AirPlaneController;
use App\Http\Controllers\AnalyticController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\IncomesController;
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
use App\Http\Controllers\FlightCustomerController;
use App\Http\Controllers\FlightHistoryController;
use App\Http\Controllers\FlightPaymentController;
use App\Http\Controllers\InfoFlightController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SessionController;

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
        Route::get('/auth/check', function () {
            return response()->json([
                true
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
    Route::get('/get/debt', [AnalyticController::class, 'getTotalDebt']);
});

Route::prefix('/pendings')->middleware('auth:sanctum')->group(function () {
    Route::get('/get/all', [PendingController::class, 'index']);
    Route::delete('/destroy/{id}', [PendingController::class, 'destroy']);
    Route::post('/create', [PendingController::class, 'create']);
    Route::post('/update', [PendingController::class, 'update']);
});

Route::prefix('/students')->middleware('auth:sanctum')->group(function () {
    Route::get('/index', [StudentController::class, 'index']);
    Route::get('/index/{name}', [StudentController::class, 'indexByName']);
    Route::post('/enroll', [CourseController::class, 'create']);
    Route::post('/create', [StudentController::class, 'create']); // Esto puede hacerlo: root, admin
    Route::get('/show/{id}', [StudentController::class, 'show'])->where('id', '[0-9]+');
    Route::put('/update/grade', [StudentController::class, 'updateGrade']);
    Route::get('/flight/index', [StudentController::class, 'indexSimulator']);
    Route::get('/flight/index/{name}', [StudentController::class, 'getStudentSimulatorByName']);
    Route::get('/flight/report/{id}', [StudentController::class, 'getInfoVueloAlumno']);
    Route::get('/flight/employees/bystudent/{id}', [StudentController::class, 'getEmployeesByStudent']);
    Route::get('/index/syllabus', [StudentController::class, 'indexSyllabus']);
    Route::post('/flight/store', [StudentController::class, 'storeFlight']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/get', [StudentController::class, 'getStudents']); // Esto puede hacerlo: root, admin
        Route::get('/get/subjects/{id}', [StudentController::class, 'getStudentSubjects'])->where('id', '[0-9]+'); // Esto puede hacerlo: root, admin
        Route::post('/add/subject', [StudentController::class, 'addSubjectToStudent']); // Esto puede hacerlo: root, admin
        Route::delete('/delete/subject', [StudentController::class, 'deleteSubjectFromStudent']); // Esto puede hacerlo: root, admin
        Route::put('/change/instructor', [StudentController::class, 'changeInstructorFromStudentSubject']); // Esto puede hacerlo: root, admin
        Route::put('/update', [StudentController::class, 'update']); // Esto puede hacerlo: root, admin
        Route::get('/student/monthly-payments/{id}', [StudentController::class, 'getStudentMonthlyPayments'])->where('id', '[0-9]+'); // Esto puede hacerlo: root, admin
        Route::get('/student/owed-monthly-payments/{id}', [StudentController::class, 'getStudentAndOwedMonthlyPayments'])->where('id', '[0-9]+'); // Esto puede hacerlo: root, admin
        Route::get('/get/name-identification/{id}', [StudentController::class, 'getStudentNameAndIdentification'])->where('id', '[0-9]+'); // Esto puede hacerlo: root, admin
    });
});

Route::prefix('/bases')->group(function () {
    Route::post('/create', [BaseController::class, 'create']);
    Route::get('/get', [BaseController::class, 'getBases']);
});

Route::prefix('/instructors')->group(function () {
    Route::post('/create', [InstructorController::class, 'create']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/get/careers', [InstructorController::class, 'getInstructorCareers']); // Esto puede hacerlo: instructor
        Route::get('/get/subjects', [InstructorController::class, 'getInstructorSubjects']); // Esto puede hacerlo: instructor
        Route::get('/get/students', [InstructorController::class, 'getStudentsByInstructor']); // Esto puede hacerlo: instructor
        Route::get('/get/instructors-subjects', [InstructorController::class, 'getInstructorsSubjects']); // Esto puede hacerlo: root, admin
        Route::put('/update/instructors-subjects', [InstructorController::class, 'updateInstructorsSubjects']); // Esto puede hacerlo: root, admin
        Route::put('/update/student/grade', [InstructorController::class, 'updateStudentGrade']); // Esto puede hacerlo: instructor
        Route::get('/get/instructors-and-turns', [InstructorController::class, 'getInstructorsAndTurns']); // Esto puede hacerlo: root, admin
    });
});

Route::prefix('/careers')->group(function () {
    Route::post('/create', [CareerController::class, 'create']);
    Route::get('/get', [CareerController::class, 'getCareers']);
    Route::get('/get-with-subjects', [CareerController::class, 'getCareersWithSubjects']);
});

Route::prefix('/incomes')->group(function (){
    Route::middleware('auth:sanctum')->group(function () {
            Route::post('/tuition/create', [IncomesController::class, 'createTuitionIncome']);
            Route::post('/flight-credit/create', [IncomesController::class, 'createFlightCreditIncome']);
    });
});


Route::prefix('/subjects')->group(function () {
    Route::get('/get', [SubjectController::class, 'getSubjects']);
    Route::get('/get-info-calendar/{id_career}', [SubjectController::class, 'getSubjectsInfoCalendar']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/create', [SubjectController::class, 'create']); // Esto puede hacerlo: root, admin
        Route::delete('/destroy', [SubjectController::class, 'destroy']); // Esto puede hacerlo: root, admin
    });
});

Route::prefix('/employes')->middleware('auth:sanctum')->group(function () {
    Route::get('/get/tasks', [UserController::class, 'getEmployes']);
});

Route::prefix('/contacts')->group(function () {
    Route::post('/create', [ContactController::class, 'create']);
    Route::get('/get', [ContactController::class, 'index2']);
    Route::get('/show/{id}', [ContactController::class, 'show'])->where('id', '[0-9]+');
    Route::delete('/destroy/{id}', [ContactController::class, 'destroy'])->where('id', '[0-9]+');
    Route::put('/update/{id}', [ContactController::class, 'update'])->where('id', '[0-9]+');
    Route::get('/index', [ContactController::class, 'index2']);
});

Route::prefix('/turns')->group(function () {
    Route::get('/get', [TurnController::class, 'index']);
});


Route::prefix('/flights')->middleware('auth:sanctum')->group(function () {
    Route::get('/get', [InfoFlightController::class, 'index']);
    Route::get('/get/flight/data/{id_student}', [FlightHistoryController::class, 'flightsData']);
    Route::post('/changeStatus', [FlightHistoryController::class, 'changeStatusFlight']);
    Route::get('/get/flight/report/{id_flight}', [FlightHistoryController::class, 'reportDataById']);
    Route::post('/already/date/reserved', [FlightHistoryController::class, 'isDateReserved']);
    Route::get('/credit/students/index/{name?}', [FlightHistoryController::class, 'flightCreditStudent']);
});

Route::prefix('/payments')->middleware('auth:sanctum')->group(function () {
    Route::post('/amount', [PaymentsController::class, 'addPayment']);
    Route::post('/change/status', [PaymentsController::class, 'changeFlightPaymentStatus']);
});

Route::prefix('/employees')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/index', [EmployeeController::class, 'index']);
    });
});

Route::prefix('/products')->middleware('auth:sanctum')->group(function () {
    Route::get('/index/{name?}', [ProductController::class, 'index']);
    Route::post('/store', [ProductController::class, 'store']);
    Route::put('/update/{id_product}', [ProductController::class, 'update']);
    Route::post('/filters', [ProductController::class, 'filters']);

});

Route::prefix('/enum/values')->group(function () {
    Route::get('/flight/equipo', [InfoFlightController::class, 'getEquipFlight']);
    Route::get('/flight/flight_type', [InfoFlightController::class, 'getFlightType']);
    Route::get('/flight/flight_category', [InfoFlightController::class, 'getFlightCategory']);
    Route::get('/flight/maneuver', [InfoFlightController::class, 'getFlightManeuver']);
});

Route::prefix('/reports')->group(function () {
    Route::post('/store', [FlightHistoryController::class, 'storeReport']);
    Route::get('/index/student/{id_flight}', [FlightHistoryController::class, 'indexReport']);
    Route::post('/update/total', [FlightPaymentController::class, 'updateTotalPrice']);
    Route::get('/index/students', [StudentController::class, 'indexStudentsReport']);
    Route::post('/index/students/filter', [FlightHistoryController::class, 'indexStudentsFilter']);
    Route::get('/all/info/{id_flight}', [FlightHistoryController::class, 'getAllInfoReport']);
});


Route::prefix('/prices')->group(function () {
    Route::post('/flight', [FlightPaymentController::class, 'getFlightPrice']);
});

Route::prefix('/calendars')->group(function () {
    Route::get('/flight/reservate', [FlightHistoryController::class, 'getFlightReservations']);
    Route::get('/flight/types/{flight_type}', [FlightHistoryController::class, 'getFLightTypes']);
    Route::get('/flight/reservate/{id_student}', [FlightHistoryController::class, 'getFLightReservationsById']);
    Route::get('/flight/details/{id_flight}', [FlightHistoryController::class, 'getFlightDetails']);
});


Route::prefix('/tikets')->group(function () {
    Route::get('/flight/reservation/{flightHistoryId}', [PDFController::class, 'getReservationTicket']);
});


Route::prefix('/lessons')->group(function () {
    Route::get('/index', [LessonController::class, 'index']);
    Route::get('/index/{id_flight}', [LessonController::class, 'indexByFlight']);
    Route::post('/update/{id_flight}', [LessonController::class, 'update']);
});

Route::prefix('/infoflights')->group(function () {
    Route::get('/sessions/index', [SessionController::class, 'index']);
    Route::get('/airplanes/index', [AirPlaneController::class, 'index']);
});


Route::prefix('/customers')->middleware('auth:sanctum')->group(function () {
    Route::post('/flight/reservation', [FlightCustomerController::class, 'storeReservationFlight']);
});

Route::prefix('/airplanes')->middleware('auth:sanctum')->group(function () {
    Route::get('/flight/check/limit/hours', [FlightHistoryController::class, 'checkLimitHoursPlane']);
});


