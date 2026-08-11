<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\grading;
use App\Http\Controllers\test;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Details;
use App\Http\Controllers\FTPController;
use App\Http\Controllers\DetailsController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OutcomesController;
use App\Http\Controllers\APIController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\AdminProgressDetails;
use App\Http\Controllers\BlockChainController;
use App\Http\Controllers\ProgressReportController;


use App\Mail\welcomeEmail;
use GuzzleHttp\Psr7\Request;

use Aacotroneo\Saml2\Saml2Auth;
use App\Http\Controllers\CollegeDashboardController;


use App\Http\Controllers\FileExplorerController;
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


Route::get('nationality', [UserController::class, 'nationality'])->name('nationality');
Route::post('updateNationality', [UserController::class, 'updateNationality'])->name('updateNationality');
Route::get('gradingDetailsPublic/{p_id}', [DetailsController::class, 'gradingDetailsPublic'])->name('gradingDetailsPublic'); //Admin
Route::get('reviewerEvaluationPublic/{u_id}', [DetailsController::class, 'reviewerEvaluationPublic'])->name('reviewerEvaluationPublic'); //Admin


//the middleware for the nationality check at start
Route::middleware('check.user.attribute')->group(function () {
    // Define your routes here


    //User Selection
    Route::get('switchRole/{role}', [HomeController::class, 'switchRole'])->name('switchRole'); //Admin

    Route::get('/', [HomeController::class, 'index'])->name('/'); //Admin
    //Welcome route without authentication
    // Route::get('/', function () {
    //     return view('welcome');
    // })->name('welcome');

    //for excel
    Route::get('/excelForm', [test::class, 'excelForm'])->name('excelForm');
    Route::post('/excelImport', [test::class, 'excelImport'])->name('excelImport');

    //for pdf bulk upload
    Route::get('/pdfForm', [test::class, 'pdfForm'])->name('pdfForm');
    Route::post('/uploadProposals', [test::class, 'uploadProposals'])->name('uploadProposals');


    Route::get('aboutUsSettings', [HomeController::class, 'aboutUsSettings'])->name('aboutUsSettings'); //Admin
    Route::get('home.ajaxListAboutus', [HomeController::class, 'ajaxListAboutus'])->name('home.ajaxListAboutus'); //Admin
    Route::get('aboutUsEdit/{id}', [HomeController::class, 'aboutUsEdit'])->name('aboutUsEdit'); //Admin
    Route::post('aboutUsUpdate/{id}', [HomeController::class, 'aboutUsUpdate'])->name('aboutUsUpdate'); //Admin


    Route::get('emails', [EmailController::class, 'emails'])->name('emails'); //Admin


    Route::get('emails', [EmailController::class, 'emails'])->name('emails'); //Admin
    Route::get('announcement', [EmailController::class, 'announcement'])->name('announcement');
    Route::get('project_added', [EmailController::class, 'project_added'])->name('project_added');

    Route::view('projectStep1', 'projectStep1')->name('sessionStep1'); //LPI &LPI+Reviewer
    Route::view('projectStep2', 'projectStep2')->name('sessionStep2'); //LPI &LPI+Reviewer
    Route::view('projectStep3', 'projectStep3')->name('sessionStep3'); //LPI &LPI+Reviewer

    Auth::routes();
    Route::get('auth/login', 'Aacotroneo\Saml2\Http\Controllers\Saml2Controller@login')->name('auth/login'); //All users
    //   Route::get('/', [HomeController::class, 'welcome'])->name('/');


    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home'); //All

    // Authentication
    Route::middleware(['auth'])->group(function () {

        Route::get('logout', function () {
            Auth::logout();
        });



        //All users

        Route::get('CollegeDashboard', [CollegeDashboardController::class, 'showDashboard'])->name('CollegeDashboard'); //All
        Route::get('fetchdatacollege', [CollegeDashboardController::class, 'fetchDataCollege'])->name('fetchdatacollege'); //All
        Route::get('fetchdatapillar', [CollegeDashboardController::class, 'fetchDataPillar'])->name('fetchdatapillar'); //All

        //Home
        Route::get('/registerProjectReminder', [EmailController::class, 'registerProjectReminder'])->name('registerProjectReminder'); //All

        Route::get('home.ajaxList', [HomeController::class, 'ajaxList'])->name('home.ajaxList');
        Route::get('home.ajaxList2/{cycle}', [ProjectController::class, 'ajaxList22'])->name('home.ajaxList2');
        Route::get('home.ajaxList3', [HomeController::class, 'ajaxList3'])->name('home.ajaxList3');
        Route::get('/download/{type}/{cycle}/{projectId}',  [HomeController::class, 'serve'])->name('download.file');

        Route::get('/download-zip/{cycle}/{projectId}', [HomeController::class, 'downloadZip'])->name('download.zip');


        //graded projects
        Route::get('gradedcycles', [ProjectController::class, 'displaygradedcycle'])->name('gradedcycles');

        //Cycles
        Route::get('cycles', [ProjectController::class, 'displaycycle'])->name('cycles');
        Route::get('confcycles', [HomeController::class, 'displaycycle'])->name('confcycles');
        Route::get('ajaxListconfcycle', [HomeController::class, 'ajaxListconfcycle'])->name('ajaxListconfcycle');
        Route::get('conftoolProjects/{cycle}', [ProjectController::class, 'conftoolProjects'])->name('conftoolProjects');

        //Project
        Route::get('confprojectadd/{cycle}', [ProjectController::class, 'confprojectadd'])->name('confprojectadd'); //Admin
        Route::get('confprojectedit/{id}', [ProjectController::class, 'confprojectedit'])->name('confprojectedit'); //Admin
        Route::post('confprojectupdate', [ProjectController::class, 'confprojectupdate'])->name('confprojectupdate'); //Admin
        Route::post('confprojectsave', [ProjectController::class, 'confprojectsave'])->name('confprojectsave'); //Admin
        Route::get('newProject', [ProjectController::class, 'newproject'])->name('newProject'); //LPI &LPI+Reviewer & Admin
        Route::post('createProject', [ProjectController::class, 'createProject'])->name('mintProject'); //LPI &LPI+Reviewer & Admin
        Route::post('createProjectStep2', [ProjectController::class, 'createProjectStep2'])->name('createProjectStep2'); //LPI &LPI+Reviewer & Admin
        Route::post('createProjectStep3', [ProjectController::class, 'createProjectStep3'])->name('createProjectStep3'); //LPI &LPI+Reviewer & Admin

        //   Route::get('projects', [ProjectController::class, 'display'])->name('projects');

        Route::get('gradedproject/{c_id}', [ProjectController::class, 'gradeddisplay'])->name('gradedproject');


        Route::get('project/{c_id}', [ProjectController::class, 'display'])->name('project');
        Route::get('project.ajaxList/{c_id}', [ProjectController::class, 'ajaxList'])->name('project.ajaxList');
        Route::get('project.ajaxList2/{c_id}', [ProjectController::class, 'ajaxList2'])->name('project.ajaxList2');

        Route::get('project.ajaxListReviewerGraded/{c_id}', [ProjectController::class, 'ajaxListReviewerGraded'])->name('project.ajaxListReviewerGraded'); //for reviewer graded projects

        Route::get('project.ajaxListcycle', [ProjectController::class, 'ajaxListcycle'])->name('project.ajaxListcycle');
        Route::get('project.ajaxListcycle2', [ProjectController::class, 'ajaxListcycle2'])->name('project.ajaxListcycle2');

        Route::post('updateCommitments', [ProjectController::class, 'updateCommitments'])->name('updateCommitments'); //Admin
        Route::get('StudentAPI', [APIController::class, 'StudentAPI'])->name('StudentAPI'); //Admin
        Route::get('serveFile3', [ProjectController::class, 'serveFile3'])->name('serveFile3'); //Reviewer


        //grading
        Route::get('gradeDetails/{p_id}', [DetailsController::class, 'gradeDetails'])->name('gradeDetails'); //Admin
        Route::get('gradingDetails/{p_id}', [DetailsController::class, 'gradingDetails'])->name('gradingDetails'); //Admin
        Route::get('gradingDetailsLagacy/{p_id}', [DetailsController::class, 'gradingDetailsLagacy'])->name('gradingDetailsLagacy'); //Admin

        Route::get('project.ajaxListLPIGraded/{cycle}', [ProjectController::class, 'ajaxListLPIGraded'])->name('project.ajaxListLPIGraded'); //Admin
        Route::get('serveFile', [ProjectController::class, 'serveFile'])->name('serve'); //Reviewer
        Route::get('serveFile2', [ProjectController::class, 'serveFile2'])->name('serveFile2'); //Reviewer



        /*************************************************************** */

        Route::post('progressGrade', [grading::class, 'progressGrade'])->name('progressGrade');

        //Admin + LPI>
        // Route::middleware(['can:ADMIN_LPI'])->group(function () {

        Route::get('ajaxListGradedprojects', [ProjectController::class, 'ajaxListGradedprojects'])->name('ajaxListGradedprojects'); //Admin
        Route::get('gradedProjects', [ProjectController::class, 'graded'])->name('gradedProjects'); //Admin

        Route::post('CreatePro', [ProjectController::class, 'create'])->name('Create'); //LPI && Admin sometimes
        Route::get('userDetail/{u_id}', [DetailsController::class, 'userDetail'])->name('userDetail'); //specific user +Admin
        Route::get('projectDetails/{p_id}', [DetailsController::class, 'projectDetails'])->name('projectDetails'); //Admin
        Route::get('ajaxList/{id}', [DetailsController::class, 'ajaxList'])->name('ajaxList'); //specific user +Admin
        Route::get('upload/{p_id}', [ProjectController::class, 'upload'])->name('upload'); //LPI

        // });

        //Admin + Reviewer
        // Route::middleware(['can:ADMIN_REVIEWER'])->group(function () {

        Route::post('finalGrades', [grading::class, 'finalGrades'])->name('finalGrades');
        Route::view('gradingTabs', 'gradingTabs');
        Route::get('grading/{p_id}', [ProjectController::class, 'grading'])->name('grading'); //Reviewer and Admins
        Route::get('reviewerEvaluation/{u_id}', [DetailsController::class, 'reviewerEvaluation'])->name('reviewerEvaluation'); //Admin
        // });

        //LPI + Reviewer
        // Route::middleware(['can:LPI_LPIREVIEWER'])->group(function () {

        Route::post('reportUpload/{p_id}', [ProjectController::class, 'reportUpload'])->name('reportUpload'); // LPI && LPI+Reviewer
        Route::get('uploadedOutcomes', [DetailsController::class, 'uploadedOutcomes'])->name('uploadedOutcomes'); //LPI & LPI+Reviewer
        Route::post('uploadedOutcomesDelete/{p_id}', [DetailsController::class, 'uploadedOutcomesDelete'])->name('uploadedOutcomesDelete'); //LPI & LPI+Reviewer
        Route::post('uploadedOutcomesDeleteStudent/{id}', [DetailsController::class, 'uploadedOutcomesDeleteStudent'])->name('uploadedOutcomesDeleteStudent'); //LPI & LPI+Reviewer
        Route::post('uploadedOutcomesDeleteContribution/{id}', [DetailsController::class, 'uploadedOutcomesDeleteContribution'])->name('uploadedOutcomesDeleteContribution'); //LPI & LPI+Reviewer
        Route::get('projectOutcomes', [OutcomesController::class, 'projectOutcomes'])->name('projectOutcomes');
        Route::post('projectOutcome', [OutcomesController::class, 'projectOutcome'])->name('projectOutcome');
        Route::post('projectOutcome2', [OutcomesController::class, 'projectOutcome2'])->name('projectOutcome2');
        Route::post('projectOutcome3', [OutcomesController::class, 'projectOutcome3'])->name('projectOutcome3');
        Route::post('projectOutcomesstudent', [OutcomesController::class, 'projectOutcomesstudent'])->name('projectOutcomesstudent');
        Route::post('progressReport/save/{project_id}', [ProgressReportController::class, 'save'])->name('progressReport.save');
        Route::get('progressReport/get/{project_id}', [ProgressReportController::class, 'get'])->name('progressReport.get');
        Route::get('progressReport/preview/{project_id}', [ProgressReportController::class, 'preview'])->name('progressReport.preview');
        Route::get('progressReport/download/{project_id}', [ProgressReportController::class, 'download'])->name('progressReport.download');
        Route::get('progressReport/edit/{project_id}', [ProgressReportController::class, 'editForm'])->name('progressReport.edit');
        Route::post('deletefile', [OutcomesController::class, 'deletefile'])->name('deletefile');
        Route::get('announcementDetail/{id}', [HomeController::class, 'announcementDetail'])->name('announcementDetail'); //Admin
        Route::get('reviewerDetail/{u_id}', [UserController::class, 'reviewerDetail'])->name('reviewerDetail'); //Admin
        Route::get('userDetails', [DetailsController::class, 'userDetails'])->name('userDetails'); //specific user +Admin
        Route::get('/confprojects', [HomeController::class, 'confprojects'])->name('confprojects'); //All

        //Admin
        Route::middleware(['can:ADMIN'])->group(function () {

            //File Explorer
            Route::get('/file-explorer', [FileExplorerController::class, 'index'])->name('file.explorer');
            Route::get('/zip-folder', [FileExplorerController::class, 'downloadZip'])->name('zip.folder');
            Route::get('/list-projects', [FileExplorerController::class, 'listProjects'])->name('list.projects');
            Route::get('/zip-file', [FileExplorerController::class, 'downloadfile'])->name('zip.file');
            //BlockChain

            Route::view('fileupload', 'blockchain/fileupload')->name('fileupload');
            Route::post('blockchainuploadpost', [BlockChainController::class, 'blockchainuploadpost'])->name('blockchainuploadpost');
            Route::get('blockchainsummary', [BlockChainController::class, 'summary'])->name('blockchainsummary');

            //progress Details
            Route::get('AdminCycles', [AdminProgressDetails::class, 'index'])->name('AdminCycles'); //list cycles
            Route::get('AdminajaxListCycle', [AdminProgressDetails::class, 'ajaxListCycle'])->name('AdminajaxListCycle');  //cycle ajax
            Route::get('AdminProjects/{cycle}', [AdminProgressDetails::class, 'projects'])->name('AdminProjects'); //list projects
            Route::get('AdminajaxListProjects/{cycle}', [AdminProgressDetails::class, 'ajaxListProjects'])->name('AdminajaxListProjects');  //projecs ajax
            Route::get('adminProjectDetails/{project}', [AdminProgressDetails::class, 'projectDetails'])->name('adminProjectDetails'); //Admin
            Route::get('adminProgressSummary/{cycle}', [AdminProgressDetails::class, 'summary'])->name('adminProgressSummary'); //LPI

            //Progress Report 2 Extension
            Route::get('pr2-extension', [AdminProgressDetails::class, 'pr2Extension'])->name('pr2.extension');
            Route::get('ajaxListPr2Extension', [AdminProgressDetails::class, 'ajaxListPr2Extension'])->name('ajaxListPr2Extension');
            Route::post('pr2-extend-single', [AdminProgressDetails::class, 'pr2ExtendSingle'])->name('pr2.extend.single');
            Route::post('pr2-remove-single', [AdminProgressDetails::class, 'pr2RemoveSingle'])->name('pr2.remove.single');
            Route::post('pr2-extend-bulk', [AdminProgressDetails::class, 'pr2ExtendBulk'])->name('pr2.extend.bulk');

            //progress Details: Emails
            Route::post('SummaryEmail', [EmailController::class, 'SummaryEmail'])->name('SummaryEmail'); //LPI

            //1. Dashboard
            Route::get('dashboard', [DashboardController::class, 'load'])->name('dashboard'); //Admin

            //1. Users
            Route::post('create', [UserController::class, 'create'])->name('createUser'); //Admin
            Route::get('new', [UserController::class, 'new'])->name('newUser'); //Admin
            Route::get('edit/{id}', [UserController::class, 'edit'])->name('edit'); //Admin
            Route::post('update', [UserController::class, 'update'])->name('update'); //Admin
            Route::get('user', [UserController::class, 'display'])->name('user'); //Admin
            Route::get('user.ajaxList', [UserController::class, 'ajaxList'])->name('user.ajaxList');
            Route::get('downloadISO', [UserController::class, 'downloadISO'])->name('downloadISO');

            Route::post('verifyUsersPost', [UserController::class, 'verifyUsersPost'])->name('verifyUsersPost');
            Route::view('verifyUsers', 'verifyUsers')->name('verifyUsers'); //LPI &LPI+Reviewer

            //3. Cycles
            Route::get('cycle', [HomeController::class, 'cycle'])->name('cycle'); //Admin
            Route::get('home.cycle', [HomeController::class, 'ajaxListcycle'])->name('home.cycle'); //Admin
            Route::get('cycleEdit/{id}', [HomeController::class, 'cycleEdit'])->name('cycleEdit'); //Admin
            Route::post('cycleUpdate/{id}', [HomeController::class, 'cycleUpdate'])->name('cycleUpdate'); //Admin
            Route::post('createCycle', [HomeController::class, 'createCycle'])->name('createCycle'); //Admin
            Route::get('newCycle', [HomeController::class, 'newCycle'])->name('newCycle'); //Admin
            Route::get('deleteCycle/{id}', [HomeController::class, 'deleteCycle'])->name('deleteCycle'); //Admin

            //4. Project
            Route::post('updateProjectTag/{p_id}', [DetailsController::class, 'updateProjectTag'])->name('updateProjectTag'); //Admin
            Route::post('updateProjectPillar/{p_id}', [DetailsController::class, 'updateProjectPillar'])->name('updateProjectPillar'); //Admin
            Route::view('projectDetails', 'projectDetails'); //Admin

            //5. Progress Update
            Route::view('uploadProgressAdmin', 'uploadProgressAdmin')->name('uploadProgressAdmin'); //LPI &LPI+Reviewer
            Route::get('ajaxUploadProgressAdmin', [ProjectController::class, 'ajaxUploadProgressAdmin'])->name('ajaxUploadProgressAdmin'); //ADmin
            Route::post('saveProgressAdmin', [ProjectController::class, 'saveProgressAdmin'])->name('saveProgressAdmin'); //Admin

            //6. Reviewer Assignment
            Route::get('ajaxListcycleAssignView', [ProjectController::class, 'ajaxListcycleAssignView'])->name('ajaxListcycleAssignView');
            Route::get('assignReviewCycle', [ProjectController::class, 'assignViewCycle'])->name('assignReviewCycle'); //Admin
            Route::get('assignReview/{cycle}', [ProjectController::class, 'assignView'])->name('assignReview'); //Admin
            Route::get('ajaxListreviewer/{cycle}', [ProjectController::class, 'ajaxListreviewer'])->name('ajaxListreviewer'); //specific user +Admin
            Route::get('ajaxListgetcount/{r_id}', [ProjectController::class, 'ajaxListgetcount'])->name('ajaxListgetcount'); //specific user +Admin
            Route::get('AssignedReviewers', [ProjectController::class, 'AssignedReviewers'])->name('AssignedReviewers'); //Admin
            Route::get('/UnAssignReviewers/{id}', [ProjectController::class, 'UnAssignReviewers'])->name('UnAssignReviewers'); //Admin
            Route::get('ajaxListAssignedReviewers', [ProjectController::class, 'ajaxListAssignedReviewers'])->name('ajaxListAssignedReviewers'); //Admin
            Route::get('aReviewer', [ProjectController::class, 'countReviewer'])->name('countReviewer'); //Admin
            Route::post('assignReviewer/{p_id}', [DetailsController::class, 'assignReviewer'])->name('assignReviewer'); //Admin
            Route::view('reviewerAgrementAdmin', 'reviewerAgrementAdmin')->name('reviewerAgrementAdmin'); //LPI &LPI+Reviewer
            Route::get('ajaxreviewerAgrementsAdmin', [UserController::class, 'ajaxreviewerAgrementsAdmin'])->name('ajaxreviewerAgrementsAdmin'); //ADmin
            Route::post('bulk', [ProjectController::class, 'bulk'])->name('bulk'); //Admin



            //Announcements
            Route::get('announcementSetting', [HomeController::class, 'announcementSetting'])->name('announcementSetting'); //Admin
            Route::get('ajaxListAnnouncement', [HomeController::class, 'ajaxListAnnouncement'])->name('ajaxListAnnouncement'); //Admin
            Route::post('announcementUpdate/{id}', [HomeController::class, 'announcementUpdate'])->name('announcementUpdate'); //Admin
            Route::get('announcementEdit/{id}', [HomeController::class, 'announcementEdit'])->name('announcementEdit'); //Admin
            Route::view('newAnnouncement', 'newAnnouncement'); //Admin
            Route::post('newAnnouncement', [HomeController::class, 'newAnnouncement'])->name('newAnnouncement'); //All users

            //Budget API
            Route::get('fetchFailedEmails', [EmailController::class, 'fetchFailedEmails'])->name('fetchFailedEmails'); //Admin
            Route::post('budgetAPISync', [APIController::class, 'budgetAPISync'])->name('budgetAPISync'); //Admin
            Route::get('budgetAPIList', [APIController::class, 'budgetAPIList'])->name('budgetAPIList'); //Admin
            Route::get('ajaxBudgetAPIList', [APIController::class, 'ajaxBudgetAPIList'])->name('ajaxBudgetAPIList'); //Admin

            //reviewer grading
            Route::get('reviewer', [ProjectController::class, 'reviewer'])->name('reviewer'); //Admin
            Route::get('reviewerGrading/{u_id}', [UserController::class, 'reviewerGrading'])->name('reviewerGrading'); //Admin
            Route::get('ajaxListreviewerGrading', [UserController::class, 'ajaxListreviewerGrading'])->name('ajaxListreviewerGrading'); //Admin
            Route::post('saveratings', [UserController::class, 'saveratings'])->name('saveratings'); //Admin
            Route::get('reviewerEvaluationPublic/{u_id}', [DetailsController::class, 'reviewerEvaluationPublic'])->name('reviewerEvaluationPublic'); //Admin

            //Settings
            Route::post('guage', [HomeController::class, 'guage'])->name('guage'); //Admin
            Route::get('settings', [DetailsController::class, 'settings'])->name('settings'); //Admin
            Route::get('aboutUsSettings', [HomeController::class, 'aboutUsSettings'])->name('aboutUsSettings'); //Admin
            Route::get('aboutUsEdit/{id}', [HomeController::class, 'aboutUsEdit'])->name('aboutUsEdit'); //Admin
            Route::post('aboutUsUpdate/{id}', [HomeController::class, 'aboutUsUpdate'])->name('aboutUsUpdate'); //Admin
            Route::get('guageSetting', [HomeController::class, 'guageSetting'])->name('guageSetting'); //Admin
            Route::get('emailSetting', [EmailController::class, 'emailSetting'])->name('emailSetting'); //ADmin
            Route::get('ajaxListemailSetting', [EmailController::class, 'ajaxListemailSetting'])->name('ajaxListemailSetting'); //ADmin
            Route::get('emailEdit/{id}', [EmailController::class, 'emailEdit'])->name('emailEdit'); //Admin
            Route::post('emailUpdate/{id}', [EmailController::class, 'emailUpdate'])->name('emailUpdate'); //Admin

            //Emails
            Route::view('emailNew', 'emailNew'); //Reviewer & Admin
            Route::post('emailNew', [EmailController::class, 'emailNew'])->name('emailNew'); //Admin
            Route::get('sendBudgetReminder/{project_id}', [EmailController::class, 'sendBudgetReminder'])->name('sendBudgetReminder'); //ADmin
            Route::get('smtpSettings', [EmailController::class, 'smtpSettings'])->name('smtpSettings'); //ADmin
            Route::post('savesmtpSettings', [EmailController::class, 'savesmtpSettings'])->name('savesmtpSettings'); //Admin
            Route::get('sendEmailAdmin', [EmailController::class, 'sendEmailAdmin'])->name('sendEmailAdmin'); //ADmin
            Route::post('sendEmailAdminSave', [EmailController::class, 'sendEmailAdminSave'])->name('sendEmailAdminSave'); //Admin
            Route::view('EmailSendingStatus', 'EmailSendingStatus')->name('EmailSendingStatus'); //LPI &LPI+Reviewer
            Route::get('ajaxEmailSendingStatus', [EmailController::class, 'ajaxEmailSendingStatus'])->name('ajaxEmailSendingStatus'); //ADmin
            Route::get('MarkEmailProcessed/{id}', [EmailController::class, 'MarkEmailProcessed'])->name('MarkEmailProcessed'); //Admin

            //Files
            Route::get('/files', 'FileController@index');
            Route::post('/files', 'FileController@store');
            Route::delete('/files/{id}', 'FileController@destroy');


            //FTP
            Route::get('listFilesAndFolders', [FtpController::class, 'listFilesAndFolders'])->name('listFilesAndFolders');
            Route::get('downloadFile', [FtpController::class, 'downloadFile'])->name('downloadFile');
            Route::get('systemBackup', [FtpController::class, 'systemBackup'])->name('systemBackup');

            //     Route::get('uploadftp', [FtpController::class, 'uploadftp'])->name('uploadftp');

            Route::get('welcomeEmail', function () {
                return new welcomeEmail();
            });
        });


        //LPI
        Route::middleware(['can:LPI'])->group(function () {
            Route::get('commitments', [ProjectController::class, 'commitments'])->name('commitments'); //LPI

        });


        //Reviewer
        Route::middleware(['can:REVIEWER'])->group(function () {

            Route::get('reviewerDetails', [UserController::class, 'reviewerDetail'])->name('reviewerDetails'); //Admin
            Route::get('acceptProposal/{r_id}', [ProjectController::class, 'acceptProposal'])->name('acceptProposal');
            Route::post('acceptProposalPost', [ProjectController::class, 'acceptProposalPost'])->name('acceptProposalPost');
        });


        // //Admin + LPI>
        // Route::middleware(['can:ADMIN_LPI'])->group(function () {

        //     Route::get('ajaxListGradedprojects', [ProjectController::class, 'ajaxListGradedprojects'])->name('ajaxListGradedprojects'); //Admin
        //     Route::get('gradedProjects', [ProjectController::class, 'graded'])->name('gradedProjects'); //Admin

        //     Route::post('CreatePro', [ProjectController::class, 'create'])->name('Create'); //LPI && Admin sometimes
        //     Route::get('userDetail/{u_id}', [DetailsController::class, 'userDetail'])->name('userDetail'); //specific user +Admin
        //     Route::get('projectDetails/{p_id}', [DetailsController::class, 'projectDetails'])->name('projectDetails'); //Admin
        //     Route::get('ajaxList/{id}', [DetailsController::class, 'ajaxList'])->name('ajaxList'); //specific user +Admin
        //     Route::get('upload/{p_id}', [ProjectController::class, 'upload'])->name('upload'); //LPI
        // });

        // //Admin + Reviewer
        // Route::middleware(['can:ADMIN_REVIEWER'])->group(function () {

        //     Route::post('finalGrades', [grading::class, 'finalGrades'])->name('finalGrades');
        //     Route::view('gradingTabs', 'gradingTabs');
        //     Route::get('grading/{p_id}', [ProjectController::class, 'grading'])->name('grading'); //Reviewer and Admins
        //     Route::get('reviewerEvaluation/{u_id}', [DetailsController::class, 'reviewerEvaluation'])->name('reviewerEvaluation'); //Admin
        // });

        // //LPI + Reviewer
        // Route::middleware(['can:LPI_LPIREVIEWER'])->group(function () {

        //     Route::post('reportUpload/{p_id}', [ProjectController::class, 'reportUpload'])->name('reportUpload'); // LPI && LPI+Reviewer
        //     Route::get('uploadedOutcomes', [DetailsController::class, 'uploadedOutcomes'])->name('uploadedOutcomes'); //LPI & LPI+Reviewer
        //     Route::post('uploadedOutcomesDelete/{p_id}', [DetailsController::class, 'uploadedOutcomesDelete'])->name('uploadedOutcomesDelete'); //LPI & LPI+Reviewer
        //     Route::post('uploadedOutcomesDeleteStudent/{id}', [DetailsController::class, 'uploadedOutcomesDeleteStudent'])->name('uploadedOutcomesDeleteStudent'); //LPI & LPI+Reviewer
        //     Route::post('uploadedOutcomesDeleteContribution/{id}', [DetailsController::class, 'uploadedOutcomesDeleteContribution'])->name('uploadedOutcomesDeleteContribution'); //LPI & LPI+Reviewer
        //     Route::get('projectOutcomes', [OutcomesController::class, 'projectOutcomes'])->name('projectOutcomes');
        //     Route::post('projectOutcome', [OutcomesController::class, 'projectOutcome'])->name('projectOutcome');
        //     Route::post('projectOutcome2', [OutcomesController::class, 'projectOutcome2'])->name('projectOutcome2');
        //     Route::post('projectOutcome3', [OutcomesController::class, 'projectOutcome3'])->name('projectOutcome3');
        // });

        //Test
        Route::middleware(['can:TEST'])->group(function () {

            //test route to check authorization for role test
            Route::get('tests', function () {
                echo 'ok';
            });

            Route::view('trying', 'trying');
            Route::get('uHass', [OutcomesController::class, 'uHass'])->name('uHass');
            Route::post('outcome', [ProjectController::class, 'outcome'])->name('outcome');
            Route::get('proposal/{p_id}', [ProjectController::class, 'proposal'])->name('proposal');

            Route::post('updateStatus', [grading::class, 'statusUpdate'])->name('statusUpdate');
            Route::get('getProject/{p_id}', [grading::class, 'getProject'])->name('getProject');
            Route::post('print', [ProjectController::class, 'print'])->name('print');
            Route::get('test', [ProjectController::class, 'test'])->name('test');
            Route::get('CompleteInfo', [EmailController::class, 'CompleteInfo'])->name('CompleteInfo');

            Route::get('testingEmail', [EmailController::class, 'testingEmail'])->name('testingEmail');
            Route::get('pdf/{p_id}', [grading::class, 'index'])->name('pdf');
            Route::get('doi', [test::class, 'doi'])->name('doi');
            Route::get('sort_name', [UserController::class, 'sortByname'])->name('sort_name');
            Route::get('sort_email', [UserController::class, 'sortByemail'])->name('sort_email');
            Route::get('sort_title', [ProjectController::class, 'sortBytitle'])->name('sort_title');
            Route::get('search_project', [ProjectController::class, 'searchByProject'])->name('search_project');
            Route::get('search_user', [UserController::class, 'searchByUser'])->name('search_user');
            Route::post('hassOutcomes', [OutcomesController::class, 'hassOutcomes'])->name('hassOutcomes');

            Route::post('verifyOutcomes', [grading::class, 'verifyOutcomes'])->name('verifyOutcomes');
            //  Route::get('announcement', [EmailController::class, 'announcement'])->name('announcement');
            //   Route::get('project_added', [EmailController::class, 'project_added'])->name('project_added');
            Route::view('TrialDashboard', 'TrialDashboard');
            Route::get('DBquery', [grading::class, 'DBquery'])->name('DBquery');
            Route::get('select', [grading::class, 'select'])->name('select');
            Route::get('API_DOI', [grading::class, 'API_DOI'])->name('API_DOI');
            Route::get('API', [OutcomesController::class, 'API'])->name('API');
            Route::get('elsevierAPI', [grading::class, 'elsevierAPI'])->name('elsevierAPI');
            Route::post('publish', [grading::class, 'publish'])->name('publish');
            Route::get('serveImg', [HomeController::class, 'serveFile'])->name('serveImg');
            Route::view('outcomeInfo', 'outcomeInfo');
            Route::post('printed', [ProjectController::class, 'printed'])->name('printed');
            Route::get('testing2', [OutcomesController::class, 'testing2'])->name('testing2');
        });
    });
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
