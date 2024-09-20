<?php


use App\Http\Controllers\AcademicFileController;
use App\Http\Controllers\AirPlaneController;
use App\Http\Controllers\AnalyticController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\IncomesController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\SubjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\CheckInRecordsController;
use App\Http\Controllers\ConsumableController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\PendingController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FlightCustomerController;
use App\Http\Controllers\FlightHistoryController;
use App\Http\Controllers\FlightPaymentController;
use App\Http\Controllers\InfoFlightController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\NewSletterController;
use App\Http\Controllers\OptionController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SessionController;
use App\Http\Middleware\userType;

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


Route::prefix('/avia')->group(function () { //root, admin, employee, instructor, student, flight_instructor

    Route::post('login', [UserController::class, 'login']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/user/data', [UserController::class, 'getUserData']);
        Route::post('logout', [UserController::class, 'logout'])->middleware('role:root,admin,employee,instructor,student,flight_instructor');
        Route::get('bases-careers-turns', [BaseController::class, 'getBasesWithCareersAndTurns'])->middleware('role:root,admin,employee');
    });
});

Route::prefix('/analitics')->middleware('auth:sanctum')->group(function () {
    Route::get('/get/principal', [AnalyticController::class, 'getCardData']);
    Route::get('/get/enrollments/year', [AnalyticController::class, 'getEnrollmentsYear']);
    Route::get('/get/activity/week', [AnalyticController::class, 'getWeekActivity']);
    Route::get('/get/debt', [AnalyticController::class, 'getTotalDebt']);
});

Route::prefix('/pendings')->middleware('auth:sanctum')->middleware('auth:sanctum')->group(function () {
    Route::get('/get/all', [PendingController::class, 'index']);
    Route::delete('/destroy/{id}', [PendingController::class, 'destroy']);
    Route::post('/create', [PendingController::class, 'create']);
    Route::post('/update', [PendingController::class, 'update']);
});

Route::prefix('/students')->middleware('auth:sanctum')->group(function () {
    Route::get('/index', [StudentController::class, 'index']);
    Route::get('/index/{identificator}', [StudentController::class, 'indexByName']);
    Route::get('/indexId/{id_student}', [StudentController::class, 'indexId']);


    Route::middleware('role:root,admin,employee')->group(function (){ /**/
        Route::post('/create', [StudentController::class, 'create']);
        Route::get('/show/{id}', [StudentController::class, 'show'])->where('id', '[0-9]+');
        Route::put('/update/grade', [StudentController::class, 'updateGrade']);
        Route::get('/get', [StudentController::class, 'getStudents']);
        Route::get('/get/subjects/{id}', [StudentController::class, 'getStudentSubjects'])->where('id', '[0-9]+');
        Route::post('/add/subject', [StudentController::class, 'addSubjectToStudent']);
        Route::delete('/delete/subject', [StudentController::class, 'deleteSubjectFromStudent']);
        Route::put('/change/instructor', [StudentController::class, 'changeInstructorFromStudentSubject']);
        Route::put('/update', [StudentController::class, 'update']);
        Route::get('/student/owed-monthly-payments/{id}', [StudentController::class, 'getStudentAndOwedMonthlyPayments'])->where('id', '[0-9]+');
        Route::get('/get/name-identification/{id}', [StudentController::class, 'getStudentNameAndIdentification'])->where('id', '[0-9]+');
    });

    Route::middleware('role:student')->group(function (){ /**/
        Route::get('/student/get/profile-info', [StudentController::class, 'studentInfo']);
        Route::get('/student/get/subjects', [StudentController::class, 'getStudentSubjectsAsStudent']);
        Route::get('/student/incomes', [StudentController::class, 'getStudentIncomes']);
        Route::put('/update/password', [StudentController::class, 'updateStudentPassword']);
    });

    Route::get('/student/pending-payments/{id?}', [StudentController::class, 'studentPendingPayments'])->where('id', '[0-9]+')
        ->middleware('role:student,root,admin,employee');

    Route::middleware('role:admin,root')->group(function (){ /**/
        Route::delete('/delete/access-user/{id}', [StudentController::class, 'deleteAccessUser'])->where('id', '[0-9]+');
        Route::post('/create/access-user/{id}', [StudentController::class, 'createAccessUser'])->where('id', '[0-9]+');
        Route::delete('/delete/{id}', [StudentController::class, 'deleteStudent'])->where('id', '[0-9]+');
    });


    Route::get('/flight/index', [StudentController::class, 'indexSimulator']);
    Route::get('/flight/index/{name}', [StudentController::class, 'getStudentSimulatorByName']);
    Route::get('/flight/report/{id_student?}', [StudentController::class, 'getInfoVueloAlumno']);
    Route::get('/flight/employees/bystudent/{id}', [StudentController::class, 'getEmployeesByStudent']);
    Route::post('/flight/store', [StudentController::class, 'storeFlight']);
});

Route::prefix('/bases')->middleware(['auth:sanctum', 'role:root,admin,employee,instructor,flight_instructor,student'])->group(function () {
    Route::get('/get', [BaseController::class, 'getBases']);
});

Route::prefix('/instructors')->middleware('auth:sanctum')->group(function () {

    Route::get('/index', [InstructorController::class, 'index']);

    Route::middleware('role:admin,root')->group(function () { /**/
        Route::post('/create', [InstructorController::class, 'create']);
        Route::put('/update/instructors-subjects', [InstructorController::class, 'updateInstructorsSubjects']); // Esto puede hacerlo: root, admin
        Route::get('/get/instructors-subjects', [InstructorController::class, 'getInstructorsSubjects']);
        Route::get('/get/instructors-and-turns', [InstructorController::class, 'getInstructorsAndTurns']);
    });

    Route::middleware('role:instructor')->group(function () {
        Route::get('/get/instructor-students/{id}', [InstructorController::class, 'getInstructorStudents'])->where('id', '[0-9]+');
        Route::get('/get/instructor-active-subjects', [InstructorController::class, 'getInstructorActiveSubjects'])->where('id', '[0-9]+');
        Route::put('/update/student-grade', [InstructorController::class, 'updateStudentSubjectGrade']);
    });
});

Route::prefix('/careers')->middleware('auth:sanctum')->group(function () {
    Route::middleware('role:admin,root')->group(function (){
        Route::get('/get', [CareerController::class, 'getCareers']);
        Route::get('/index', [CareerController::class, 'index']);
        Route::post('/create', [CareerController::class, 'create']);
        Route::put('/update', [CareerController::class, 'update']);
    });
});

Route::prefix('/incomes')->middleware('auth:sanctum')->group(function (){
    Route::middleware('role:root,admin,employee')->group(function (){
        Route::post('/create/income', [IncomesController::class, 'createIncomes']);
        Route::get('/get/all', [IncomesController::class, 'index']);
        Route::get('/show/{id}', [IncomesController::class, 'show'])->where('id', '[0-9]+');
    });
});


Route::prefix('/subjects')->middleware('auth:sanctum')->group(function () {
    Route::get('/get-info-calendar/{id_career}', [SubjectController::class, 'getSubjectsInfoCalendar']);

    Route::middleware('role:admin,root')->group(function () {
        Route::post('/create', [SubjectController::class, 'create']);
        Route::delete('/destroy', [SubjectController::class, 'destroy']);
    });
});

Route::prefix('/employes')->middleware('auth:sanctum')->group(function () {
    Route::get('/get/tasks', [UserController::class, 'getEmployes']);
});

Route::prefix('/flights')->middleware('auth:sanctum')->group(function () {
    Route::get('/get', [InfoFlightController::class, 'index']);
    Route::get('/get/flight/data/{id_student}/{flightHistory?}', [FlightHistoryController::class, 'flightsData']);
    Route::post('/changeStatus', [FlightHistoryController::class, 'changeStatusFlight']);
    Route::get('/get/flight/report/{id_flight}', [FlightHistoryController::class, 'reportDataById']);
    Route::post('/already/date/reserved', [FlightHistoryController::class, 'isDateReserved']);
    Route::post('/store/request/student/flight', [FlightHistoryController::class, 'requestFlightReservation']);
    Route::post('/change/status/request', [FlightHistoryController::class, 'changeStatusRequest']);
    Route::get('/credit/students/index', [FlightHistoryController::class, 'flightCreditStudent']);
});

Route::prefix('/payments')->middleware('auth:sanctum')->group(function () {
    Route::post('/amount', [PaymentsController::class, 'addPayment']);
    Route::post('/change/status', [PaymentsController::class, 'changeFlightPaymentStatus']);
});

Route::prefix('/employees')->middleware('auth:sanctum')->group(function () {

    Route::middleware('role:root,admin,employee')->group(function (){
        Route::get('/index', [EmployeeController::class, 'index']);
        Route::get('/show/{id}', [EmployeeController::class, 'show'])->where('id', '[0-9]+');
    });

    Route::middleware('role:root,admin')->group(function (){
        Route::put('/update/{id}', [EmployeeController::class, 'update'])->where('id', '[0-9]+');
        Route::put('/update/password/{id}', [EmployeeController::class, 'updatePassword'])->where('id', '[0-9]+');
        Route::delete('/delete/access-user/{id}', [EmployeeController::class, 'deleteAccessUser'])->where('id', '[0-9]+');
        Route::post('/create/access-user/{id}', [EmployeeController::class, 'createAccessUser'])->where('id', '[0-9]+');
    });
});

Route::prefix('/products')->middleware(['auth:sanctum', 'role:root, admin,employee'])->group(function () {
    Route::get('/index/{name?}', [ProductController::class, 'index']);
    Route::post('/store', [ProductController::class, 'store']);
    Route::put('/update/{id_product}', [ProductController::class, 'update']);
    Route::post('/filters', [ProductController::class, 'filters']);
});

Route::prefix('/enum/values')->middleware('auth:sanctum')->group(function () {
    Route::get('/flight/equipo', [InfoFlightController::class, 'getEquipFlight']);
    Route::get('/flight/flight_type', [InfoFlightController::class, 'getFlightType']);
    Route::get('/flight/flight_category', [InfoFlightController::class, 'getFlightCategory']);
    Route::get('/flight/maneuver', [InfoFlightController::class, 'getFlightManeuver']);
});

Route::prefix('/reports')->group(function () {
    Route::post('/store', [FlightHistoryController::class, 'storeReport']);
    Route::get('/index/student/{id_flight}', [FlightHistoryController::class, 'indexReport']);
    Route::post('/update/total', [FlightPaymentController::class, 'updateTotalPrice']);
    Route::get('/all/info/{id_flight}', [FlightHistoryController::class, 'getAllInfoReport']);
    Route::get('/index/schedule', [FlightHistoryController::class, 'getSchedule']);
    Route::post('/index/students/filter', [FlightHistoryController::class, 'indexStudentsFilter']);
    Route::get('/index/students', [StudentController::class, 'indexStudentsReport']);
});


Route::prefix('/prices')->middleware('auth:sanctum')->group(function () {
    Route::post('/flight', [FlightPaymentController::class, 'getFlightPrice']);
});

Route::prefix('/calendars')->middleware('auth:sanctum')->group(function () {
    Route::get('/flight/reservate', [FlightHistoryController::class, 'getFlightReservations']);
    Route::get('/flight/types/{flight_type}', [FlightHistoryController::class, 'getFLightTypes']);
    Route::get('/flight/reservate/{id_student}', [FlightHistoryController::class, 'getFLightReservationsById']);
    Route::get('/flight/details/{id_flight}', [FlightHistoryController::class, 'getFlightDetails']);
});


Route::prefix('/tikets')->middleware(['auth:sanctum', 'role:root,admin,employee'])->group(function () {
    Route::get('/flight/reservation/{flightHistoryId}', [PDFController::class, 'generateTicket']);
    Route::get('/{flightHistoryId}', [PDFController::class, 'getReservationTicket']);
});


Route::prefix('/lessons')->middleware('auth:sanctum')->group(function () {
    Route::get('/index', [LessonController::class, 'index']);
    Route::get('/index/{id_flight}', [LessonController::class, 'indexByFlight']);
    Route::put('/update', [LessonController::class, 'update'])->middleware('role:root,admin,employee,flight_instructor');
});

Route::prefix('/infoflights')->middleware('auth:sanctum')->group(function () {
    Route::get('/sessions/index/{id_student}/{id_flight?}', [SessionController::class, 'syllabus']);
    Route::get('/index/syllabus/edit/{id_flight}/{id_student}', [SessionController::class, 'indexEditSyllabus']);
    Route::get('/syllabus/lessons/{id_student}', [SessionController::class, 'showLessons']);
    Route::get('/airplanes/index', [AirPlaneController::class, 'index']);
    Route::get('/index/syllabus/{name?}', [StudentController::class, 'indexSyllabus']);
    Route::get('/syllabus', [StudentController::class, 'indexSyllabus']);
    Route::get('/students/history/flight/{name?}', [InfoFlightController::class, 'studentsFlightHistory']);
    Route::get('/history/flight/{id_student}', [InfoFlightController::class, 'flightHistory']);
    Route::get('/get/flight/syllabus/data/{id_flight}', [InfoFlightController::class, 'getFlightSyllabusData']);
    Route::get('/airplane/flight/index', [InfoFlightController::class, 'AirplaneFlightIndex']);
    Route::get('/request/flight', [InfoFlightController::class, 'flightRequestIndex']);
});


Route::prefix('/customers')->middleware(['auth:sanctum', 'role:root,admin,employee'])->group(function () {
    Route::post('/flight/reservation', [FlightCustomerController::class, 'storeReservationFlight']);
    Route::get('/flight/index', [FlightCustomerController::class, 'index']);
    Route::post('/flight/edit/{reservation_id}/{flight_status?}', [FlightCustomerController::class, 'edit']);
});

Route::prefix('/airplanes')->middleware('auth:sanctum')->group(function () {
    Route::get('/flight/check/limit/hours', [FlightHistoryController::class, 'checkLimitHoursPlane']);
    Route::get('/flight/reset/hours', [AirPlaneController::class, 'resetHours']);
});

Route::prefix('/consumables')->middleware(['auth:sanctum', 'role:root,admin,flight_instructor,employee'])->group(function () {
    Route::get('/index', [ConsumableController::class, 'index']);
    Route::post('/store', [ConsumableController::class, 'store']);
    Route::get('/show', [ConsumableController::class, 'show']);
    Route::put('/update', [ConsumableController::class, 'update']);
});

Route::prefix('/newsletters')->middleware('auth:sanctum')->group(function () {
    Route::get('/index', [NewSletterController::class, 'index']);
});

Route::prefix('/newsletters')->middleware(['auth:sanctum', 'role:root, admin, instructor, flight_instructor, employee'])->group(function () {
    Route::post('/store', [NewSletterController::class, 'create']);
    Route::post('/edit', [NewSletterController::class, 'edit']);
});

Route::prefix('/payment_methods')->middleware('auth:sanctum')->group(function () {
    Route::get('/index', [PaymentMethodController::class, 'index']);
});

Route::prefix('/discounts')->middleware('auth:sanctum')->group(function () {
    Route::get('/index', [DiscountController::class, 'index']);
});

Route::prefix('/shops')->middleware(['auth:sanctum', 'role:root,admin,employee'])->group(function () {
    Route::post('/store', [OrderController::class, 'store']);
    Route::get('/index/{id_order?}', [OrderController::class, 'index']);
    Route::get('/index/student/{id_student?}', [OrderController::class, 'indexStudent']);
    Route::post('/edit', [OrderController::class, 'edit']);
    Route::post('/store/installment', [OrderController::class, 'storeInstallment']);
    Route::get('/test/{id_order}', [PDFController::class, 'getProductOrderTicket']);
});


Route::prefix('/arrival')->middleware(['auth:sanctum', 'role:root'])->group(function () {
    Route::get('/index', [CheckInRecordsController::class, 'index']);
});

Route::prefix('/options')->middleware(['auth:sanctum', 'role:root,admin,employee'])->group(function () {
    Route::get('/change/flight/request', [OptionController::class, 'changeFlightRequest']);
});


Route::prefix('/files')->middleware(['auth:sanctum', 'role:root,admin,employee'])->group(function () {
    Route::get('/student/index/{id_student}', [AcademicFileController::class, 'index']);
    Route::post('/store', [AcademicFileController::class, 'store']);
});

Route::prefix('/fingerPrint')->group(function () {
    Route::get('/check/list/{id_finger}', [EmployeeController::class, 'fingerPrintList']);
});

Route::get('/test', [StudentController::class, 'index']);
