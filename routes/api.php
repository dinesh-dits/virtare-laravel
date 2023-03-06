<?php

use Illuminate\Support\Facades\Route;
use App\Services\Api\PushNotificationService;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('client/addDefaultClient', 'Api\v1\ClientController@addDefaultClient');
Route::get('test', 'Api\v1\TestController@index');

Route::get('/testpdf/{id?}', [
    'middleware' => 'role:testpdfname',
    'as' => 'testpdfname',
    'uses' => 'Api\v1\CreatePdfController@createPdf'
]);

// cpt billing for call
Route::get('cptCodes/call/billing', 'Api\v1\CPTCodeController@cptNextBillingForCall');
Route::get('cptCodes/billing/process', 'Api\v1\CPTCodeController@processNextBillingDetail');
Route::get('cptCodes/billing/insert', 'Api\v1\CPTCodeController@insertNextBillingServiceDetail');

/* Coustom form APIS */
Route::get('control-fields', [
    'middleware' => 'role:control-fields', 'as' => 'control-fields', 'uses' => 'Api\v1\CustomFormController@getCustomFields']);
Route::get('assign-workflow', 'Api\v1\WorkflowController@assign_workflow');
// Staff Group
Route::get('staff/{id}/group/{groupId?}', 'Api\v1\StaffController@listStaffGroup');
// provider Group
Route::get('provider/{id}/group/{groupId?}', 'Api\v1\ProviderController@listProviderGroup');
Route::get('email-stats', 'Api\v1\EmailStatsController@getStats');
Route::get('email/count', 'Api\v1\EmailStatsController@emailCount');
Route::get('email/graph', 'Api\v1\EmailStatsController@emailGraph');/*
Route::group(['middleware' => 'haveAccess'], function () use ($router) {
    Route::get('testpdf', 'Api\v1\CreatePdfController@createPdf');
});*/
Route::post('update-email-stats', 'Api\v1\EmailStatsController@update_stats');
Route::get('encryptdata', 'Api\v1\PatientController@encryptdata');
Route::get('patientTimelog/report/export/{id}', 'Api\v1\TimeLogController@timeLogReport');
Route::get('task/report/export/{id}', 'Api\v1\TaskController@taskReport');
Route::get('cptCode/report/export/{id}', 'Api\v1\CPTCodeController@cptCodeReport');
Route::get('generalParameter/report/export/{id}', 'Api\v1\GeneralParameterController@generalParameterReport');
Route::get('template/report/export/{id}', 'Api\v1\TemplateController@templateReport');
Route::get('inventory/report/export/{id}', 'Api\v1\InventoryController@inventoryReport');
Route::get('communication/report/export/{id}', 'Api\v1\CommunicationController@communicationReport');
Route::get('careCoordinator/report/export/{id}', 'Api\v1\StaffController@careCoordinatorReport');
Route::get('program/report/export/{id}', 'Api\v1\ProgramController@programReport');
Route::get('provider/report/export/{id}', 'Api\v1\ProviderController@providerReport');
Route::get('roleAndPermission/report/export/{id}', 'Api\v1\RolePermissionController@roleAndPermissionReport');
Route::get('patient/report/export/{id}', 'Api\v1\PatientController@patientReport');
Route::get('globalCode/report/export/{id}', 'Api\v1\GlobalCodeController@globalCodeReport');
Route::get('cptBilling/report/export/{id}', 'Api\v1\CPTCodeController@cptBillingReport');
Route::get('timelogApproval/report/export/{id}', 'Api\v1\TimeApprovalController@timelogApprovalReport');
Route::get('referral/report/export/{id}', 'Api\v1\PatientController@referralReport');
Route::get('escalation/report/export/{id}', 'Api\v1\EscalationController@escalationReport');
Route::get('escalationAudit/report/export/{id}', 'Api\v1\EscalationController@escalationAuditReport');
Route::get('patientVital/report/export/{id}', 'Api\v1\PatientController@patientVitalReport');
Route::get('specialists/report/export/{id}', 'Api\v1\StaffController@specialistsReport');

// pdf
Route::get('vital/pdf/report/{id}', 'Api\v1\VitalController@vitalPdfReport');

Route::get('error/logs/{id}', 'Api\v1\ErrorLogController@listErrorLog');

// forgot password
Route::post('forgot/password', 'Api\v1\UserController@forgotPassword');
Route::post('/generate/newPassword/', 'Api\v1\UserController@newPassword');
Route::get('/forgotPassword/verify/{code}', 'Api\v1\UserController@forgotPasswordCodeVerify');

//send message
Route::post('/send/message/', 'Api\v1\DashboardController@sendMessage');
Route::post('/send/email/message/', 'Api\v1\UserController@testMail');

//error log with device info
Route::post('/errorLog/with/deviceInfo', 'Api\v1\ErrorLogController@errorLogWithDeviceInfo');


Route::get('/linkstorage', function () use ($router) {

    /*$public = getcwd();
    $storage = dirname(getcwd()) . "/storage";

    $command = 'ln -s ' . $storage . ' ' . $public;

    system($command);*/
    $confrence = "";
    Helper::updateFreeswitchConfrence($confrence);
});
Route::get('/notification/default', function () use ($router) {

    $pushnotification = new PushNotificationService();
    $notificationData = array(
        "body" => "Please answer",
        "title" => "Call Recived",
        "type" => "call",
        "typeId" => "test",
    );
    $pushnotification::sendNotification([279], $notificationData);
});

Route::get('/', function () use ($router) {
});
Route::post('/webhook/blackbox', 'Api\v1\WebhookController@addBlackbox');
Route::get('/webhook/blackbox', 'Api\v1\WebhookController@getBlackbox');
Route::get('webhook/blackboxnew', 'Api\v1\WebhookNewController@getBlackbox');
Route::post('webhook/blackboxnew', 'Api\v1\WebhookNewController@addBlackbox');


Route::post('login', 'Api\v1\AuthController@login');
Route::post('refreshToken', 'Api\v1\AuthController@refreshToken');
Route::group(['middleware' => 'auth:api'], function () use ($router) {
    /* Custom FORM -SS*/
    Route::post('save-form', 'Api\v1\CustomFormController@saveForm');
    Route::get('custom-forms', 'Api\v1\CustomFormController@getAllForms');
    Route::get('custom-forms/{id}', 'Api\v1\CustomFormController@FormDetail');
    Route::delete('custom-forms/{id}', 'Api\v1\CustomFormController@deleteForm');
    Route::post('save-form-data', 'Api\v1\CustomFormController@saveFormData');
    Route::get('form-response/{id}/{userId}', 'Api\v1\CustomFormController@getResponseData');
    Route::post('assign-form', 'Api\v1\CustomFormController@assignForm');
    Route::get('get-assigned-forms/{id}', 'Api\v1\CustomFormController@getAssignedForm');
    Route::get('get-custom-templates', 'Api\v1\CustomFormController@get_custom_templates');
    Route::get('get-template-detail/{id}', 'Api\v1\CustomFormController@getTemplateDetail');
    Route::get('get-template-question-section/{id}', 'Api\v1\CustomFormController@getTemplateQuestionSection');
    Route::post('save-response-template-question-section', 'Api\v1\CustomFormController@save_response_template_question_section');

    //Route::get('get-steps-forms/{id}', 'Api\v1\CustomFormController@get_steps_forms');
    Route::get('get-steps-forms/{id}', 'Api\v1\CustomFormController@get_assigned_workflow');

    Route::get('get-steps-score/{id}', 'Api\v1\CustomFormController@get_steps_score');
    Route::get('all-roles/{id?}', 'Api\v1\RolePermissionController@getAllRoles');

    /* Custom FORM ENDS -SS*/

    //Get notifications Routes
    Route::get('appointment/notification', 'Api\v1\NotificationController@appointmentNotification');
    Route::get('appointment/detail/{id}', 'Api\v1\AppointmentController@appointmentDetail');
    Route::put('appointment/status/{id}', 'Api\v1\AppointmentController@appointmentStatus');
    // Auth Routes
    Route::get('userProfile', 'Api\v1\UserController@userProfile');
    Route::post('logout', 'Api\v1\AuthController@logout');

    Route::get('staff/network', 'Api\v1\DashboardController@staffNetwork');
    Route::get('staff/specialization', 'Api\v1\DashboardController@staffSpecialization');

    // Call Router
    Route::patch('communicationCallRecord/{id}', 'Api\v1\CommunicationController@callUpdate');
    Route::patch('callRecord/{id}', 'Api\v1\CommunicationController@callUpdateByStaff');
    Route::post('callRecordCommunication', 'Api\v1\CommunicationController@callAddPatient');
    Route::patch('callRecordCommunication/{id}', 'Api\v1\CommunicationController@callUpdateByPatient');

    Route::post('patient/call/{id}', 'Api\v1\CommunicationController@patientCommunicationCalls');
    Route::put('patient/call/complete', 'Api\v1\CommunicationController@patientCommunicationCallStatusUpdate');
    Route::get('patient/call/status/{id?}', 'Api\v1\CommunicationController@callStatusList');

    // Staff Routes
    Route::get('staff/access', 'Api\v1\AccessRoleController@assignedRoles');
    Route::get('staff/patient', 'Api\v1\StaffPatientController@patientList');
    Route::get('staff/{id}/patient', 'Api\v1\StaffPatientController@patientList');
    Route::get('staff/appointment', 'Api\v1\StaffPatientController@appointmentList');
    Route::get('staff/{id}/appointment', 'Api\v1\StaffPatientController@appointmentList');
     // Staff Client Locations
    Route::get('locations', 'Api\v1\StaffController@getLocation');

    Route::get('patient/appointment', 'Api\v1\StaffPatientController@patientAppointment');
    Route::get('patient/{id}/appointment', 'Api\v1\StaffPatientController@patientAppointment');

    Route::get('staff/access/action/{id?}', 'Api\v1\AccessRoleController@assignedRoleAction');
    Route::get('group/access/action/{id?}', 'Api\v1\AccessRoleController@assignedRoleActionGroup');

    // team Routes
    Route::get('team', 'Api\v1\TeamController@all');
    Route::get('team/{type}/{id?}', 'Api\v1\TeamController@team');
    //  FamilyMember Login Team Routes
    Route::get('patient/{patientId}/team', 'Api\v1\TeamController@all');
    Route::get('patient/{patientId}/team/{type}/{id?}', 'Api\v1\TeamController@team');

    // patient Routes
    Route::post('patient/{id}/family', 'Api\v1\PatientController@createFamily');
    Route::put('patient/{id}/family/{familyId}', 'Api\v1\PatientController@createFamily');
    Route::get('patientInventory', 'Api\v1\PatientController@listingPatientInventory');
    // team Routes
    Route::get('team/{type}/{id?}', 'Api\v1\TeamController@team');
    Route::get('team', 'Api\v1\TeamController@all');
    //Route::get('team/{patientId}/{type}/{id?}', 'Api\v1\TeamController@team');
    Route::get('patient/condition/count', 'Api\v1\DashboardController@patientConditionCount');
    Route::get('patient/abnormal', 'Api\v1\DashboardController@abnormalPatients');
    Route::get('patient/critical', 'Api\v1\DashboardController@criticalPatients');
    Route::get('patient/condition', 'Api\v1\DashboardController@patientCondition');
    // Dashboard Routes
    Route::get('patient/chart', 'Api\v1\TimelineController@patientTotal');
    Route::get('patient/count', 'Api\v1\ClinicalDashboardController@patientCount');

    // patient Routes
    Route::post('family', 'Api\v1\PatientController@createFamily');
    Route::put('family/{id}', 'Api\v1\PatientController@createFamily');
    Route::put('inventory/{id}/link', 'Api\v1\PatientController@inventory');
    Route::post('patient/vital', 'Api\v1\PatientController@createPatientVital');
    Route::get('patient/vital', 'Api\v1\PatientController@listPatientVital');
    Route::get('patient/{id}/vital', 'Api\v1\PatientController@listPatientVital');
    Route::get('patient/{id}/vital/{vitalType}', 'Api\v1\PatientController@latest');
    Route::get('patient/vital/{vitalType}', 'Api\v1\PatientController@latest');
    Route::get('patient/vitalNew', 'Api\v1\PatientController@vital');
    Route::post('patient/{id}/device', 'Api\v1\PatientController@createPatientDevice');
    Route::post('patient/device', 'Api\v1\PatientController@createPatientDevice');
    Route::put('patient/{id}/device/{deviceId}', 'Api\v1\PatientController@createPatientDevice');
    Route::put('patient/device/{deviceId}', 'Api\v1\PatientController@createPatientDevice');
    Route::get('patient/{id}/device/{deviceId?}', 'Api\v1\PatientController@listPatientDevice');
    Route::get('patient/device/{deviceId?}', 'Api\v1\PatientController@listPatientDevice');

    Route::get('patient/{id}/goal/{goalId?}', 'Api\v1\PatientGoalController@index');
    Route::post('patient/{id}/goal', 'Api\v1\PatientGoalController@addPatientGoal');
    Route::delete('patient/{id}/goal/{goalId}', 'Api\v1\PatientGoalController@deletePatientGoal');
    Route::get('patient/goal/{goalId?}', 'Api\v1\PatientGoalController@index');
    Route::get('patient/notes', 'Api\v1\NoteController@patientNote');

    Route::post('patient/{id}/flag', 'Api\v1\PatientController@addPatientFlag');
    Route::get('patient/{id}/flag/{flagId?}', 'Api\v1\PatientController@listPatientFlag');
    Route::delete('patient/{id}/flag/{flagId}', 'Api\v1\PatientController@deletePatientFlag');
    Route::post('patient/{id}/flag/delete', 'Api\v1\PatientController@deletePatientFlag');
    Route::post('patient/{id}/flag/{flagId}', 'Api\v1\PatientController@deletePatientFlag');
    Route::get('patientFlagCount', 'Api\v1\ClinicalDashboardController@listPatientFlagCount');
    Route::get('patientFlag', 'Api\v1\PatientController@listPatientFlag');
    Route::get('{entity}/{id}/task', 'Api\v1\TaskController@taskListEntity');
    Route::post('patient/{id}/staff', 'Api\v1\PatientController@latest');

    Route::post('patient', 'Api\v1\PatientController@createPatient');
    Route::put('patient/{id}', 'Api\v1\PatientController@createPatient');
    Route::put('patientStatus/{id}', 'Api\v1\PatientController@updatePatientStatus');
    Route::put('patient/{id}/resetPassword', 'Api\v1\PatientController@resetPassword');
    Route::get('patient/{id?}', 'Api\v1\PatientController@listPatient');
    Route::delete('patient/{id}', 'Api\v1\PatientController@deletePatient');
    Route::post('patient/{id}/condition', 'Api\v1\PatientController@createPatientCondition');
    Route::put('patient/{id}/condition/{conditionId}', 'Api\v1\PatientController@createPatientCondition');
    Route::delete('patient/{id}/condition/{conditionId}', 'Api\v1\PatientController@deletePatientCondition');
    Route::get('patient/{id}/condition/{conditionId?}', 'Api\v1\PatientController@listPatientCondition');
    // Route::post('patient/{id}/provider', 'Api\v1\PatientController@patientProvider');
    Route::put('patient/{id}/provider', 'Api\v1\PatientController@patientProviderUpdate');
    Route::post('staff', 'Api\v1\StaffController@addStaff');


    Route::put('staff/{id}/status', 'Api\v1\StaffController@updateStaffStatus');
    Route::get('staff/{id?}', 'Api\v1\StaffController@listStaff');
    Route::put('staff/{id}', 'Api\v1\StaffController@updateStaff');
    Route::delete('staff/{id}', 'Api\v1\StaffController@deleteStaff');
    Route::post('patient/{id}/referral', 'Api\v1\PatientController@createPatientReferals');
    Route::put('patient/{id}/referral/{referalsId}', 'Api\v1\PatientController@updatePatientReferals');
    Route::get('patient/{id}/referral', 'Api\v1\PatientController@listPatientReferral');
    Route::get('referral', 'Api\v1\PatientController@referral');
    Route::get('referalCount', 'Api\v1\BusinessDashboardController@referalCount');
    Route::delete('patient/{id}/referals/{referalsId}', 'Api\v1\PatientController@deletePatientReferals');
    Route::post('patient/{id}/physician', 'Api\v1\PatientController@createPatientPhysician');
    Route::put('patient/{id}/physician/{physicianId}', 'Api\v1\PatientController@updatePatientPhysician');
    Route::get('patient/{id}/physician/{physicianId?}', 'Api\v1\PatientController@listPatientPhysician');
    Route::delete('patient/{id}/physician/{physicianId}', 'Api\v1\PatientController@deletePatientPhysician');
    Route::post('patient/{id}/program', 'Api\v1\PatientController@createPatientProgram');
    Route::put('patient/{id}/program', 'Api\v1\PatientController@updatePatientProgram');
    Route::get('patient/{id}/program/{programId?}', 'Api\v1\PatientController@listPatientProgram');
    Route::delete('patient/{id}/program/{programId}', 'Api\v1\PatientController@deletePatientProgram');
    Route::post('patient/{id}/inventory', 'Api\v1\PatientController@createPatientInventory');
    Route::put('patient/{id}/inventory/{inventoryId}', 'Api\v1\PatientController@updatePatientInventory');
    Route::delete('patient/{id}/inventory/{inventoryId}', 'Api\v1\PatientController@deletePatientInventory');
    Route::get('patient/{id}/inventory/{inventoryId?}', 'Api\v1\PatientController@listPatientInventory');
    Route::post('patient/{id}/vital', 'Api\v1\PatientController@createPatientVital');
    Route::put('patient/{id}/vital/{vitalId}', 'Api\v1\PatientController@createPatientVital');
    Route::delete('patient/{id}/vital/{vitalId}', 'Api\v1\PatientController@deletePatientVital');
    Route::post('patient/{id}/medicalHistory', 'Api\v1\PatientController@createPatientMedicalHistory');
    Route::put('patient/{id}/medicalHistory/{medicalHistoryId}', 'Api\v1\PatientController@createPatientMedicalHistory');
    Route::get('patient/{id}/medicalHistory/{medicalHistoryId?}', 'Api\v1\PatientController@listPatientMedicalHistory');
    Route::delete('patient/{id}/medicalHistory/{medicalHistoryId}', 'Api\v1\PatientController@deletePatientMedicalHistory');
    Route::post('patient/{id}/medicalRoutine', 'Api\v1\PatientController@createPatientMedicalRoutine');
    Route::put('patient/{id}/medicalRoutine/{medicalRoutineId}', 'Api\v1\PatientController@createPatientMedicalRoutine');
    Route::get('patient/{id}/medicalRoutine/{medicalRoutineId?}', 'Api\v1\PatientController@listPatientMedicalRoutine');
    Route::delete('patient/{id}/medicalRoutine/{medicalRoutineId}', 'Api\v1\PatientController@deletePatientMedicalRoutine');
    Route::post('patient/{id}/insurance', 'Api\v1\PatientController@createPatientInsurance');
    Route::put('patient/{id}/insurance/{insuranceId?}', 'Api\v1\PatientController@createPatientInsurance');
    Route::get('patient/{id}/insurance/{insuranceId?}', 'Api\v1\PatientController@listPatientInsurance');
    Route::delete('patient/{id}/insurance/{insuranceId}', 'Api\v1\PatientController@deletePatientInsurance');
    Route::get('patient/{id}/timeLine', 'Api\v1\PatientController@listPatientTimeline');
    Route::post('patient/{id}/responsible', 'Api\v1\PatientController@patientResponsible');
    Route::put('patient/{id}/responsible/{responsibleId}', 'Api\v1\PatientController@patientResponsible');
    Route::get('patient/{id}/responsible/{responsibleId?}', 'Api\v1\PatientController@listPatientResponsible');

    // patient summary family member
    Route::post('patient/{id}/familyAdd', 'Api\v1\PatientController@addPatientFamily');
    Route::get('patient/{id}/family/{familyId?}', 'Api\v1\PatientController@listPatientFamily');
    Route::put('patient/{id}/familyUpdate/{familyId}', 'Api\v1\PatientController@addPatientFamily');
    Route::delete('patient/{id}/family/{familyId}', 'Api\v1\PatientController@deletePatientFamily');

    // patient summary emergency contact
    Route::post('patient/{id}/emergency', 'Api\v1\PatientController@addPatientEmergency');
    Route::get('patient/{id}/emergency/{emergencyId?}', 'Api\v1\PatientController@listPatientEmergency');
    Route::put('patient/{id}/emergency/{emergencyId}', 'Api\v1\PatientController@addPatientEmergency');
    Route::delete('patient/{id}/emergency/{emergencyId}', 'Api\v1\PatientController@deletePatientEmergency');

    Route::get('patient/{id}/criticalNote/{noteId?}', 'Api\v1\PatientController@listPatientCriticalNote');
    Route::post('patient/{id}/criticalNote', 'Api\v1\PatientController@createPatientCriticalNote');
    Route::put('patient/{id}/criticalNote/{noteId}', 'Api\v1\PatientController@updatePatientCriticalNote');
    Route::delete('patient/{id}/criticalNote/{noteId}', 'Api\v1\PatientController@deletePatientCriticalNote');

    // Patient Profile Photo
    Route::put('patientProfile/{id}', 'Api\v1\PatientController@updateProfile');

    // Staff Profile Photo
    Route::put('staffProfile/{id}', 'Api\v1\StaffController@staffProfileUpdate');

    Route::group(['prefix' => 'client', 'as' => 'client.'], function () use ($router) {

        // Client Routes
        Route::get('get-patients/{id}', 'Api\v1\ClientController@get_patients');
        Route::get('get-all-address/{id}', 'Api\v1\ClientController@getAllAddress');
        Route::post('addClient', 'Api\v1\ClientController@addClient');
        Route::get('getClient/{id?}', 'Api\v1\ClientController@listClient');
        Route::put('updateClient/{id}', 'Api\v1\ClientController@updateClient');
        Route::put('updateStatus/{id}', 'Api\v1\ClientController@updateStatus');
        Route::put('un-suspend/{id}', 'Api\v1\ClientController@unSuspendClient');
        Route::delete('destroyClient/{id}', 'Api\v1\ClientController@deleteClient');


        // Site Routes
        Route::post('{id}/site', 'Api\v1\SiteController@addSite');
        Route::get('{id}/site/{siteId?}', 'Api\v1\SiteController@listSite');
        Route::get('{id}/siteList', 'Api\v1\SiteController@siteList');
        Route::put('{id}/site/{siteId}', 'Api\v1\SiteController@updateSite');
        Route::delete('{id}/site/{siteId}', 'Api\v1\SiteController@deleteSite');
    });
    Route::get('{entity}/{id}/program', 'Api\v1\ClientController@program');


    Route::group(['prefix' => 'careTeam', 'as' => 'careTeam.'], function () use ($router) {
        // care team Routes
        Route::post('create', 'Api\v1\CareTeamController@createCareTeam');
        Route::get('list/{id?}', 'Api\v1\CareTeamController@listCareTeam');
        Route::get('listBySiteId/{id}', 'Api\v1\CareTeamController@listCareTeamBySiteId');
        Route::get('listByClient/{id}', 'Api\v1\CareTeamController@careTeamListByClientId');
        Route::put('update/{id}', 'Api\v1\CareTeamController@updateCareTeam');
        Route::delete('destroy/{id}', 'Api\v1\CareTeamController@deleteCareTeam');
        //member routes
        Route::group(['prefix' => 'member', 'as' => 'member.'], function () use ($router) {
            Route::post('addMember', 'Api\v1\CareTeamMemberController@addMember');
            Route::get('list/{id?}', 'Api\v1\CareTeamMemberController@listCareTeamMember');
            Route::get('listByContactId/{id}', 'Api\v1\CareTeamMemberController@listCareTeamMemberByContactId');
            Route::get('listByCareTeamId/{id}', 'Api\v1\CareTeamMemberController@listCareTeamMemberByCareTeamId');
            Route::delete('destroy/{id}', 'Api\v1\CareTeamMemberController@deleteCareTeamMember');
        });
    });

    Route::group(['prefix' => 'people', 'as' => 'people.'], function () use ($router) {
        Route::post('create', 'Api\v1\PeopleController@createPeople');
        Route::get('list/{id}', 'Api\v1\PeopleController@listPeople');
        Route::get('detail/{id}', 'Api\v1\PeopleController@detailPeople');
        Route::get('list/users/{id}/{type?}', 'Api\v1\PeopleController@listuser');
        Route::put('update/{id}', 'Api\v1\PeopleController@updatePeople');
    });

    Route::post('{entity}/{id}/contact', 'Api\v1\ContactController@addContact');
    Route::get('{entity}/{id}/contact/{contactId?}', 'Api\v1\ContactController@listContact');
    Route::put('{entity}/{id}/contact/{contactId}', 'Api\v1\ContactController@updateContact');
    Route::delete('{entity}/{id}/contact/{contactId}', 'Api\v1\ContactController@deleteContact');


    // Patient Staff Routes
    Route::post('patient/{id}/staff', 'Api\v1\PatientStaffController@assignStaff');
    Route::get('patient/{id}/staff/{patientStaffId?}', 'Api\v1\PatientStaffController@getAssignStaff');
    Route::put('patient/{id}/staff/{patientStaffId}', 'Api\v1\PatientStaffController@assignStaff');
    Route::delete('patient/{id}/staff/{patientStaffId}', 'Api\v1\PatientStaffController@deleteAssignStaff');

    // Timelog Routes
    Route::get('timeLog/{id?}', 'Api\v1\TimeLogController@listTimeLog');
    Route::put('timeLog/{id}', 'Api\v1\TimeLogController@updateTimeLog');
    Route::delete('timeLog/{id}', 'Api\v1\TimeLogController@deleteTimeLog');

    // Patient Timelog Routes
    Route::post('{entityType}/{id}/timeLog', 'Api\v1\TimeLogController@addPatientTimeLog');
    Route::get('{entityType}/{id}/timeLog/{timelogId?}', 'Api\v1\TimeLogController@listPatientTimeLog');
    Route::put('{entityType}/{id}/timeLog/{timelogId}', 'Api\v1\TimeLogController@addPatientTimeLog');
    Route::delete('{entityType}/{id}/timeLog/{timelogId}', 'Api\v1\TimeLogController@deletePatientTimeLog');

    /*
    *Bitrix APi routes
    */
    /*
    *Bitrix APi routes
    */
    Route::get("bitrix/deal/{patientId}", 'Api\v1\PatientController@getAllBitrixDeals');
    Route::get("bitrix/deal", 'Api\v1\PatientController@getAllBitrixDeals');

    // Bitrix Fields routes
    Route::get("bitrix/fields", 'Api\v1\BitrixFieldController@listBitrixField');
    Route::get("bitrix/field/{id}", 'Api\v1\BitrixFieldController@listBitrixField');
    Route::post("bitrix/field", 'Api\v1\BitrixFieldController@createBitrixField');
    Route::put("bitrix/field/{id}", 'Api\v1\BitrixFieldController@updateBitrixField');
    Route::post("bitrix/field/{id}", 'Api\v1\BitrixFieldController@deleteBitrixField');

    // appointment Routes
    // Route::get('patient/vital', 'Api\v1\PatientController@listPatientVital');

    // globalstartEnd Routes
    Route::get('globalstartEnd/date', 'Api\v1\GlobalCodeController@globalStartEndDate');

    // Export Excel Report Routes
    Route::post('export/report/request', 'Api\v1\ExportReportRequestController@addExportRequest');

    // Pdf Report Routes
    Route::post('pdf/report/request', 'Api\v1\ExportReportRequestController@addPdfExportRequest');

    // appointment Routes
    Route::post('appointment/calls', 'Api\v1\AppointmentController@appointmentCalls');
    Route::get('appointment/conference', 'Api\v1\AppointmentController@conferenceAppointment');
    Route::get('appointment/conference/{id}', 'Api\v1\AppointmentController@conferenceIdAppointment');
    Route::get('appointment/new', 'Api\v1\AppointmentController@newAppointments');
    Route::get('appointment/search', 'Api\v1\AppointmentController@appointmentSearch');
    Route::get('appointment/summary', 'Api\v1\TimelineController@appointmentTotal');
    Route::get('appointment/{id}/today', 'Api\v1\AppointmentController@todayAppointment');
    Route::get('appointment/today', 'Api\v1\AppointmentController@todayAppointment');
    Route::get('appointment/{id?}', 'Api\v1\AppointmentController@appointmentList');
    Route::get('appointments/{id}', 'Api\v1\AppointmentController@appointmentListNew');
    Route::post('appointment/{id?}', 'Api\v1\AppointmentController@addAppointment');
    Route::delete('appointment/{id}', 'Api\v1\AppointmentController@deleteAppointment');
    Route::get('appointmentCount', 'Api\v1\ClinicalDashboardController@appointmentCount');

    Route::put('appointment/{id}/statusUpdate', 'Api\v1\AppointmentController@appointmentStatusUpdate');


    // Communication Routes
    Route::get('conversation/exists', 'Api\v1\ConversationController@conversationExists');
    Route::get('communication/messages/{id}', 'Api\v1\CommunicationController@getCommunicationMessages');
    Route::get('communication/{id}/call', 'Api\v1\CommunicationController@getCommunicationCalls');
    Route::get('communication/count', 'Api\v1\CommunicationController@countCommunication');
    Route::get('communication/search', 'Api\v1\CommunicationController@searchCommunication');
    Route::get('communication/type', 'Api\v1\CommunicationController@messageType');
    Route::post('communication', 'Api\v1\CommunicationController@addCommunication');
    Route::get('communication', 'Api\v1\CommunicationController@getCommunication');
    Route::post('communication/{id}/reply', 'Api\v1\CommunicationController@communicationReply');


    // Global Codes Routes
    Route::get('globalCodeCategory/{id?}', 'Api\v1\GlobalCodeController@globalCodeCategory');
    Route::get('globalCode/{id?}', 'Api\v1\GlobalCodeController@globalCode');
    Route::post('globalCode', 'Api\v1\GlobalCodeController@createGlobalCode');
    Route::patch('globalCode/{id?}', 'Api\v1\GlobalCodeController@updateGlobalCode');
    Route::delete('globalCode/{id?}', 'Api\v1\GlobalCodeController@deleteGlobalCode');

    // Task Routes
    Route::post('task', 'Api\v1\TaskController@addTask');
    Route::get('task', 'Api\v1\TaskController@listTask');
    Route::get('taskCount', 'Api\v1\ClinicalDashboardController@TaskCount');
    Route::get('task/priority', 'Api\v1\TaskController@priorityTask');
    Route::get('task/status', 'Api\v1\TaskController@statusTask');
    Route::get('task/staff', 'Api\v1\TaskController@taskPerStaff');
    Route::get('task/category', 'Api\v1\TaskController@taskPerCategory');
    Route::put('task/{id}', 'Api\v1\TaskController@updateTask');
    Route::delete('task/{id}', 'Api\v1\TaskController@deleteTask');
    Route::get('task/{id}', 'Api\v1\TaskController@taskById');
    Route::get('task/status/summery', 'Api\v1\TaskController@taskTotalWithTimeDuration');
    Route::get('task/completion/rates', 'Api\v1\TaskController@taskCompletedRates');

    Route::get('patient/{id}/taskAssigned', 'Api\v1\TaskController@taskAssigneList');

    // Inventory Routes
    Route::post('inventory/{id}', 'Api\v1\InventoryController@store');
    Route::get('inventory', 'Api\v1\InventoryController@index');
    Route::get('inventory/{id}', 'Api\v1\InventoryController@index');
    Route::put('inventory/{id}', 'Api\v1\InventoryController@update');
    Route::delete('inventory/{id}', 'Api\v1\InventoryController@destroy');
    Route::get('model', 'Api\v1\InventoryController@getModels');

    //Family Member
    Route::get('familyMember/patient/{id?}', 'Api\v1\FamilyMemberController@listPatient');

    //Push Notification

    Route::get('notification', 'Api\v1\PushNotificationController@notificationShow');
    Route::get('unreadNotification', 'Api\v1\PushNotificationController@showUnreadNotification');

    // notification isread update
    Route::put('notification/isRead/{id?}', 'Api\v1\NotificationController@updateIsRead');

    //  notification isread list
    Route::get('notification/isReadList', 'Api\v1\NotificationController@listIsRead');


    // Conversation Routes
    Route::get('conversation/list/{id?}', 'Api\v1\ConversationController@allConversation');
    Route::get('conversation/{id?}', 'Api\v1\ConversationController@conversation');
    Route::get('conversation/detail/{id}', 'Api\v1\ConversationController@conversationDetail');
    Route::post('send-message/{id?}', 'Api\v1\ConversationController@conversationMessage');
    Route::get('get-conversation/{id?}', 'Api\v1\ConversationController@showConversation');
    Route::get('latest-message/{id?}', 'Api\v1\ConversationController@latestMessage');


    //Contact Us Routes
    Route::post('requestCall', 'Api\v1\ContactController@index');
    Route::post('contactText', 'Api\v1\ContactController@contactMessage');
    Route::post('contactMail', 'Api\v1\ContactController@contactEmail');
    Route::get('requestCall', 'Api\v1\ContactController@requestContactList');
    Route::put('requestCall/{patientId}/{id}', 'Api\v1\ContactController@requestcallUpdate');

    // General Parameter Routes
    Route::post('generalParameterGroup', 'Api\v1\GeneralParameterController@addGeneralParameterGroup');
    Route::get('generalParameterGroup/{id?}', 'Api\v1\GeneralParameterController@listGeneralParameterGroup');
    Route::get('generalParameter/{id}', 'Api\v1\GeneralParameterController@listGeneralParameter');
    Route::put('generalParameterGroup/{id}', 'Api\v1\GeneralParameterController@addGeneralParameterGroup');
    Route::delete('generalParameterGroup/{id}', 'Api\v1\GeneralParameterController@deleteGeneralParameterGroup');
    Route::delete('generalParameter/{id}', 'Api\v1\GeneralParameterController@deleteGeneralParameter');

    // Note Routes
    Route::post('{entity}/{id}/notes', 'Api\v1\NoteController@addNote');
    Route::get('{entity}/{id}/notes/{noteId?}', 'Api\v1\NoteController@listNote');
    Route::get('{entity}/notes/{noteId?}', 'Api\v1\NoteController@listNote');


    // Document Routes

    Route::post('{entity}/{id}/document', 'Api\v1\DocumentController@createDocument');
    Route::put('{entity}/{id}/document/{documentId}', 'Api\v1\DocumentController@createDocument');
    Route::get('{entity}/{id}/document/{documentId?}', 'Api\v1\DocumentController@listDocument');
    Route::delete('{entity}/{id}/document/{documentId}', 'Api\v1\DocumentController@deleteDocument');

    // Provider Routes
    Route::post('provider', 'Api\v1\ProviderController@store');
    Route::get('provider/{id?}', 'Api\v1\ProviderController@index');
    Route::put('provider/{id}', 'Api\v1\ProviderController@updateProvider');
    Route::delete('provider/{id}', 'Api\v1\ProviderController@deleteProviderLocation');
    Route::post('provider/{id}/location', 'Api\v1\ProviderController@providerLocationStore');

    // Provider Contact Routes
    Route::post('provider/{id}/contact', 'Api\v1\ProviderController@addProviderContact');
    Route::get('provider/{id}/contact/{contactId?}', 'Api\v1\ProviderController@listProviderContact');
    Route::put('provider/{id}/contact/{contactId}', 'Api\v1\ProviderController@updateProviderContact');

    // Provider Location Routes
    Route::get('provider/{id}/location/{locationId?}', 'Api\v1\ProviderController@listLocation');
    Route::put('provider/{id}/location/{locationId}', 'Api\v1\ProviderController@updateLocation');
    Route::delete('provider/{id}/location/{locationId}', 'Api\v1\ProviderController@deleteProviderLocation');

    // Provider Location Program
    Route::delete('provider/{id}/location/{locationId}/program/{programId}', 'Api\v1\ProviderController@deleteProviderLocationProgram');
    Route::post('provider/{id}/location/{locationId}/program', 'Api\v1\ProviderController@addProviderLocationProgram');
    Route::get('provider/{id}/location/{locationId}/program', 'Api\v1\ProviderController@listProviderLocationProgram');

    // Provider Location Sublocation
    Route::post('provider/{id}/location/{locationId}/subLocation', 'Api\v1\ProviderController@addProviderLocationSubLocation');
    Route::delete('provider/{id}/location/{locationId}/subLocation/{subLocationId}', 'Api\v1\ProviderController@deleteProviderLocationSubLocation');

    // Provider total location update
    Route::put('provider/{id}/locationUpdate', 'Api\v1\ProviderController@locationUpdate');


    // role Permission routes
    Route::post('role', 'Api\v1\RolePermissionController@createRole');
    Route::get('roleList/{id?}', 'Api\v1\RolePermissionController@roleList');
    Route::get('role/{id}', 'Api\v1\RolePermissionController@listingRole');
    Route::put('role/{id}', 'Api\v1\RolePermissionController@updateRole');
    Route::delete('role/{id}', 'Api\v1\RolePermissionController@deleteRole');
    Route::post('rolePermission/{id}', 'Api\v1\RolePermissionController@createRolePermission');
    Route::get('permissionList', 'Api\v1\RolePermissionController@permissionsList');
    Route::get('rolePermission/{id}', 'Api\v1\RolePermissionController@rolePermissionList');
    Route::get('rolePermissionEdit/{id}', 'Api\v1\RolePermissionController@rolePermissionEdit');

    //cpt code
    Route::get('financialStats', 'Api\v1\BusinessDashboardController@financialStats');
    Route::get('cptCode/billingSummary', 'Api\v1\BusinessDashboardController@billingSummary');
    Route::get('cptCode', 'Api\v1\CPTCodeController@listCPTCode');
    Route::get('cptCode/{id}', 'Api\v1\CPTCodeController@listCPTCode');
    Route::put('cptCode/status/{id}', 'Api\v1\CPTCodeController@updateCPTCodeStatus');
    Route::post('cptCode', 'Api\v1\CPTCodeController@createCPTCode');
    Route::put('cptCode/{id}', 'Api\v1\CPTCodeController@updateCPTCode');
    Route::delete('cptCode/{id}', 'Api\v1\CPTCodeController@deleteCPTCode');

    //cpt Code Router
    Route::get('cptCodes', 'Api\v1\CPTCodeController@cptCodeList');
    Route::get('cptCodes/{id}', 'Api\v1\CPTCodeController@cptCodeListDetail');
    Route::get('cptCodes/billing/{id}', 'Api\v1\CPTCodeController@getNextBillingDetail');
    Route::put('cptCodes/{id?}', 'Api\v1\CPTCodeController@cptCodeStatusUpdate');

    Route::get('cptCodeActivities', 'Api\v1\CPTCodeController@cptCodeActivity');

    // user Router
    Route::get('user/{id}', 'Api\v1\UserController@listUser');

    // patient appointment update
    Route::patch('patient/appointment/{id}', 'Api\v1\AppointmentController@updateAppointment');

    //widgets Access
    Route::get('widgetAccess/{id}', 'Api\v1\WidgetController@listWidgetAccess');
    Route::post('widgetAccess/{id}', 'Api\v1\WidgetController@createWidgetAccess');
    Route::delete('widgetAccess/{id}', 'Api\v1\WidgetController@deleteWidgetAccess');


    // change password
    Route::post('changePassword', 'Api\v1\UserController@changePassword');

    // Change Audit Log API
    Route::get('changeAuditLog/{id}', 'Api\v1\TimeLogController@changeAuditLog');

    // delete new patient flad
    Route::delete('newPatientFlag', 'Api\v1\NotificationController@newPatientFlag');

    // Flag Route
    Route::get('flag', 'Api\v1\FlagController@listFlag');

    // update firstLogin
    Route::put('user/firstLogin', 'Api\v1\UserController@firstLogin');
    // search
    Route::get('search', 'Api\v1\PatientStaffController@search');
    // Admin Details Routes
    Route::get('admin/details', 'Api\v1\AdminDetailsController@adminDetails');
    //Patient screen Action Route
    Route::post('patient/{id}/action', 'Api\v1\ScreenActionController@createPatientScreenAction');
    //Patient timeLineType Route
    Route::get('timeLineType', 'Api\v1\PatientController@listPatientTimeLineType');
    Route::post('guest', 'Api\v1\GuestController@guest');
    Route::get('escalationCount', 'Api\v1\ClinicalDashboardController@escalationCount');
    Route::post('escalation', 'Api\v1\EscalationController@addEscalation');
    Route::put('escalation/{id}', 'Api\v1\EscalationController@updateEscalation');
    Route::delete('escalation/{id}', 'Api\v1\EscalationController@deleteEscalation');
    Route::get('escalation/{id?}', 'Api\v1\EscalationController@listEscalation');
    Route::post('escalation/{id}/action', 'Api\v1\EscalationController@escalationActionAdd');

    // Route::get('escalation/{ecalationTypeId}/{id}/', 'Api\v1\EscalationController@getEscalationByType');
    Route::get('escalation/{id}/resend', 'Api\v1\EscalationController@resendEscalation');
    Route::get('escalation/{id}/audit', 'Api\v1\EscalationController@auditEscalation');
    Route::post('escalation/{id}/assign', 'Api\v1\EscalationController@addEscalationAssign');
    Route::post('escalation/{id}/action', 'Api\v1\EscalationController@addEscalationAction');
    Route::get('escalation/{id}/action/{actionId?}', 'Api\v1\EscalationController@listEscalationAction');
    Route::get('escalation/{id}/assign/{assignId?}', 'Api\v1\EscalationController@listEscalationAssign');
    Route::post('escalation/{id}/detail', 'Api\v1\EscalationController@addEscalationDetail');
    Route::post('escalation/{id}/email', 'Api\v1\EscalationController@addEscalationEmail');
    Route::post('escalation/{id}/notification', 'Api\v1\EscalationController@addEscalationNotification');
    Route::get('escalationList/{id}', 'Api\v1\EscalationController@escalationList');
    Route::post('escalation/{id}/close', 'Api\v1\EscalationController@addEscalationActionClose');
    Route::get('escalationAudit/{id?}', 'Api\v1\EscalationController@escalationAuditList');
    Route::post('escalationAudit/{id}/comment', 'Api\v1\EscalationController@addEscalationAuditDescription');

    //Bug Report Route
    Route::get('bug/{bugReportId?}', 'Api\v1\BugReportController@bugReportList');
    Route::post('bug', 'Api\v1\BugReportController@createBugReport');
    Route::delete('bug/{bugReportId}', 'Api\v1\BugReportController@deleteBugReport');
    Route::get('bugScreen', 'Api\v1\BugReportController@screenList');

    //Patient Task Route
    Route::get('patient/{id}/tasks/{patientTaskId?}', 'Api\v1\PatientTaskController@patientTaskList');
    Route::post('patient/{id}/tasks', 'Api\v1\PatientTaskController@createPatientTask');
    Route::put('patient/{id}/tasks/{patientTaskId}', 'Api\v1\PatientTaskController@patientTaskUpdate');
    Route::delete('patient/{id}/tasks/{patientTaskId}', 'Api\v1\PatientTaskController@deletePatientTask');

    // patient group Route
    Route::post('patient/{id}/group', 'Api\v1\PatientController@addPatientGroup');
    Route::get('patient/{id}/group', 'Api\v1\PatientController@listPatientGroup');


    // Condition List
    Route::get('condition/{id?}', 'Api\v1\ConditionController@listCondition');

    //user Setting router
    Route::post('users/setting', 'Api\v1\UserController@userSetting');
    Route::get('users/setting', 'Api\v1\UserController@userSettingList');
    Route::post('timeApproval', 'Api\v1\TimeApprovalController@addTimeApproval');
    Route::get('timeApproval/{id?}', 'Api\v1\TimeApprovalController@listTimeApproval');
    Route::put('timeApproval/{id}', 'Api\v1\TimeApprovalController@updateTimeApproval');
    Route::put('timeApproval', 'Api\v1\TimeApprovalController@updateTimeApprovalMultiple');


    // s3 image download


    //workflow Endpoints
    Route::post('workflow', 'Api\v1\WorkflowController@add');
    Route::put('workflow/{id}', 'Api\v1\WorkflowController@update');
    Route::get('workflow/event/{id?}', 'Api\v1\WorkflowController@event');
    Route::get('workflow/event/{eventId}/column', 'Api\v1\WorkflowController@column');
    Route::get('workflow/event/{eventId}/action', 'Api\v1\WorkflowController@action');
    Route::get('workflow/{id?}', 'Api\v1\WorkflowController@list');

    Route::put('workflow/{id}/criteria', 'Api\v1\WorkflowController@addCriteria');
    Route::get('workflow/{id}/criteria', 'Api\v1\WorkflowController@getCriteria');
    Route::post('workflow/{id}/step', 'Api\v1\WorkflowController@addStep');
    Route::get('workflow/{workFlowId}/step/{id?}', 'Api\v1\WorkflowController@getStep');
    Route::put('workflow/{workFlowId}/step/{id}', 'Api\v1\WorkflowController@updateStep');
    Route::delete('workflow/{workFlowId}/step/{id}', 'Api\v1\WorkflowController@deleteStep');
    Route::get('workflow/{workFlowId}/offset/{id?}', 'Api\v1\WorkflowController@offset');
    Route::get('workflow/{workFlowId}/alert/{id}/field', 'Api\v1\WorkflowController@alertField');
    Route::post('workflow/{workFlowId}/action', 'Api\v1\WorkflowController@addStepAction');
    Route::get('workflow/{workFlowId}/action/{id?}', 'Api\v1\WorkflowController@getStepAction');
    Route::put('workflow/{workFlowId}/action/{id?}', 'Api\v1\WorkflowController@updateStepAction');
    Route::delete('workflow/{workFlowId}/action/{id}', 'Api\v1\WorkflowController@deleteStepAction');

    // Group Route
    Route::get('group/{id?}', 'Api\v1\GroupController@groupList');
    Route::post('group', 'Api\v1\GroupController@createGroup');
    Route::put('group/{id}', 'Api\v1\GroupController@updateGroup');
    Route::delete('group/{id}', 'Api\v1\GroupController@deleteGroup');

    //Staff Group
    Route::get('group/{id}/staff/{staffGroupId?}', 'Api\v1\GroupController@staffGroupList');
    Route::post('group/{id}/staff', 'Api\v1\GroupController@createStaffGroup');
    Route::delete('group/{id}/staff/{staffGroupId}', 'Api\v1\GroupController@deleteStaffGroup');

    //Group Program
    Route::get('group/{id}/program', 'Api\v1\GroupController@groupProgramList');
    Route::post('group/{id}/program', 'Api\v1\GroupController@creategroupProgram');
    Route::delete('group/{id}/program/{groupProgramId}', 'Api\v1\GroupController@deleteGroupProgram');

    // Program Provider
    Route::get('program/{id}/provider', 'Api\v1\GroupController@programProviderList');


    //Group Provider
    Route::get('group/{id}/provider', 'Api\v1\GroupController@groupProviderList');
    Route::post('group/{id}/provider', 'Api\v1\GroupController@createGroupProvider');
    Route::delete('group/{id}/provider/{groupProviderId}', 'Api\v1\GroupController@deleteGroupProvider');

    // Group Widget
    Route::post('group/{id}/widget', 'Api\v1\GroupController@addGroupWidget');
    Route::get('group/{id}/widget/{widgetId?}', 'Api\v1\GroupController@listGroupWidget');

    //Group Permission
    Route::get('group/{id}/permission', 'Api\v1\GroupController@groupPermissionList');
    Route::post('group/{id}/permission', 'Api\v1\GroupController@createGroupPermission');
    Route::get('group/{id}/action', 'Api\v1\AccessRoleController@assignedRoleActionGroup');

    // Merge Permission
    Route::get('actionPermission', 'Api\v1\AccessRoleController@mergePermission');


    // Group Composition
    Route::post('group/{id}/composition', 'Api\v1\GroupController@addGroupComposition');
    Route::get('group/{id}/composition/{compostionId?}', 'Api\v1\GroupController@listGroupComposition');

    // Questionnaire Routes
    Route::post('questionnaireTemplate', 'Api\v1\QuestionnaireController@addQuestionnaire');
    Route::put('questionnaireTemplate/{id?}', 'Api\v1\QuestionnaireController@updateQuestionnaire');
    Route::delete('questionnaireTemplate/{id?}', 'Api\v1\QuestionnaireController@deleteQuestionnaire');
    Route::get('questionnaireTemplate/{id?}', 'Api\v1\QuestionnaireController@listQuestionnaire');
    Route::post('questionnaireTemplate/{id}/question', 'Api\v1\QuestionnaireController@assignQuestion');
    Route::put('questionnaireTemplate/{id}/question', 'Api\v1\QuestionnaireController@updateAssignQuestion');
    Route::get('questionnaireTemplate/{id}/question', 'Api\v1\QuestionnaireController@listAssignQuestion');
    Route::get('import/nested/question', 'Api\v1\QuestionnaireController@getSectionBasedQuestion');

    // question
    Route::post('question/{id?}', 'Api\v1\QuestionnaireController@addQuestion');
    Route::get('question/{id?}', 'Api\v1\QuestionnaireController@listQuestion');
    Route::put('question/{id?}', 'Api\v1\QuestionnaireController@updateQuestion');
    Route::delete('question/{id}', 'Api\v1\QuestionnaireController@deleteQuestion');

    // question option
    Route::post('question/options/{id}', 'Api\v1\QuestionnaireController@addQuestionOption');
    Route::put('question/options/{id}', 'Api\v1\QuestionnaireController@updateQuestionOption');
    Route::delete('question/options/{id}', 'Api\v1\QuestionnaireController@deleteQuestionOption');
    Route::post('question/option/assign', 'Api\v1\QuestionnaireController@assignOptionQuestion');
    Route::get('delete/nested/question/{id}', 'Api\v1\QuestionnaireController@deleteNestedQuestion');

    // assign questionnaire template
    Route::get('assign/questionnaire/template/{id?}', 'Api\v1\ClientQuestionnaireController@getAssignQuestionnaireTemplate');
    Route::post('assign/questionnaire/template', 'Api\v1\ClientQuestionnaireController@assignQuestionnaireTemplate');

    Route::get('assign/user/{id?}', 'Api\v1\QuestionnaireController@getAssignedTemplateUserList');

    // fillup questionnaire form
    Route::get('fillup/questionnaire/{id}', 'Api\v1\ClientQuestionnaireController@getfillUpQuestionnaire');
    Route::get('fillup/questionnaire/form/{id}', 'Api\v1\ClientQuestionnaireController@getfillUpQuestionnaireForApp');
    Route::post('questionnaireTemplate/{id}/client', 'Api\v1\ClientQuestionnaireController@AddQuestionnaireTemplateByUsers');
    Route::get('next/question/{id}', 'Api\v1\ClientQuestionnaireController@getNextQuestion');

    // client questinniare template
    Route::get('user/questionnaire/template/{id?}', 'Api\v1\ClientQuestionnaireController@questionnaireTemplateByUser');
    Route::get('questionnaire/response/score/{id?}', 'Api\v1\ClientQuestionnaireController@getQuestionnaireScore');

    // Questionnaire Section
    Route::post('questionnaireSection', 'Api\v1\QuestionnaireSectionController@addQuestionnaireSection');
    Route::put('questionnaireSection/{id?}', 'Api\v1\QuestionnaireSectionController@updateQuestionnaireSection');
    Route::delete('questionnaireSection/{id?}', 'Api\v1\QuestionnaireSectionController@deleteQuestionnaireSection');
    Route::get('questionnaireSection/{id?}', 'Api\v1\QuestionnaireSectionController@listQuestionnaireSection');
    Route::delete('question/section/{id}', 'Api\v1\QuestionnaireSectionController@deleteQuestionInSection');

    //Assign Questionnaire Section
    Route::post('questions/{id}/assign', 'Api\v1\QuestionnaireSectionController@assignQuestionSection');
    Route::put('questions/{id}/assign', 'Api\v1\QuestionnaireSectionController@updateAssignQuestionSection');
    Route::post('questionnaireSection/{id}/assign', 'Api\v1\QuestionnaireSectionController@assignQuestionnaireSection');
    Route::put('questionnaireSection/{id}/assign', 'Api\v1\QuestionnaireSectionController@updateAssignQuestionnaireSection');

    // Questionnaire Data Type
    Route::get('questionnaire/dataType/{id?}', 'Api\v1\QuestionnaireController@getQuestionnaireDataType');
    Route::get('questionnaire/score/type/{id?}', 'Api\v1\QuestionnaireController@getQuestionnaireScoreType');
    Route::get('questionnaire/globalCode/list/{id?}', 'Api\v1\QuestionnaireController@getQuestionnaireGlobalCode');
    //tag
    Route::get('tag/{id?}', 'Api\v1\TagController@listTag');

    // get Questionniare CustomField
    Route::get('questionnaire/custom/field/{id?}', 'Api\v1\QuestionnaireController@getQuestionnaireCustomField');

    // test questionnaire template
    Route::get('templateQuestionnaire/{id?}', 'Api\v1\QuestionnaireController@getTemplateQuestionnaire');

    // email log
    Route::get('message/logs/{id?}', 'Api\v1\DashboardController@getEmailLogs');

    Route::get('patientProgramReminder', 'Api\v1\NotificationController@patientProgramReminder');

    Route::get('messageReminder', 'Api\v1\NotificationController@messageReadReminder');


    /** Email Templates Route : Sanjiv */

    Route::get('get-templates', 'Api\v1\ConfigMsgController@gettemplates');
    Route::get('get-templates/{id}', 'Api\v1\ConfigMsgController@gettemplateDetail');
    Route::post('update-template', 'Api\v1\ConfigMsgController@update_template');
    Route::post('create-communication-template', 'Api\v1\ConfigMsgController@create_communication_template');

    //Dashboad Widgets

    Route::post('dashboardWidget/{id?}', 'Api\v1\WidgetController@addDashboardWidget');
    Route::get('dashboardWidget', 'Api\v1\WidgetController@dashboardWidgetList');

    // Tool Tip
    Route::get('toolTip', 'Api\v1\ToolTipController@tooltipListing');
    Route::get('form', 'Api\v1\ToolTipController@formList');
    Route::post('toolTip/{id}', 'Api\v1\ToolTipController@addToolTip');
    Route::put('toolTip/{id}', 'Api\v1\ToolTipController@updateToolTip');


    //template
    Route::get('template', 'Api\v1\TemplateController@listTemplate');
    Route::post('template', 'Api\v1\TemplateController@createTemplate');
    Route::put('template/{id}', 'Api\v1\TemplateController@updateTemplate');
    Route::delete('template/{id}', 'Api\v1\TemplateController@deleteTemplate');
    Route::get('downloadFile', 'Api\v1\FileController@download');

    Route::get('call/status', 'Api\v1\BusinessDashboardController@callStatus');

    Route::post('file', 'Api\v1\FileController@createFile');
    Route::delete('file', 'Api\v1\FileController@deleteFile');
    Route::get('call/staff', 'Api\v1\CommunicationController@callCountPerStaff');
    Route::get('count/patient', 'Api\v1\DashboardController@patientCountMonthly');
    Route::get('count/appointment', 'Api\v1\DashboardController@appointmentCountMonthly');
    Route::put('profile', 'Api\v1\UserController@profile');

    Route::get('inQueue', 'Api\v1\CommunicationController@inQueue');
    Route::get('goingOn', 'Api\v1\CommunicationController@goingOn');
    Route::get('completed', 'Api\v1\CommunicationController@completed');
    Route::get('staffCallCount', 'Api\v1\CommunicationController@callCountPerStaff');
    Route::get('field/{id?}', 'Api\v1\VitalController@listVitalTypeField');


    Route::put('staff/{staffId}/contact/{id}', 'Api\v1\StaffController@updateStaffContact');
    Route::delete('staff/{staffId}/contact/{id}', 'Api\v1\StaffController@deleteStaffContact');

    Route::put('staff/{staffId}/availability/{id}', 'Api\v1\StaffController@updateStaffAvailability');
    Route::delete('staff/{staffId}/availability/{id}', 'Api\v1\StaffController@deleteStaffAvailability');


// Staff Location
    Route::post('staff/{id}/location', 'Api\v1\StaffController@addStaffLocation');
    Route::get('staff/{id}/location/{locationId?}', 'Api\v1\StaffController@listStaffLocation');
    Route::delete('staff/{id}/location/{locationId}', 'Api\v1\StaffController@deleteStaffLocation');
// Staff Program
    Route::post('staff/{id}/program', 'Api\v1\StaffController@addStaffProgram');
    Route::get('staff/{id}/program/{programId?}', 'Api\v1\StaffController@listStaffProgram');
    Route::delete('staff/{id}/program/{programId}', 'Api\v1\StaffController@deleteStaffProgram');
    Route::get('role', 'Api\v1\AccessRoleController@index');
    Route::get('staff/{id}/access', 'Api\v1\AccessRoleController@assignedRoles');
    Route::post('inventory', 'Api\v1\InventoryController@store');

    /** Email Templates Route ends*/


    //Staff Routes
    Route::put('staff/{staffId}/contacts/{id}', 'Api\v1\StaffController@updateStaffContact');
    Route::post('staff/{id}/contacts', 'Api\v1\StaffController@addStaffContact');
    Route::get('staff/{id}/contacts/{staffContactId?}', 'Api\v1\StaffController@listStaffContact');
    Route::post('staff/{id}/availability', 'Api\v1\StaffController@addStaffAvailability');
    Route::get('staff/{id}/availability/{staffAvailabilityId?}', 'Api\v1\StaffController@listStaffAvailability');
    Route::post('staff/{id}/role', 'Api\v1\StaffController@addStaffRole');
    Route::get('staff/{id}/role', 'Api\v1\StaffController@listStaffRole');
    Route::put('staff/{staffId}/role/{id}', 'Api\v1\StaffController@updateStaffRole');
    Route::delete('staff/{staffId}/role/{id}', 'Api\v1\StaffController@deleteStaffRole');
    Route::post('staff/{id}/provider', 'Api\v1\StaffController@addStaffProvider');
    Route::get('staff/{id}/provider', 'Api\v1\StaffController@listStaffProvider');
    Route::put('staff/{staffId}/provider/{id}', 'Api\v1\StaffController@updateStaffProvider');
    Route::delete('staff/{staffId}/provider/{id}', 'Api\v1\StaffController@deleteStaffProvider');

//    Route::put('staff/{id}/resetPassword', 'Api\v1\StaffController@resetStaffPassword'); //Need to enable later

    //Screen action Routes
    Route::post('screenAction', 'Api\v1\ScreenActionController@creatScreenAction');
    Route::get('getScreenAction', 'Api\v1\ScreenActionController@getScreenAction');

    //Module Routes
    Route::post('module', 'Api\v1\ModuleController@createModule');
    Route::get('module', 'Api\v1\ModuleController@getModule');
    Route::post('screen', 'Api\v1\ScreenController@createScreen');
    Route::get('screen', 'Api\v1\ScreenController@getScreen');

    //program
    Route::post('program', 'Api\v1\ProgramController@createProgram');
    Route::get('program/{id?}', 'Api\v1\ProgramController@listProgram');
    Route::put('program/{id}', 'Api\v1\ProgramController@updateProgram');
    Route::delete('program/{id}', 'Api\v1\ProgramController@deleteProgram');


    //patient flags
    Route::post('flags', 'Api\v1\PatientController@addPatientFlags');
    // Route::get('nonCompliancePatient', 'Api\v1\NotificationController@nonCompliance');
    // Route::get('patient/{id}/nonCompliance', 'Api\v1\NonComplianceController@nonCompliance');

    Route::post('getSMS', 'Api\v1\UserController@getSMS');
    Route::post('sentSMS', 'Api\v1\UserController@sentSMS');

    //Communication Inbound
    Route::get('communicationInbound/{id?}', 'Api\v1\CommunicationController@getCommunicationInbound');
    Route::delete('communicationInbound/{id}', 'Api\v1\CommunicationController@deleteCommunicationInbound');
    Route::put('communicationInbound/{id}', 'Api\v1\CommunicationController@updateCommunicationInbound');
    Route::get('getMail', 'Api\v1\UserController@getMail');

});
Route::put('staff/{id}/resetPassword', 'Api\v1\StaffController@resetStaffPassword'); //Need to create new
// Route::get('termsAndConditions', 'Api\v1\TermsConditionsController');

//escalation email verification
Route::post('escalation/{id}/verify', 'Api\v1\EscalationController@verifyEscalation');

// Route::get('termsAndConditions', 'Api\v1\TermsConditionsController');

// Route::get('freeSwitch', 'Freeswitch\DirectoryController@directory');


//timezone
Route::get('timezone', 'Api\v1\DashboardController@getTimezone');

Route::post('call', 'Api\v1\CommunicationController@addCallRecord');
Route::get('widget', 'Api\v1\WidgetController@getWidget');
Route::put('widget/{id}', 'Api\v1\WidgetController@updateWidget');
Route::get('widget/assign', 'Api\v1\WidgetController@getassignedWidget');


Route::post('callRecord', 'Api\v1\CommunicationController@addCallRecord');


Route::get('inventory', 'Api\v1\InventoryController@index');
Route::put('inventory/{id}', 'Api\v1\InventoryController@update');
Route::delete('inventory/{id}', 'Api\v1\InventoryController@destroy');
Route::get('model', 'Api\v1\InventoryController@getModels');

Route::get('staff/specialization/count', 'Api\v1\StaffController@specializationCount');
Route::get('staff/network/count', 'Api\v1\StaffController@networkCount');


//service
Route::get('service', 'Api\v1\ServiceNameController@listService');
Route::post('service', 'Api\v1\ServiceNameController@createService');

// FAQ Routes
// Route::get('faq', 'Api\v1\FaqController');

// Terms&Conditions Routes

//freeswitch
// Route::get('freeswitch/directory', 'Freeswitch\DirectoryController@directory');
// Route::get('freeswitch/dialplan', 'Freeswitch\DirectoryController@dialplan');
