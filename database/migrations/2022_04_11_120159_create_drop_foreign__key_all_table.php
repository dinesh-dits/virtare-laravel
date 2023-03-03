<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDropForeignKeyAllTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accessRoles', function (Blueprint $table) {
           $table->dropForeign('accessRoles_roleTypeId_foreign');
        });
        Schema::table('actions', function (Blueprint $table) {
            $table->dropForeign('actions_screenId_foreign');
         });
        Schema::table('appointmentNotification', function (Blueprint $table) {
            $table->dropForeign('appointmentNotification_appointmentId_foreign');
        });
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign('appointments_patientId_foreign');
            $table->dropForeign('appointments_staffId_foreign');
            $table->dropForeign('appointments_appointmentTypeId_foreign');
            $table->dropForeign('appointments_durationId_foreign');
            $table->dropForeign('appointments_statusId_foreign');
        });
        Schema::table('callRecords', function (Blueprint $table) {
            $table->dropForeign('callRecords_communicationCallRecordId_foreign');
            $table->dropForeign('callRecords_staffId_foreign');
        });
        Schema::table('carePlans', function (Blueprint $table) {
            $table->dropForeign('carePlans_patientId_foreign');
            $table->dropForeign('carePlans_staffId_foreign');
        });
        Schema::table('communicationCallRecords', function (Blueprint $table) {
            $table->dropForeign('communicationcallrecords_statusid_foreign');
            $table->dropForeign('communicationCallRecords_patientId_foreign');
        });
        Schema::table('communicationMessages', function (Blueprint $table) {
            $table->dropForeign('communicationmessages_communicationid_foreign');
        });
        Schema::table('communications', function (Blueprint $table) {
            $table->dropForeign('communications_messagecategoryid_foreign');
            $table->dropForeign('communications_messagetypeid_foreign');
            $table->dropForeign('communications_priorityid_foreign');
        });
        Schema::table('contactEmails', function (Blueprint $table) {
            $table->dropForeign('contactemails_userid_foreign');
        });
        Schema::table('contactTexts', function (Blueprint $table) {
            $table->dropForeign('contacttexts_messagestatusid_foreign');
            $table->dropForeign('contacttexts_userid_foreign');
        });
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign('conversations_receiverid_foreign');
            $table->dropForeign('conversations_senderid_foreign');
        });
        Schema::table('cptCodes', function (Blueprint $table) {
            $table->dropForeign('cptcodes_durationid_foreign');
            $table->dropForeign('cptcodes_serviceid_foreign');
        });
        Schema::table('dashboardWidgetByRoles', function (Blueprint $table) {
            $table->dropForeign('dashboardwidgetbyroles_roleid_foreign');
            $table->dropForeign('dashboardwidgetbyroles_widgetid_foreign');
        });
        Schema::table('deviceModels', function (Blueprint $table) {
            $table->dropForeign('devicemodels_devicetypeid_foreign');
        });
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign('documents_documenttypeid_foreign');
            $table->dropForeign('documents_referanceid_foreign');
        });
        Schema::table('durationIntervals', function (Blueprint $table) {
            $table->dropForeign('durationintervals_durationid_foreign');
        });
        Schema::table('errorLog', function (Blueprint $table) {
            $table->dropForeign('errorlog_userid_foreign');
        });
        Schema::table('generalParameters', function (Blueprint $table) {
            $table->dropForeign('generalparameters_generalparametergroupid_foreign');
        });
        Schema::table('globalCodes', function (Blueprint $table) {
            $table->dropForeign('globalcodes_globalcodecategoryid_foreign');
        });
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropForeign('inventories_devicemodelid_foreign');
        });
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign('messages_senderid_foreign');
        });
        Schema::table('notes', function (Blueprint $table) {
            $table->dropForeign('notes_categoryid_foreign');
            $table->dropForeign('notes_referenceid_foreign');
            $table->dropForeign('notes_type_foreign');
        });
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign('notifications_userid_foreign');
        });
        Schema::table('patentMedicineRoutines', function (Blueprint $table) {
            $table->dropForeign('patentmedicineroutines_patientid_foreign');
        });
        Schema::table('patientActions', function (Blueprint $table) {
            $table->dropForeign('patientactions_actionid_foreign');
            $table->dropForeign('patientactions_patientid_foreign');
            $table->dropForeign('patientactions_userid_foreign');
        });
        Schema::table('patientConditions', function (Blueprint $table) {
            $table->dropForeign('patientconditions_conditionid_foreign');
            $table->dropForeign('patientconditions_patientid_foreign');
        });
        Schema::table('patientCriticalNotes', function (Blueprint $table) {
            $table->dropForeign('patientcriticalnotes_patientid_foreign');
        });
        Schema::table('patientDevices', function (Blueprint $table) {
            $table->dropForeign('patientdevices_otherdeviceid_foreign');
            $table->dropForeign('patientdevices_patientid_foreign');
        });
        Schema::table('patientEmergencyContacts', function (Blueprint $table) {
            $table->dropForeign('patientemergencycontacts_genderid_foreign');
            $table->dropForeign('patientemergencycontacts_patientid_foreign');
        });
        Schema::table('patientFamilyMembers', function (Blueprint $table) {
            $table->dropForeign('patientfamilymembers_genderid_foreign');
            $table->dropForeign('patientfamilymembers_patientid_foreign');
            $table->dropForeign('patientfamilymembers_relationid_foreign');
            $table->dropForeign('patientfamilymembers_userid_foreign');
        });
        Schema::table('patientFlags', function (Blueprint $table) {
            $table->dropForeign('patientflags_flagid_foreign');
            $table->dropForeign('patientflags_patientid_foreign');
        });
        Schema::table('patientGoals', function (Blueprint $table) {
            $table->dropForeign('patientgoals_devicetypeid_foreign');
            $table->dropForeign('patientgoals_frequencytypeid_foreign');
            $table->dropForeign('patientgoals_patientid_foreign');
            $table->dropForeign('patientgoals_vitalfieldid_foreign');
            $table->dropForeign('patientgoals_vitaltypeid_foreign');
        });
        Schema::table('patientInsurances', function (Blueprint $table) {
            $table->dropForeign('patientinsurances_insurancenameid_foreign');
            $table->dropForeign('patientinsurances_insurancetypeid_foreign');
            $table->dropForeign('patientinsurances_patientid_foreign');
        });
        Schema::table('patientInventories', function (Blueprint $table) {
            $table->dropForeign('patientinventories_inventoryid_foreign');
            $table->dropForeign('patientinventories_patientid_foreign');
        });
        Schema::table('patientMedicalHistories', function (Blueprint $table) {
            $table->dropForeign('patientmedicalhistories_patientid_foreign');
        });
        Schema::table('patientPhysicians', function (Blueprint $table) {
            $table->dropForeign('patientphysicians_designationid_foreign');
            $table->dropForeign('patientphysicians_patientid_foreign');
            $table->dropForeign('patientphysicians_userid_foreign');
        });
        Schema::table('patientPrograms', function (Blueprint $table) {
            $table->dropForeign('patientprograms_patientid_foreign');
            $table->dropForeign('patientprograms_programtid_foreign');
        });
        Schema::table('patientReferals', function (Blueprint $table) {
            $table->dropForeign('patientreferals_designationid_foreign');
            $table->dropForeign('patientreferals_patientid_foreign');
        });
        Schema::table('patients', function (Blueprint $table) {
            $table->dropForeign('patients_countryid_foreign');
            $table->dropForeign('patients_genderid_foreign');
            $table->dropForeign('patients_languageid_foreign');
            $table->dropForeign('patients_stateid_foreign');
            $table->dropForeign('patients_userid_foreign');
        });
        Schema::table('patientTimelines', function (Blueprint $table) {
            $table->dropForeign('patienttimelines_patientid_foreign');
        });
        Schema::table('patientTimeLogs', function (Blueprint $table) {
            $table->dropForeign('patienttimelogs_categoryid_foreign');
            $table->dropForeign('patienttimelogs_loggedid_foreign');
            $table->dropForeign('patienttimelogs_patientid_foreign');
            $table->dropForeign('patienttimelogs_performedid_foreign');
        });
        Schema::table('patientVitals', function (Blueprint $table) {
            $table->dropForeign('patientvitals_patientid_foreign');
            $table->dropForeign('patientvitals_vitalfieldid_foreign');
        });
        Schema::table('programs', function (Blueprint $table) {
            $table->dropForeign('programs_typeid_foreign');
        });
        Schema::table('providerLocations', function (Blueprint $table) {
            $table->dropForeign('providerlocations_stateid_foreign');
        });
        Schema::table('providers', function (Blueprint $table) {
            $table->dropForeign('providers_countryid_foreign');
            $table->dropForeign('providers_stateid_foreign');
        });
        Schema::table('relations', function (Blueprint $table) {
            $table->dropForeign('relations_genderid_foreign');
            $table->dropForeign('relations_relationid_foreign');
            $table->dropForeign('relations_reverserelationid_foreign');
        });
        Schema::table('requestCalls', function (Blueprint $table) {
            $table->dropForeign('requestcalls_messagestatusid_foreign');
            $table->dropForeign('requestcalls_userid_foreign');
        });
        Schema::table('rolePermissions', function (Blueprint $table) {
            $table->dropForeign('rolepermissions_accessroleid_foreign');
            $table->dropForeign('rolepermissions_actionid_foreign');
        });
        Schema::table('screenActions', function (Blueprint $table) {
            $table->dropForeign('screen_actions_actionid_foreign');
            $table->dropForeign('screen_actions_userid_foreign');
        });
        Schema::table('screens', function (Blueprint $table) {
            $table->dropForeign('screens_moduleid_foreign');
        });
        Schema::table('staffAvailabilities', function (Blueprint $table) {
            $table->dropForeign('staffavailabilities_staffid_foreign');
        });
        Schema::table('staffContacts', function (Blueprint $table) {
            $table->dropForeign('staffcontacts_staffid_foreign');
        });
        Schema::table('staffProviders', function (Blueprint $table) {
            $table->dropForeign('staffproviders_staffid_foreign');
        });
        Schema::table('staffs', function (Blueprint $table) {
            $table->dropForeign('staffs_designationid_foreign');
            $table->dropForeign('staffs_genderid_foreign');
            $table->dropForeign('staffs_networkid_foreign');
            $table->dropForeign('staffs_roleid_foreign');
            $table->dropForeign('staffs_specializationid_foreign');
            $table->dropForeign('staffs_userid_foreign');
        });
        Schema::table('tags', function (Blueprint $table) {
            $table->dropForeign('tags_documentid_foreign');
        });
        Schema::table('taskAssignedTo', function (Blueprint $table) {
            $table->dropForeign('taskassignedto_taskid_foreign');
        });
        Schema::table('taskCategory', function (Blueprint $table) {
            $table->dropForeign('taskcategory_taskcategoryid_foreign');
            $table->dropForeign('taskcategory_taskid_foreign');
        });
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign('tasks_priorityid_foreign');
            $table->dropForeign('tasks_taskstatusid_foreign');
            $table->dropForeign('tasks_tasktypeid_foreign');
        });
        Schema::table('userRoles', function (Blueprint $table) {
            $table->dropForeign('userroles_accessroleid_foreign');
            $table->dropForeign('userroles_staffid_foreign');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_roleid_foreign');
        });
        Schema::table('vitalTypeFields', function (Blueprint $table) {
            $table->dropForeign('vitaltypefields_vitalfieldid_foreign');
            $table->dropForeign('vitaltypefields_vitaltypeid_foreign');
        });
        Schema::table('widgetAccesses', function (Blueprint $table) {
            $table->dropForeign('widgetaccesses_accessroleid_foreign');
            $table->dropForeign('widgetaccesses_widgetid_foreign');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('drop_foreign__key_all');
    }
}
