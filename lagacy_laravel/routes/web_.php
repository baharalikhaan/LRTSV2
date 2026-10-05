<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\grading;
use App\Http\Controllers\test;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Details;
use App\Http\Controllers\DetailsController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OutcomesController;
use App\Mail\welcomeEmail;
use GuzzleHttp\Psr7\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/',[HomeController::class,'welcome'])->name('/');
Auth::routes();



Route::get('reviewer',[ProjectController::class,'reviewer'])->name('reviewer');
Route::view('trying','trying');

Route::get('upload/{p_id}',[ProjectController::class,'upload'])->name('upload');
Route::get('uHass',[OutcomesController::class,'uHass'])->name('uHass');
Route::post('outcome',[ProjectController::class,'outcome'])->name('outcome');
Route::get('user',[UserController::class,'display'])->name('user');
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('project',[ProjectController::class,'display'])->name('project');
Route::get('assignReview',[ProjectController::class,'assignView'])->name('assignReview');
// Route::get('getReviewers','ProjectController@get_reviewers')->name('reviewersList')->middleware('admin');
Route::post('CreatePro',[ProjectController::class,'create'])->name('Create');
Route::get('grading/{p_id}',[ProjectController::class,'grading'])->name('grading');
Route::get('proposal/{p_id}',[ProjectController::class,'proposal'])->name('proposal');
Route::get('serveFile',[ProjectController::class,'serveFile'])->name('serve');
Route::post('finalGrades',[grading::class,'finalGrades'])->name('finalGrades');
Route::post('progressGrade',[grading::class,'progressGrade'])->name('progressGrade');
Route::post('reportUpload/{p_id}',[ProjectController::class,'reportUpload'])->name('reportUpload');
Route::get('gradedProjects',[ProjectController::class,'graded'])->name('gradedProjects');
Route::post('updateStatus',[grading::class,'statusUpdate'])->name('statusUpdate');
Route::get('getProject/{p_id}',[grading::class,'getProject'])->name('getProject');
Route::get('projectDetails/{p_id}',[DetailsController::class,'projectDetails'])->name('projectDetails');
Route::get('gradeDetails/{p_id}',[DetailsController::class,'gradeDetails'])->name('gradeDetails');

Route::post('updateProjectTag/{p_id}',[DetailsController::class,'updateProjectTag'])->name('updateProjectTag');
Route::post('updateProjectPillar/{p_id}',[DetailsController::class,'updateProjectPillar'])->name('updateProjectPillar');
Route::post('assignReviewer/{p_id}',[DetailsController::class,'assignReviewer'])->name('assignReviewer');

Route::get('gradingDetails/{p_id}',[DetailsController::class,'gradingDetails'])->name('gradingDetails');
Route::post('create',[UserController::class,'create'])->name('createUser');
Route::get('new',[UserController::class,'new'])->name('newUser');
Route::get('edit/{id}',[UserController::class,'edit'])->name('edit');
Route::post('update/{id}',[UserController::class,'update'])->name('update');
Route::post('print',[ProjectController::class,'print'])->name('print');

Route::get('dashboard',[DashboardController::class,'load'])->name('dashboard');

Route::view('projectDetails','projectDetails');
Route::get('commitments',[ProjectController::class,'commitments'])->name('commitments');

Route::get('test',[ProjectController::class,'test'])->name('test');

Route::get('CompleteInfo',[EmailController::class,'CompleteInfo'])->name('CompleteInfo');
Route::get('projectOutcomes',[OutcomesController::class,'projectOutcomes'])->name('projectOutcomes');

Route::get('welcomeEmail',function(){
    return new welcomeEmail();
});

Route::get('testingEmail',[EmailController::class,'testingEmail'])->name('testingEmail');

Route::get('userDetail/{u_id}',[DetailsController::class,'userDetail'])->name('userDetail');

Route::view('projectStep1','projectStep1')->name('sessionStep1');
Route::view('projectStep2','projectStep2')->name('sessionStep2');
Route::view('projectStep3','projectStep3')->name('sessionStep3');

Route::get('settings',[DetailsController::class,'settings'])->name('settings');
Route::get('newProject',[ProjectController::class,'newproject'])->name('newProject');
Route::post('newProject',[ProjectController::class,'createProject'])->name('mintProject');
Route::post('createProjectStep2',[ProjectController::class,'createProjectStep2'])->name('createProjectStep2');
Route::post('createProjectStep3',[ProjectController::class,'createProjectStep3'])->name('createProjectStep3');

Route::get('pdf/{p_id}', [grading::class, 'index'])->name('pdf');
Route::view('gradingTabs','gradingTabs');

Route::get('doi',[test::class,'doi'])->name('doi');

Route::get('sort_name',[UserController::class,'sortByname'])->name('sort_name');
Route::get('sort_email',[UserController::class,'sortByemail'])->name('sort_email');
Route::get('sort_title',[ProjectController::class,'sortBytitle'])->name('sort_title');
Route::get('search_project',[ProjectController::class,'searchByProject'])->name('search_project');
Route::get('search_user',[UserController::class,'searchByUser'])->name('search_user');

Route::post('hassOutcomes',[OutcomesController::class,'hassOutcomes'])->name('hassOutcomes');
Route::post('projectOutcome',[OutcomesController::class,'projectOutcome'])->name('projectOutcome');
Route::post('projectOutcome2',[OutcomesController::class,'projectOutcome2'])->name('projectOutcome2');
Route::post('projectOutcome3',[OutcomesController::class,'projectOutcome3'])->name('projectOutcome3');

Route::post('verifyOutcomes',[grading::class,'verifyOutcomes'])->name('verifyOutcomes');

Route::get('emails',[EmailController::class,'emails'])->name('emails');
Route::get('announcement',[EmailController::class,'announcement'])->name('announcement');
Route::get('project_added',[EmailController::class,'project_added'])->name('project_added');

Route::view('TrialDashboard','TrialDashboard');
Route::post('guage',[HomeController::class,'guage'])->name('guage');

Route::get('emailSetting',[EmailController::class,'emailSetting'])->name('emailSetting');
Route::get('emailEdit/{id}',[EmailController::class, 'emailEdit'])->name('emailEdit');
Route::post('emailUpdate/{id}',[EmailController::class, 'emailUpdate'])->name('emailUpdate');
Route::get('announcementSetting',[HomeController::class,'announcementSetting'])->name('announcementSetting');
Route::post('announcementUpdate/{id}',[HomeController::class, 'announcementUpdate'])->name('announcementUpdate');
Route::post('announcementDetail/{id}',[HomeController::class, 'announcementDetail'])->name('announcementDetail');
Route::get('announcementEdit/{id}',[HomeController::class,'announcementEdit'])->name('announcementEdit');

Route::get('aboutUsSettings',[HomeController::class,'aboutUsSettings'])->name('aboutUsSettings');

Route::post('bulk',[ProjectController::class,'bulk'])->name('bulk');

Route::get('DBquery',[grading::class,'DBquery'])->name('DBquery');

Route::get('select',[grading::class,'select'])->name('select');

Route::get('API_DOI',[grading::class,'API_DOI'])->name('API_DOI');
Route::get('API',[OutcomesController::class,'API'])->name('API');

Route::get('elsevierAPI',[grading::class,'elsevierAPI'])->name('elsevierAPI');

Route::get('Reviewer',[ProjectController::class,'countReviewer'])->name('countReviewer');
Route::get('cycle',[HomeController::class,'cycle'])->name('cycle');
Route::get('aboutUsEdit/{id}',[HomeController::class,'aboutUsEdit'])->name('aboutUsEdit');
Route::post('aboutUsUpdate/{id}',[HomeController::class, 'aboutUsUpdate'])->name('aboutUsUpdate');

Route::get('cycleEdit/{id}',[HomeController::class,'cycleEdit'])->name('cycleEdit');
Route::post('cycleUpdate/{id}',[HomeController::class, 'cycleUpdate'])->name('cycleUpdate');

Route::post('createCycle',[HomeController::class,'createCycle'])->name('createCycle');
Route::get('newCycle',[HomeController::class,'newCycle'])->name('newCycle');
Route::get('newAnnouncement',[HomeController::class,'newAnnouncement'])->name('newAnnouncement');
Route::get('AssignedReviewers',[ProjectController::class,'AssignedReviewers'])->name('AssignedReviewers');

Route::post('publish',[grading::class, 'publish'])->name('publish');

Route::get('serveImg',[HomeController::class,'serveFile'])->name('serveImg');

Route::get('guageSetting',[HomeController::class,'guageSetting'])->name('guageSetting');
Route::get('uploadedOutcomes',[DetailsController::class,'uploadedOutcomes'])->name('uploadedOutcomes');
Route::view('outcomeInfo','outcomeInfo');
Route::post('printed',[ProjectController::class,'printed'])->name('printed');

Route::get('testing2',[OutcomesController::class,'testing2'])->name('testing2');


//for excel
Route::get('/excel', [test::class, 'excelForm'])->name('excel.form');
Route::post('/excel', [test::class, 'excelImport'])->name('excel');
