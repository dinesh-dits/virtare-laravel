<?php

namespace App\Transformers\Escalation;

use App\Models\Note\Note;
use Illuminate\Support\Facades\DB;
use App\Models\GlobalCode\GlobalCode;
use League\Fractal\TransformerAbstract;
use App\Transformers\Note\NoteTransformer;


class EscalationNotesTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        $escalationType = ""; 
        if(isset($data->escalationType) && !empty($data->escalationType)){
            $globalCode = GlobalCode::where("id",$data->escalationType)->first();
            if(isset($globalCode->name)){
                $escalationType = $globalCode->name;
            }
        }

        $note = "";
        $notes = "";
        $date = "";
        $category = "";
        $type = "";
        $note = "";
        $addedBy = "";
        if(isset($data->notesId) && !empty($data->escalationType)){
            $notes = Note::where("id",$data->notesId)->with('typeName')->first();
            if($notes->entityType == "patient"){
                $notes = DB::select(
                    "CALL getNotesByNotesId('" . $data->notesId . "')"
                );
            }elseif($notes->entityType == "appointment"){
                $notes = DB::select(
                    "CALL getAppointmentNotesByNotesId('" . $data->notesId . "')"
                );
            }elseif($notes->entityType == "auditlog"){
                $notes = DB::select(
                    "CALL getAuditlogNotesByNotesId('" . $data->notesId . "')"
                );
            }elseif($notes->entityType == "patientVital"){
                $notes = DB::select(
                    "CALL getPatientVitalNotesByNotesId('" . $data->notesId . "')"
                );
            }else{
                $notes = DB::select(
                    "CALL getNotesByNotesId('" . $data->notesId . "')"
                );
            }

            if(isset($notes[0]->id)){
                $date = strtotime($notes[0]->date);
                $category = $notes[0]->category;
                $type = $notes[0]->type;
                $note = $notes[0]->note;
                $addedBy = (!empty($notes[0]->addedBy)) ? $notes[0]->addedBy:'';
                $addedById = (!empty($notes[0]->addedById)) ? $notes[0]->addedById:'';
            }
            
        }    
        if(!empty($notes)){
            $notes = fractal()->item($notes[0])->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->transformWith(new NoteTransformer())->toArray();
        }else{
            $notes = array();
        }
        return [
            'udid' => $data->udid,
            'notesId' => $data->notesId,
            'escalationId' => $data->escalationId,
            'escalationTypeId' => $data->escalationType,
            'escalationType' => $escalationType,
            'isActive' => $data->isActive?True:False,
            'note' => $notes,
        ];
    }
}
