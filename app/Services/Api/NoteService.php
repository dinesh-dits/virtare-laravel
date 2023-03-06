<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use App\Models\Flag\Flag;
use App\Models\Note\Note;
use App\Models\Staff\Staff;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use App\Models\Patient\Patient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Patient\PatientTimeLine;
use App\Transformers\Note\NoteTransformer;

class NoteService
{
    // Add Note
    public function noteAdd($request, $entity, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $userId = Auth::id();
            $referenceId = Helper::entity($entity, $id);
            $dataConvert = Helper::date($request->input('date'));
            $flag = Flag::where('udid', $request->input('flag'))->first();
            if ($flag) {
                $flagId = $flag->id;
            } else {
                $flagId = '';
            }
            $input = [
                'date' => $dataConvert, 'categoryId' => $request->input('category'), 'type' => $request->input('type'), 'flagId' => $flagId, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'note' => $request->input('note'), 'udid' => Str::uuid()->toString(), 'createdBy' => $userId, 'referenceId' => $referenceId, 'entityType' => $request->input('entityType'), 'locationEntityType' => $entityType
            ];
            $note = Note::create($input);
            if (auth()->user()->roleId == 4) {
                $userInput = Patient::where('id', auth()->user()->patient->id)->first();
            } else {
                $userInput = Staff::where('id', auth()->user()->staff->id)->first();
            }
            $timeLine = [
                'patientId' => $referenceId, 'heading' => 'Note Added', 'title' => $request->input('note') . ' ' . '<b>By' . ' ' . $userInput->lastName . ',' . ' ' . $userInput->firstName . '</b>', 'type' => 6,
                'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
            ];
            PatientTimeLine::create($timeLine);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'notes', 'tableId' => $note->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
            ];
            ChangeLog::create($changeLog);
            return response()->json(['message' => trans('messages.createdSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Note
    public function noteList($request, $entity, $id, $noteId)
    {
        try {
            if ($request->latest) {
                $referenceId = Helper::entity($entity, $id);
                if (isset($request->fromDate) && isset($request->toDate)) {
                    $fromDateStr = Helper::date($request->input('fromDate'));
                    $toDateStr = Helper::date($request->input('toDate'));

                    $note = DB::select("CALL NotesListByPatientIdWithDate('" . $referenceId . "','" . $fromDateStr . "','" . $toDateStr . "')");
                } else {
                    $note = DB::select('CALL NotesListByPatientId(' . $referenceId . ')',);
                }

                // $note = DB::select('CALL NotesListByPatientId(' . $referenceId . ')',);
                // $note = Note::where([['referenceId', $referenceId], ['entityType', $entity]])->with('typeName', 'category')->latest('createdAt')->get();
                if (!empty($note)) {
                    return fractal()->collection($note)->transformWith(new NoteTransformer())->toArray();
                } else {
                    $note = [];
                    return fractal()->collection($note)->transformWith(new NoteTransformer())->toArray();
                }
            } else {
                // $note = Note::where('entityType', $entity)->with('typeName', 'category')->get();
                $referenceId = Helper::entity($entity, $id);
                if (isset($request->fromDate) && isset($request->toDate)) {
                    $fromDateStr = Helper::date($request->input('fromDate'));
                    $toDateStr = Helper::date($request->input('toDate'));
                }
                if ($request->type == 'patient') {
                    $note = DB::select("CALL NotesListByPatientIdPatient('" . $referenceId . "','" . $fromDateStr . "','" . $toDateStr . "')");
                } elseif ($request->type == 'appointment') {
                    $note = DB::select("CALL NotesListByPatientIdAppointment('" . $referenceId . "','" . $fromDateStr . "','" . $toDateStr . "')");
                } elseif ($request->type == 'auditlog') {
                    $note = DB::select("CALL NotesListByPatientIdAuditlog('" . $referenceId . "','" . $fromDateStr . "','" . $toDateStr . "')");
                } elseif ($request->type == 'patientFlag') {
                    $note = DB::select("CALL NotesListByPatientIdPatientFlag('" . $referenceId . "','" . $fromDateStr . "','" . $toDateStr . "')");
                } elseif ($request->type == 'patientVital') {
                    $note = DB::select("CALL NotesListByPatientIdpatientVital('" . $referenceId . "','" . $fromDateStr . "','" . $toDateStr . "')");
                } else {
                    if (isset($request->fromDate) && isset($request->toDate)) {
                        $note = DB::select("CALL NotesListByPatientIdWithDate('" . $referenceId . "','" . $fromDateStr . "','" . $toDateStr . "')");
                    } else {
                        $note = DB::select('CALL NotesListByPatientId(' . $referenceId . ')',);
                    }
                }
                if (!empty($note)) {
                    return fractal()->collection($note)->transformWith(new NoteTransformer())->toArray();
                } else {
                    $note = [];
                    return fractal()->collection($note)->transformWith(new NoteTransformer())->toArray();
                }
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Patient Note
    public function patientNoteList($request): array
    {
        try {
            $referenceId = auth()->user()->patient->id;
            if (@$request->fromDate && @$request->toDate) {
                $fromDateStr = Helper::date($request->input('fromDate'));
                $toDateStr = Helper::date($request->input('toDate'));

                $note = DB::select("CALL NotesListByPatientIdWithDate('" . $referenceId . "','" . $fromDateStr . "','" . $toDateStr . "')");
            } else {
                $note = DB::select('CALL NotesListByPatientId(' . $referenceId . ')',);
            }
            if (!empty($note)) {
                return fractal()->collection($note)->transformWith(new NoteTransformer())->toArray();
            } else {
                $note = [];
                return fractal()->collection($note)->transformWith(new NoteTransformer())->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
