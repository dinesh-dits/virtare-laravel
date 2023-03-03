<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use App\Models\Tag\Tag;
use App\Models\Staff\Staff;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use App\Models\Patient\Patient;
use App\Models\Document\Document;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use App\Models\Patient\PatientTimeLine;
use App\Transformers\Document\DocumentTransformer;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    // Add Document
    public function documentCreate($request, $entity, $id, $documentId)
    {
        DB::beginTransaction();
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            if (!$documentId) {
                $reference = Helper::entity($entity, $id);
                $input = [
                    'name' => $request->input('name'), 'filePath' => $request->input('document'), 'documentTypeId' => $request->input('type'), 'locationEntityType' => $entityType,
                    'referanceId' => $reference, 'entityType' => $entity, 'udid' => Str::uuid()->toString(), 'createdBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation
                ];
                $document = Document::create($input);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'documents', 'tableId' => $document->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                ];
                ChangeLog::create($changeLog);
                $tags = $request->input('tags');
                foreach ($tags as $value) {
                    $tag = [
                        'tag' => $value, 'createdBy' => 1, 'udid' => Str::uuid()->toString(), 'documentId' => $document['id'], 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                    ];
                    $tagInput = Tag::create($tag);
                    // $changeLog = [
                    //     'udid' => Str::uuid()->toString(), 'table' => 'tags', 'tableId' => $tagInput->id,
                    //     'value' => json_encode($tag), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    // ];
                    // ChangeLog::create($changeLog);
                }

                if ($entity == 'patient') {
                    $userInput = Patient::where('id', $reference)->first();
                    $referenceId = Auth::id();
                    $userInput = Staff::where('userId', $referenceId)->first();
                    $timeLine = [
                        'patientId' => $reference, 'heading' => 'Document Added', 'title' => 'Document Added <b>By' . ' ' . $userInput->lastName . ',' . ' ' . $userInput->firstName . '</b>', 'type' => 5,
                        'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                    ];
                    $timelineData = PatientTimeLine::create($timeLine);
                } else {
                    $userInput = Staff::where('id', $reference)->first();
                }
                // $changeLog = [
                //     'udid' => Str::uuid()->toString(), 'table' => 'patientTimelines', 'tableId' => $timelineData->id,
                //     'value' => json_encode($timeLine), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                // ];
                // ChangeLog::create($changeLog);

                $getDocument = Document::where([['id', $document->id], ['entityType', $entity]])->with('documentType', 'tag.tags')->first();
                $userdata = fractal()->item($getDocument)->transformWith(new DocumentTransformer())->toArray();
                $message = ['message' => trans('messages.addedSuccesfully')];
            } else {
                $input = array();
                if ($request->input('name')) {
                    $input['name'] = $request->input('name');
                }
                if ($request->input('document')) {
                    //$input['filePath'] = str_replace(str_replace("public", "", URL::to('/') . '/'), "", $request->input('document'));
                    $input['filePath'] = Storage::disk('s3')->put('public' . "/" . date("Y") . "/" . date("m"), $request->input('document'));
                }
                if ($request->input('type')) {
                    $input['documentTypeId'] = $request->input('type');
                }
                $input['updatedBy'] = Auth::id();
                $input['providerId'] = $provider;
                $input['providerLocationId'] = $providerLocation;

                $document = Document::where('udid', $documentId)->first();
                $tagData = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
                Tag::where('documentId', $document->id)->update($tagData);
                $tagInputs = Tag::where('documentId', $document->id)->get();

                // $changeLog = [
                //     'udid' => Str::uuid()->toString(), 'table' => 'tags', 'tableId' => $tagInputs->id,
                //     'value' => json_encode($tagData), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                // ];
                // ChangeLog::create($changeLog);
                Tag::where('documentId', $document->id)->delete();
                Document::where('udid', $documentId)->update($input);
                $documentData = Document::where('udid', $documentId)->first();
                if ($documentData) {
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'documents', 'tableId' => $documentData->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                        'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    ];
                    ChangeLog::create($changeLog);
                }
                $tags = $request->input('tags');
                foreach ($tags as $value) {
                    $tag = [
                        'tag' => $value, 'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'documentId' => $document->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation
                    ];
                    $tagData = Tag::create($tag);
                    // $changeLog = [
                    //     'udid' => Str::uuid()->toString(), 'table' => 'tags', 'tableId' => $tagData->id,
                    //     'value' => json_encode($tag), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    // ];
                    // ChangeLog::create($changeLog);
                }
                $getDocument = Document::where([['udid', $documentId], ['entityType', $entity]])->with('documentType', 'tag.tags')->first();
                $userdata = fractal()->item($getDocument)->transformWith(new DocumentTransformer())->toArray();
                $message = ['message' => trans('messages.updatedSuccesfully')];
            }
            DB::commit();
            $endData = array_merge($message, $userdata);
            return $endData;
        } catch (Exception $e) {
            DB::rollback();
            throw new \RuntimeException($e);
        }
    }

    // List Document
    public function documentList($request, $entity, $id, $documentId)
    {
        try {
            $reference = Helper::entity($entity, $id);
            $getDocument = Document::select('documents.*')->with('documentType', 'tag.tags');

           /* $getDocument->leftJoin('providers', 'providers.id', '=', 'documents.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            $getDocument->leftJoin('programs', 'programs.id', '=', 'documents.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            $getDocument->leftJoin('providerLocations', function ($join) {
                $join->on('documents.providerLocationId', '=', 'providerLocations.id')->where('documents.entityType', '=', 'Country');
            })->whereNull('providerLocations.deletedAt');

            $getDocument->leftJoin('providerLocationStates', function ($join) {
                $join->on('documents.providerLocationId', '=', 'providerLocationStates.id')->where('documents.entityType', '=', 'State');
            })->whereNull('providerLocationStates.deletedAt');

            $getDocument->leftJoin('providerLocationCities', function ($join) {
                $join->on('documents.providerLocationId', '=', 'providerLocationCities.id')->where('documents.entityType', '=', 'City');
            })->whereNull('providerLocationCities.deletedAt');

            $getDocument->leftJoin('subLocations', function ($join) {
                $join->on('documents.providerLocationId', '=', 'subLocations.id')->where('documents.entityType', '=', 'subLocation');
            })->whereNull('subLocations.deletedAt');

            if (request()->header('providerId')) {
                $provider = Helper::providerId();
                $getDocument->where('documents.providerId', $provider);
            }
            if (request()->header('providerLocationId')) {
                $providerLocation = Helper::providerLocationId();
                if (request()->header('entityType') == 'Country') {
                    $getDocument->where([['documents.providerLocationId', $providerLocation], ['documents.entityType', 'Country']]);
                }
                if (request()->header('entityType') == 'State') {
                    $getDocument->where([['documents.providerLocationId', $providerLocation], ['documents.entityType', 'State']]);
                }
                if (request()->header('entityType') == 'City') {
                    $getDocument->where([['documents.providerLocationId', $providerLocation], ['documents.entityType', 'City']]);
                }
                if (request()->header('entityType') == 'subLocation') {
                    $getDocument->where([['documents.providerLocationId', $providerLocation], ['documents.entityType', 'subLocation']]);
                }
            }
            if (request()->header('programId')) {
                $program = Helper::programId();
                $entityType = Helper::entityType();
                $getDocument->where([['documents.programId', $program], ['documents.entityType', $entityType]]);
            } */
            if ($entity == 'patient') {
                $notAccess = Helper::haveAccess($reference);
                if (!$notAccess) {
                    if ($documentId) {
                        $getDocument->where([['documents.udid', $documentId], ['documents.entityType', $entity]]);
                        $getDocument = $getDocument->first();
                        return fractal()->item($getDocument)->transformWith(new DocumentTransformer())->toArray();
                    } else {
                        $getDocument->where([['documents.referanceId', $reference], ['documents.entityType', $entity]])->with('documentType', 'tag.tags')->latest();
                        $getDocument = $getDocument->get();
                        return fractal()->collection($getDocument)->transformWith(new DocumentTransformer())->toArray();
                    }
                } else {
                    return $notAccess;
                }
            } else {
                if ($documentId) {
                    $getDocument = $getDocument->where([['documents.udid', $documentId], ['documents.entityType', $entity]])->with('documentType', 'tag.tags')->first();
                    return fractal()->item($getDocument)->transformWith(new DocumentTransformer())->toArray();
                } else {
                    $getDocument = $getDocument->where([['documents.referanceId', $reference], ['documents.entityType', $entity]])->with('documentType', 'tag.tags')->latest()->get();
                    return fractal()->collection($getDocument)->transformWith(new DocumentTransformer())->toArray();
                }
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Document
    public function documentDelete($request, $entity, $id, $documentId)
    {
        DB::beginTransaction();
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $data = ['deletedBy' => Auth::id(), 'isDelete' => 1, 'isActive' => 0, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            Document::where([['udid', $documentId], ['entityType', $entity]])->update($data);
            // $document=Document::where([['udid', $documentId], ['entityType', $entity]])->first();
            // $changeLog = [
            //     'udid' => Str::uuid()->toString(), 'table' => 'documents', 'tableId' => $document->id,
            //     'value' => json_encode($data), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            // ];
            // ChangeLog::create($changeLog);

            tag::where('documentId', $documentId)->update($data);
            // $tag=tag::where('documentId', $documentId)->get();

            // $changeLog = [
            //     'udid' => Str::uuid()->toString(), 'table' => 'tags', 'tableId' => $tag->id,
            //     'value' => json_encode($data), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            // ];
            // ChangeLog::create($changeLog);

            $reference = Auth::id();
            $userInput = Staff::where('userId', $reference)->first();
            $entityId = Helper::entity($entity, $id);
            if ($entity == 'patient') {
                $timeLine = [
                    'patientId' => $entityId, 'heading' => 'Document Deleted', 'title' => 'Document Deleted <b>By' . ' ' . $userInput->lastName . ',' . ' ' . $userInput->firstName . '</b>', 'type' => 5,
                    'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $provider, 'providerLocationId' => $providerLocation
                ];
                $timeLogData = PatientTimeLine::create($timeLine);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patientTimelines', 'tableId' => $timeLogData->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($timeLine), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
            }
            Document::where([['udid', $documentId], ['entityType', $entity]])->delete();
            tag::where('documentId', $documentId)->delete();
            DB::commit();
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (Exception $e) {
            DB::rollback();
            throw new \RuntimeException($e);
        }
    }
}
