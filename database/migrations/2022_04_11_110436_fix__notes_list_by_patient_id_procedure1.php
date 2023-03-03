    <?php

    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;

    class FixNotesListByPatientIdProcedure1 extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            $procedure = "DROP PROCEDURE IF EXISTS `NotesListByPatientId`";
            DB::unprepared($procedure);

            $procedure = "CREATE PROCEDURE `NotesListByPatientId`(referenceId INT)
            BEGIN 

    (SELECT notes.id as newId, notes.udid as udid, notes.date as date, notes.note, gc1.name as category, gc2.name as type, notes.createdBy as userId,
    if(p1.firstName IS NULL, CONCAT(s1.firstName,' ',s1.lastName), CONCAT(p1.firstName,' ',p1.lastName)) AS addedBy,
    if(u1.profilePhoto IS NULL, u1.profilePhoto,u2.profilePhoto) AS profilePhoto, g1.name AS specialization,
    ' ' as flagIcon,flags.color AS flagColor, flags.name AS flagName FROM `notes`
    LEFT JOIN globalCodes as gc1 ON gc1.id = notes.categoryId
    LEFT JOIN globalCodes as gc2 ON gc2.id = notes.type
    LEFT JOIN flags ON flags.id = notes.flagId
    LEFT JOIN staffs as s1 ON s1.userId = notes.createdBy AND s1.roleId = 1 OR s1.userId = notes.createdBy AND s1.roleId = 3
    LEFT JOIN globalCodes AS g1 ON g1.id=s1.specializationId
    LEFT JOIN users as u1 ON u1.id=s1.userId
    LEFT JOIN patients as p1 ON p1.userId = notes.createdBy
    LEFT JOIN users as u2 ON u2.id=p1.userId
    WHERE notes.referenceId = referenceId AND notes.entityType = 'patient'
    UNION
    SELECT notes.id as newId, notes.udid as udid, notes.createdAt as date, notes.note, 'General' as category, 'Appointment' as type, notes.createdBy as userId,
    if(p1.firstName IS NULL, CONCAT(s1.firstName,' ',s1.lastName), CONCAT(p1.firstName,' ',p1.lastName)) AS addedBy,
    if(u1.profilePhoto IS NULL, u1.profilePhoto,u2.profilePhoto) AS profilePhoto, g1.name AS specialization,
    ' ' AS flagIcon,flags.color AS flagColor, flags.name AS flagName FROM `notes`
    LEFT JOIN globalCodes as gc1 ON gc1.id = notes.categoryId
    LEFT JOIN globalCodes as gc2 ON gc2.id = notes.type
    LEFT JOIN flags ON flags.id = notes.flagId
    LEFT JOIN staffs as s1 ON (s1.userId = notes.createdBy AND s1.roleId = 1) OR (s1.userId = notes.createdBy AND s1.roleId = 3)
    LEFT JOIN globalCodes AS g1 ON g1.id=s1.specializationId
    LEFT JOIN users as u1 ON u1.id=s1.userId
    LEFT JOIN patients as p1 ON p1.userId = notes.createdBy
    LEFT JOIN users as u2 ON u2.id=p1.userId
    WHERE notes.referenceId IN (SELECT id FROM appointments WHERE `patientID` = referenceId) AND notes.entityType = 'appointment'
    UNION
    SELECT notes.id as newId, notes.udid as udid, notes.createdAt as date, notes.note, 'General' as category, 'Timelogs' as type, notes.createdBy as userId,
    if(p1.firstName IS NULL, CONCAT(s1.firstName,' ',s1.lastName), CONCAT(p1.firstName,' ',p1.lastName)) AS addedBy,
    if(u1.profilePhoto IS NULL, u1.profilePhoto,u2.profilePhoto) AS profilePhoto, g1.name AS specialization,
    ' ' as flagIcon,flags.color AS flagColor, flags.name AS flagName FROM `notes`
    LEFT JOIN globalCodes as gc1 ON gc1.id = notes.categoryId
    LEFT JOIN globalCodes as gc2 ON gc2.id = notes.type
    LEFT JOIN flags ON flags.id = notes.flagId
    LEFT JOIN staffs as s1 ON (s1.userId = notes.createdBy AND s1.roleId = 1) OR (s1.userId = notes.createdBy AND s1.roleId = 3)
    LEFT JOIN globalCodes AS g1 ON g1.id=s1.specializationId
    LEFT JOIN users as u1 ON u1.id=s1.userId
    LEFT JOIN patients as p1 ON p1.userId = notes.createdBy
    LEFT JOIN users as u2 ON u2.id=p1.userId
    WHERE notes.referenceId IN (SELECT id FROM patientTimeLogs WHERE `patientID` = referenceId) AND notes.entityType = 'auditlog'
    UNION
    SELECT notes.id as newId, notes.udid as udid, notes.createdAt as date, notes.note, 'General' as category, 'Vitals' as type, notes.createdBy as userId,
    if(p1.firstName IS NULL, CONCAT(s1.firstName,' ',s1.lastName), CONCAT(p1.firstName,' ',p1.lastName)) AS addedBy,
    if(u1.profilePhoto IS NULL, u1.profilePhoto,u2.profilePhoto) AS profilePhoto, g1.name AS specialization,
    ' ' as flagIcon,flags.color AS flagColor, flags.name AS flagName FROM `notes`
    LEFT JOIN globalCodes as gc1 ON gc1.id = notes.categoryId
    LEFT JOIN globalCodes as gc2 ON gc2.id = notes.type
    LEFT JOIN flags ON flags.id = notes.flagId
    LEFT JOIN staffs as s1 ON (s1.userId = notes.createdBy AND s1.roleId = 1) OR (s1.userId = notes.createdBy AND s1.roleId = 3)
    LEFT JOIN globalCodes AS g1 ON g1.id=s1.specializationId
    LEFT JOIN users as u1 ON u1.id=s1.userId
    LEFT JOIN patients as p1 ON p1.userId = notes.createdBy
    LEFT JOIN users as u2 ON u2.id=p1.userId
    WHERE notes.referenceId IN (SELECT id FROM patientVitals WHERE `patientID` = referenceId) AND notes.entityType = 'patientVital')
    ORDER BY newId DESC;

    END;";
            DB::unprepared($procedure);
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            //
        }
    }
