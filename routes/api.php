<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ExerciceController;
use App\Http\Controllers\VideoController;

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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth');
 
Route::post('/create', [AuthController::class, 'create']);
Route::post('/login', [AuthController::class, 'login']);

//Consulta de autenticação
Route::get('/token-query', [AuthController::class, 'verifyAuthentication']);


//Courses
Route::middleware('auth')->group(function() {
    //Courses
    Route::get('/course/{slug_course}', [CourseController::class, 'getCourseUsingSlug']); 
    Route::get('/my-courses', [CourseController::class, 'getMyCourses']);
    Route::get('/course-{slug_course}-info', [CourseController::class, 'getInfoCourse']);
    Route::get('/course-{slug_course}-warnings', [CourseController::class, 'getCourseWarnings']);

    //New Courses
    Route::post('/new-course', [CourseController::class, 'setNewCourse']);

    //Warnings
    Route::post('/new-course-{id_course}-warning', [CourseController::class, 'setNewCourseWarning']);
    Route::delete('/delete-warning-{id_warning}', [CourseController::class, 'setDelCourseWarning']);

    //Users
    Route::post('/enter-course-{id_course}', [CourseController::class, 'setUserInCourseId']);
});

//Videos
Route::middleware('auth')->group(function() {
    //Video query
    Route::get('/video/{id_video}', [VideoController::class, 'getVideoUsingId']);
    Route::get('/video-{id_video}-watched', [VideoController::class, 'getVideoWatched']);
    Route::get('/video-{id_video}-note', [VideoController::class, 'getVideoNote']);
    Route::get('/video-{id_video}-comments', [VideoController::class, 'getVideoComments']);
    Route::get('/comment-{id_comment}', [VideoController::class, 'getVideoCommentUsingId']);
    Route::get('/video-{id_video}-exercises', [VideoController::class, 'getVideoExercises']);
    Route::get('/check-exercise-{id_exercise}', [VideoController::class, 'getCheckExerciseCorrect']);

    //Modules
    Route::post('/new-course-{id_course}-module', [VideoController::class, 'setNewCourseModule']);
    Route::put('/edit-{id_module}-module', [VideoController::class, 'setEditCourseModule']);

    //Videos
    Route::post('/new-module-{id_module}-video', [VideoController::class, 'setNewModuleVideo']);
    Route::put('/edit-{id_video}-video', [VideoController::class, 'setEditModuleVideo']);
    Route::delete('/delete-{id_video}-video', [VideoController::class, 'setDelModuleVideo']);
    Route::post('/new-video-{id_video}-comment', [VideoController::class, 'setNewVideoComment']);
    Route::post('/new-video-{id_video}-note', [VideoController::class, 'setNewVideoNote']);
    Route::post('/new-video-{id_video}-watched', [VideoController::class, 'setNewVideoWatched']);

    //Videos exercices
    Route::post('/new-video-{id_video}-exercice', [ExerciceController::class, 'setNewExerciceInVideo']);
});