<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\CustomForm\CustomFields;
use App\Models\CustomForm\CustomForms;
use Illuminate\Support\Facades\Auth;
use App\Models\CustomForm\CustomFormFields;
use App\Models\CustomForm\CustomFormResponse;
use App\Models\CustomForm\CustomFormResponseData;
use App\Models\Patient\Patient;
use App\Models\CustomForm\customFormAssignedToUser;
use App\Models\Group\Group;
use App\Models\Patient\PatientGroup;
use App\Models\Patient\PatientProgram;
use App\Models\Program\Program;
use App\Transformers\CustomForms\CustomFormsAssignedTransformer;
use App\Transformers\CustomForms\CustomFormsTransformer;
use App\Models\Setting\Setting;
use App\Models\CustomTemplate\CustomTemplates;
use App\Models\Patient\PatientFamilyMember;
use App\Models\Questionnaire\QuestionnaireTemplate;
use Carbon;
use App\Models\Questionnaire\QuestionnaireQuestion;
use App\Models\QuestionnaireSection\QuestionSection;
use App\Models\Questionnaire\ClientQuestionnaireAssign;
use App\Models\Questionnaire\ClientQuestionnaireTemplate;
use App\Services\Api\ClientQuestionnaireService;
use App\Models\DummySteps;
use App\Models\Workflow\WorkFlowQueueStepAction;
use App\Models\Workflow\WorkFlowQueue;
use App\Models\GlobalCode\GlobalCode;

class CustomFormService
{

    public function create_form($request)
    {
        try {
            $formData = $request->all();
            $customForm = CustomForms::create(
                array(
                    'udid' => Str::uuid()->toString(),
                    'formName' => $request->form_name,
                    'createdBy' => Auth::id(),
                    'formFields' => json_encode($formData['fields']),
                    'status' => 1,
                    'updatedBy' => 0,
                    'deletedBy' => 0
                )
            );
            if (isset($customForm->id) && !empty($customForm->id)) {
                /*$formFields = array();
                foreach ($formData['fields'] as $key => $fields) {
                    $formFields[$key]['udid'] = Str::uuid()->toString();
                    $formFields[$key]['customFormId'] = $customForm->id;
                    $formFields[$key]['order'] = $fields['order'];
                    $formFields[$key]['name'] = $fields['name'];
                    $formFields[$key]['type'] = $fields['type'];
                    $formFields[$key]['required'] = $fields['required'];
                    $formFields[$key]['properties'] = json_encode($fields['properties']);
                }
                if (0 < count($formFields)) {
                    CustomFormFields::insert($formFields);
                }*/
                return response()->json(['message' => 'Form created succesfully'], 201);
            } else {
                return response()->json(['message' => 'Unable to create form, please try gain later'], 500);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function getAllForms()
    {
        try {
            $allForms = CustomForms::where('createdBy', Auth::id())->where('status', 1)->whereNull('deletedAt')->get();
            // print_r( $allForms);
            return fractal()->collection($allForms)->transformWith(new CustomFormsTransformer(false))->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function getFormDetail($id)
    {
        try {
            $formDetail = CustomForms::where('createdBy', Auth::id())->where('udid', $id)->first();
            if (isset($formDetail->id) && !empty($formDetail->id)) {

                $responseData = json_decode($formDetail['formFields']);
                foreach ($responseData as $key => $response) :
                    if (isset($response->type) && $response->type == 'questionnaire') {
                        $responseData[$key]->data = $this->getTemplateQuestionSection($response->id);
                        $formDetail['templateId'] = $response->id;
                    } else if (isset($response->type) && $response->type == 'address') {
                        $responseData[$key]->data = $this->addressHtml();
                    }
                endforeach;
                $formDetail['formFields'] = json_encode($responseData);
                //  return response()->json(['data' => $responseData], 200);
                return fractal()->item($formDetail)->transformWith(new CustomFormsTransformer(true))->toArray();
            } else {
                return response()->json(['message' => 'Required field missing'], 404);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function deleteForm($id)
    {
        try {
            $formDetail = CustomForms::where('createdBy', Auth::id())->where('udid', $id)->first();
            if (isset($formDetail->id) && !empty($formDetail->id)) {
                $update = array(
                    'deletedBy' => Auth::id(),
                    'deletedAt' => date('Y-m-d H:i:s', time()),
                    'status' => 0
                );
                $formDetail->update($update);

                customFormAssignedToUser::where('customFormId', $formDetail->id)->delete();

                return response()->json(['message' => 'Form deleted succesfully.'], 200);
            } else {
                return response()->json(['message' => 'Required field missing'], 404);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function saveFormData($request)
    {
        try {
            $responsedata = $request->all();
            $formId = $responsedata['assigned_udid'];
            $templateId = $responsedata['templateId'];
            $actionStep = isset($responsedata['action']) ? $responsedata['action'] : 0;
            // $formDetail = CustomForms::where('udid', $formId)->where('status', 1)->whereNull('deletedAt')->first();
            $formDetail = customFormAssignedToUser::where('udid', $formId)->with('customform')->first();
            $alreadyRespond = CustomFormResponse::where('assignedId', $formDetail->id)->first(); // Check if already filled form
            if (isset($alreadyRespond->id) && !empty($alreadyRespond->id)) {
                return response()->json(['message' => 'You have already filled the form'], 200);
            } else {
                if (isset($formDetail->id) && !empty($formDetail->id)) {
                    $respoonse = CustomFormResponse::create(['udid' => Str::uuid()->toString(), 'assignedId' => $formDetail->id, 'submittedBy' => Auth::id()]);
                    if ($respoonse->id) {
                        $values = array();
                        foreach ($responsedata['values'] as $key => $respVal) :
                            $values[$key]['udid'] = Str::uuid()->toString();
                            $values[$key]['responseId'] = $respoonse->id;
                            $values[$key]['keyName'] = $key;
                            $values[$key]['referenceId'] = '';
                            $values[$key]['refrenceModel'] = '';
                            $values[$key]['value'] = $respVal;
                        endforeach;
                        if (isset($responsedata['questions']) && count($responsedata['questions']) > 0) {
                            $indexKey = count($values);
                            $savedId = $this->save_response_template_question_section_updated($responsedata['questions'], $formDetail->customform->id, $templateId);
                            $values[$indexKey]['udid'] = Str::uuid()->toString();
                            $values[$indexKey]['responseId'] = $respoonse->id;
                            $values[$indexKey]['keyName'] = 'QuestionnaireTemplate';
                            $values[$indexKey]['value'] = '';
                            $values[$indexKey]['referenceId'] = $savedId;
                            $values[$indexKey]['refrenceModel'] = 'ClientQuestionnaireTemplate';
                        }
                        $data = CustomFormResponseData::insert($values); // Save Values
                        // if ($actionStep == 'step') {
                        //echo $formDetail->id.'++'.$formDetail->userId;
                        $this->updateStep($formDetail->id, $formDetail->userId);
                        //}
                        return response()->json(['message' => 'Data saved succesfully.'], 201);
                    } else {
                        return response()->json(['message' => 'Unable to create form, please try gain later'], 500);
                    }
                } else {
                    return response()->json(['message' => 'Form not found'], 404);
                }
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function getResponseData($id, $userId)
    {
        try {

            //echo $id; die;
            // $formDetail = CustomForms::where('udid', $id)->with('fields')->first(); // Get Form Detail
            $formDetail = CustomForms::where('udid', $id)->first(); // Get Form Detail

            $columns = array();
            $values = array();
            if (isset($formDetail->id)) {

                $patient = Patient::where('udid', $userId)->first(); // Get Patient User Id
                $userId = $patient->userId;
                $assigned = customFormAssignedToUser::where('userId', $userId)->where('customFormId', $formDetail->id)->with('user')->first(); // Get Assigned form
                if (isset($assigned->id)) {
                    $responseData = CustomFormResponse::where('assignedId', $assigned->id)->with(['responses', 'user'])->get();

                    $countryState = $this->getCountryState();
                    $fields = json_decode($formDetail->formFields);
                    //print_r($fields);

                    $QuestionareResponse = $this->getQuestionTemplateResponse($fields, $userId, $formDetail->id);

                    $index = 0;
                    foreach ($fields as $key => $details) :
                        if (strtolower($details->name) != 'button' && strtolower($details->name) != 'questionnaire' && strtolower($details->name) != 'address') {
                            $columns[$index]['title'] = $details->name;
                            $columns[$index]['order'] = $details->order;
                            $columns[$index]['dataIndex'] = strtolower($details->name);
                            $index++;
                        }
                        if (strtolower($details->name) == 'address') {
                            $addressColumns = $this->addressColumns();
                            foreach ($addressColumns as $key => $colData) :
                                $colData['order'] = count($columns);
                                array_push($columns, $colData);
                            endforeach;
                        }
                    endforeach;

                    if ($responseData->count() > 0) {
                        foreach ($responseData as $keyRes => $response) :
                            foreach ($response['responses'] as $key => $result) :
                                if (strtolower($result->keyName) != 'questionnairetemplate') {
                                    if (strtolower($result->keyName) == 'city' || strtolower($result->keyName) == 'state' || strtolower($result->keyName) == 'country') {
                                        $values[$keyRes][strtolower($result->keyName)] = isset($countryState[$result->value]) ? $countryState[$result->value] : $result->value;
                                    } else {
                                        $values[$keyRes][strtolower($result->keyName)] = $result->value;
                                    }
                                }
                            endforeach;
                            $values[$keyRes]['submitted_by'] = ($response->submittedBy == 1) ? 'Admin' : (($response->submittedBy == $assigned->userId) ? 'Self' : $this->getUserdetail($response->submittedBy, $assigned->user->roleId));
                            $values[$keyRes]['relation'] = ($response->submittedBy == 1) ? '-' : (($response->submittedBy == $assigned->userId) ? '-' : $this->getRelation($response->submittedBy, $assigned->user->roleId));
                        endforeach;
                        if (count($QuestionareResponse) > 0) {
                            foreach ($QuestionareResponse['columns'] as $key => $colData) :
                                $colData['order'] = count($columns);
                                array_push($columns, $colData);
                            endforeach;
                            foreach ($QuestionareResponse['values'] as $key => $colVal) :

                                foreach ($colVal as $key => $val) :
                                    $values[$keyRes][strtolower($key)] = $val;
                                endforeach;

                            endforeach;
                        }
                        $createdBy['title'] = 'Submitted By';
                        $createdBy['order'] = count($columns);
                        $createdBy['dataIndex'] = 'submitted_by';
                        array_push($columns, $createdBy);
                        unset($createdBy);
                        $relation['title'] = 'Relation';
                        $relation['order'] = count($columns);
                        $relation['dataIndex'] = 'relation';
                        array_push($columns, $relation);
                        unset($relation);
                        return response()->json(['data' => array('columns' => $columns, 'values' => $values)], 200);
                    } else {
                        return response()->json(['message' => 'No response found'], 404);
                    }
                } else {
                    return response()->json(['message' => 'No response found'], 404);
                }
            } else {
                return response()->json(['message' => 'Template not found'], 404);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function getQuestionTemplateResponse($fields, $userId, $customFormId)
    {
        try {
            $columns = array();
            $values = array();
            $templateId = 0;
            foreach ($fields as $key => $response) :
                if ($response->type == 'questionnaire') {
                    $templateId = $response->id;
                }
            endforeach;
            if (isset($templateId) && $templateId !== 0) {
                $queResponse = $this->getQuestionnaireScrore($templateId, $customFormId, $userId);
                if (isset($queResponse['data'])) { // No Score
                    $columns[0]['title'] = $queResponse['data']['program'];
                    $columns[0]['order'] = 0;
                    $columns[0]['dataIndex'] = strtolower($queResponse['data']['program']);
                    $values[0][strtolower($queResponse['data']['program'])] = $queResponse['data']['score'];
                } else {
                    foreach ($queResponse as $key => $res) :
                        $columns[$key]['title'] = $res['program'] . ' (Q. Score)';
                        $columns[$key]['order'] = $key;
                        $columns[$key]['dataIndex'] = strtolower($res['program']);
                        $values[0][strtolower($res['program'])] = $res['score'];
                    endforeach;
                }
            }
            $data['columns'] = $columns;
            $data['values'] = $values;
            return $data;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function getUserdetail($userId, $roleId)
    {
        try {
            $detail = PatientFamilyMember::where('userId', $userId)->first();
            if (isset($detail->firstName)) {
                if (!empty($detail->lastName))
                    return $detail->lastName . ',' . $detail->firstName;
                else
                    return $detail->firstName;
            } else {
                return "-";
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function getRelation($userId, $roleId)
    {
        try {
            $detail = PatientFamilyMember::where('userId', $userId)->with('relation')->first();

            if (isset($detail->firstName)) {
                return $detail->relation->name;
            } else {
                return "-";
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function assignForm($request)
    {
        try {
            $formDetail = CustomForms::where('udid', $request->form_id)->with('fields')->first();
            if (isset($formDetail->id) && $formDetail->id) {
                $forms = json_decode($formDetail->formFields);

                $questionnaireTemplate = 0;
                $templateIds = array();
                $questionareData = array();
                foreach ($forms as $key => $form) :
                    if (isset($form->type) && $form->type == 'questionnaire') {
                        $questionnaireTemplate = 1;
                        $questionareData[] = $form->id;
                    }
                endforeach;

                /*if (isset($form[0]) && $form[0]->type == 'questionnaire') {
                $templateDetail = QuestionnaireTemplate::where('udid', $form[0]->id)->first();
                if (isset($templateDetail->questionnaireTemplateId)) {
                    $questionnaireTemplate = 1;
                    $templateId = $templateDetail->questionnaireTemplateId;
                }
            }*/
                if (isset($questionareData) && count($questionareData) > 0) {
                    $templateDetails = QuestionnaireTemplate::whereIn('udid', $questionareData)->get();
                    foreach ($templateDetails as $tempDetail) {
                        $questionnaireTemplate = 1;
                        $templateIds[] = $tempDetail->questionnaireTemplateId;
                    }
                }


                $patients = array();
                $patientsUdiD = array();
                $saveData = array();
                $assignTemplate = array();

                if ($request->has('type') && ($request->type == 'group' || $request->type == 'program' || $request->type == 'patients')) {
                    if ($request->type == 'group') {
                        $patients = $this->getGroupPatients($request->values);
                    } elseif ($request->type == 'program') {
                        $patients = $this->getProgramPatients($request->values);
                    } elseif ($request->type == 'patients') {
                        foreach ($request->values as $key => $users) :
                            $patientsUdiD[$key] = $users;
                        endforeach;
                        $patientsRecords = Patient::whereIn('udid', $patientsUdiD)->get();
                        foreach ($patientsRecords as $key => $patient) :
                            $patients[$patient->udid] = $patient->userId;
                        endforeach;
                    }
                }
                if (count($patients) > 0) {
                    $alreadyAssignStatus = $this->checkAlraedyAssignStatus($patients, $formDetail->id);
                    if (count($alreadyAssignStatus) > 0) {
                        $count = 0;
                        foreach ($alreadyAssignStatus as $key => $user) :
                            $saveData[$count]['udid'] = Str::uuid()->toString();
                            $saveData[$count]['customFormId'] = $formDetail->id;
                            $saveData[$count]['userId'] = $user;
                            $saveData[$count]['assignedBy'] = Auth::id();

                            /* if ($templateId && $questionnaireTemplate == 1) {  // To assign Questionnaire template
                                $assignTemplate[$count]["questionnaireTemplateId"] = $templateId;
                                $assignTemplate[$count]["referenceId"] = $user;
                                $assignTemplate[$count]["entityType"] = 247; // Patient for now
                                $assignTemplate[$count]['udid'] = Str::uuid()->toString();
                                $assignTemplate[$count]['createdBy'] = Auth::id();
                                $assignTemplate[$count]['customFormId'] = $formDetail->id;;
                            }*/
                            $count++;
                        endforeach;
                    } else {
                        return response()->json(['message' => 'No user found to assign this form.'], 200);
                    }
                } else {
                    return response()->json(['message' => 'No user found to assign this form.'], 200);
                }
                if (!empty($saveData)) {
                    $saved = customFormAssignedToUser::insert($saveData);
                }
                if ($questionnaireTemplate == 1 && count($templateIds) > 0) {  /// Assign Template
                    $count = 0;
                    foreach ($alreadyAssignStatus as $key => $user) :
                        // To assign Questionnaire template
                        foreach ($templateIds as $templateId) :
                            $assignTemplate[$count]["questionnaireTemplateId"] = $templateId;
                            $assignTemplate[$count]["referenceId"] = $user;
                            $assignTemplate[$count]["entityType"] = 247; // Patient for now
                            $assignTemplate[$count]['udid'] = Str::uuid()->toString();
                            $assignTemplate[$count]['createdBy'] = Auth::id();
                            $assignTemplate[$count]['customFormId'] = $formDetail->id;;

                            $count++;
                        endforeach;
                    endforeach;

                    if (!empty($assignTemplate)) {
                        $QuestionnaireSaved = ClientQuestionnaireAssign::insert($assignTemplate);
                    }
                }
                if (isset($saved) && $saved)
                    return response()->json(['message' => 'Form assigned succesfully'], 201);
                else
                    return response()->json(['message' => 'Unable to assign form, please try again later.'], 200);
            } else {
                return response()->json(['message' => 'Form detail not found'], 404);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }


    public function getGroupPatients($groupIds)
    {
        try {
            if ($groupIds) {
                $groupdUdIds = array();
                $groupdIds = array();
                $patients = array();
                $patientsData = array();
                foreach ($groupIds as $key => $groupId) :
                    $groupdUdIds[$key] = $groupId;
                endforeach;
                $groups = Group::whereIn('udid', $groupdUdIds)->whereNull('deletedAt')->get();
                foreach ($groups as $key => $group) :
                    $groupdIds[$key] = $group->groupId;
                endforeach;
                $groupData = PatientGroup::whereIn('groupId', $groupdIds)->whereNull('deletedAt')->get();
                if ($groupData->count() > 0) {
                    foreach ($groupData as $key => $group) :
                        $patients[$key] = $group->patientId;
                    endforeach;
                    $patientsRecords = Patient::whereIn('id', $patients)->get();
                    foreach ($patientsRecords as $key => $patient) :
                        $patientsData[$patient->udid] = $patient->userId;
                    endforeach;
                }
                return $patientsData;
            } else {
                return false;
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function getProgramPatients($programIds)
    {
        try {
            if ($programIds) {
                $programUdIds = array();
                $programIdData = array();
                $patients = array();
                $patientsData = array();
                foreach ($programIds as $key => $programId) :
                    $programUdIds[$key] = $programId;
                endforeach;
                $programs = Program::whereIn('udid', $programUdIds)->whereNull('deletedAt')->get();
                foreach ($programs as $key => $program) :
                    $programIdData[$key] = $program->id;
                endforeach;
                $patientPrograms = PatientProgram::whereIn('programId', $programIdData)->whereDate('dischargeDate', '<', Carbon\Carbon::now())->whereNull('deletedAt')->get();

                if ($patientPrograms->count() > 0) {
                    foreach ($patientPrograms as $key => $data) :
                        $patients[$key] = $data->patientId;
                    endforeach;
                    $patientsRecords = Patient::whereIn('id', $patients)->get();
                    foreach ($patientsRecords as $key => $patient) :
                        $patientsData[$patient->udid] = $patient->userId;
                    endforeach;
                }


                return $patientsData;
            } else {
                return false;
            }
        } catch (Exception $e) {

            throw new \RuntimeException($e);
        }
    }

    public function checkAlraedyAssignStatus($patientsData, $formId)
    {
        if ($patientsData) {
            $assignedUsers = array();
            $patients = array_values($patientsData);
            $reassignAfteDays = Setting::getValue('customform_re_assign_after'); // Get Days ,reassign after
            $assigned = customFormAssignedToUser::where('customFormId', $formId)->wherein('userId', $patients)->whereNull('deletedAt')->get();
            if ($assigned->count() > 0) {
                // Checked Filled or not
                foreach ($assigned as $key => $assign) :
                    $assignedUsers[$key] = $assign->userId;
                endforeach;
                $responseData = CustomFormResponse::where('assignedId', $formId)->wherein('submittedBy', $patients)->get();
                if ($responseData->count() > 0) {
                    foreach ($responseData as $key => $response) :
                        $diff = Carbon\Carbon::parse($response->createdAt)->diffInDays(Carbon\Carbon::now());
                        if ($diff >= $reassignAfteDays) { // Time completed need to delete
                            $keyLocation = array_search($response->userId, $assignedUsers);
                            unset($assignedUsers[$keyLocation]);
                        }
                    endforeach;
                }
                if (count($assignedUsers) > 0) { // check users after removing response data
                    foreach ($assignedUsers as $assigned) :
                        $keyLocation = array_search($assigned, $patientsData);
                        unset($patientsData[$keyLocation]);
                    endforeach;
                }
                return $patientsData;
            } else { // Not assigned return origional arary;
                return $patientsData;
            }
        }
    }

    public function getAssignedForm($id)
    {
        try {
            $patient = Patient::where('udid', $id)->first();

            if (isset($patient->id) && !empty($patient->id)) {
                $userId = $patient->userId;
                $assigned = customFormAssignedToUser::where('userId', $userId)->with('customform', 'response')->get();
                // print_r($assigned); die;


                if (0 < $assigned->count()) {
                    return fractal()->collection($assigned)->transformWith(new CustomFormsAssignedTransformer(false))->toArray();
                } else {
                    return response()->json(['message' => 'No form assigned to this user'], 200);
                }
            } else {
                return response()->json(['message' => 'Patient not found'], 404);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function get_custom_templates()
    {
        try {
            $response = array();
            $templates = CustomTemplates::where('isActive', 1)->orderby('templateType', 'asc')->orderby('order', 'asc')->get();
            $keyV = 0;
            $index = '';
            if ($templates->count() > 0) {

                foreach ($templates as $key => $template) :
                    if ($index != $template->templateType) {
                        $keyV = 0;
                    }
                    $response[$template->templateType][$keyV]['udid'] = $template->udid;
                    $response[$template->templateType][$keyV]['name'] = $template->name;
                    $response[$template->templateType][$keyV]['order'] = $template->order;
                    $response[$template->templateType][$keyV]['templateIcon'] = (file_exists(base_path() . '/public/images/custom-form/' . $template->templateIcon)) ? env('APP_URL') . 'images/custom-form/' . $template->templateIcon : '';

                    $index = $template->templateType;
                    $keyV++;
                endforeach;
                return response()->json(['data' => $response], 200);
            } else {
                return response()->json(['message' => 'No templates found'], 200);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function getTemplateDetail($id)
    {
        try {

            $data = QuestionnaireTemplate::with("questionnaireQuestion")
                ->where('questionnaireTemplates.udid', $id)
                ->first();

            $sections = array();
            $sectionQuestions = array();
            foreach ($data['questionnaireQuestion'] as $key => $section) :
                $sections[$key] = $section->referenceId;
            endforeach;

            $questions = QuestionSection::whereIn('questionnaireSectionId', $sections)->get();
            foreach ($questions as $key => $question) :
                $sectionQuestions[$question->questionnaireSectionId][$key] = $question->questionId;
            endforeach;
            $response['template_name'] = $data->templateName;
            $response['total_sections'] = count($sections);
            foreach ($sections as $key => $section) {
                $response['sectionsTotalQuestions'][] = count($sectionQuestions[$section]);
            }
            //return  $response;
            return response()->json(['data' => $response], 200);
        } catch (Exception $e) {
            // return false;
            throw new \RuntimeException($e);
        }
    }

    public function getTemplateQuestionSection($id)
    {
        try {
            $templateDetail = QuestionnaireTemplate::where('questionnaireTemplates.udid', $id)->first();

            if (isset($templateDetail->questionnaireTemplateId)) {
                $data = ClientQuestionnaireAssign::where("questionnaireTemplateId", $templateDetail->questionnaireTemplateId);
                $data->with("questionnaireTemplate");
                $data = $data->first();
                $objData = [];
                if (!empty($data)) {
                    $objData = (new ClientQuestionnaireService)->getDetail($data);
                }
                return $objData;
                //return response()->json(['data' => $objData], 200);
            } else {
                //return response()->json(['message' => 'Required field missing'], 404);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function save_response_template_question_section($response)
    {
        try {
            $data = $response->all();
            $request = new \Illuminate\Http\Request($data['values']);
            $formId = $data['assigned_udid'];
            // $formDetail = CustomForms::where('udid', $formId)->where('status', 1)->whereNull('deletedAt')->first();
            $formDetail = customFormAssignedToUser::where('udid', $formId)->first();

            if (isset($formDetail->id) && !empty($formDetail->id)) {
                $formId = $formDetail->customFormId;
                $templateDetail = QuestionnaireTemplate::where('questionnaireTemplates.udid', $data['templateId'])->first();
                $clientQuestionnaire = ClientQuestionnaireAssign::where('questionnaireTemplateId', $templateDetail->questionnaireTemplateId)->where('customFormId', $formId)->latest("createdAt")->first();
                $objData = (new ClientQuestionnaireService)->addQuestionnaireTemplateByUsers($request, $clientQuestionnaire->udid);
                $responseData = $objData->getData();
                $responseId = isset($responseData->id) ? $responseData->id : '';
                if ($responseId) {
                    $respoonse = CustomFormResponse::create(['udid' => Str::uuid()->toString(), 'assignedId' => $formDetail->id, 'submittedBy' => Auth::id(), 'refrenceModel' => 'ClientQuestionnaireTemplate', 'referenceId' => $responseId]);

                    if ($respoonse->id) {
                        return response()->json(['message' => 'Data saved succesfully.'], 201);
                    } else {
                        return response()->json(['message' => 'Unable to create form, please try gain later'], 500);
                    }
                } else {
                    return response()->json(['message' => 'Unable to save data , please try again later'], 500);
                }
            } else {
                return response()->json(['message' => 'Form not found'], 404);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function getQuestionnaireScrore($id, $customFormId, $customerId)
    {
        try {
            // echo $id.'++'.$customFormId.'++'.$customerId;
            $templateDetail = QuestionnaireTemplate::where('udid', $id)->first();
            $objData = array();
            if (isset($templateDetail->questionnaireTemplateId)) {
                $templateId = $templateDetail->questionnaireTemplateId;
                $assigned = ClientQuestionnaireAssign::where('questionnaireTemplateId', $templateId)->where('customFormId', $customFormId)->where('referenceId', $customerId)->orderBy('clientQuestionnaireAssignId', 'desc')->first();

                if (isset($assigned->clientQuestionnaireAssignId)) {
                    $template = ClientQuestionnaireTemplate::where("clientQuestionnaireAssignId", $assigned->clientQuestionnaireAssignId)->first();
                    if (isset($template->udid)) {
                        $objData = (new ClientQuestionnaireService)->getQuestionnaireTemplateScore($template->udid);
                    }
                }
            }

            return $objData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function save_response_template_question_section_updated($response, $formId, $templateId)
    {
        try {
            $request = new \Illuminate\Http\Request($response);
            $templateDetail = QuestionnaireTemplate::where('questionnaireTemplates.udid', $templateId)->first();
            $clientQuestionnaire = ClientQuestionnaireAssign::where('questionnaireTemplateId', $templateDetail->questionnaireTemplateId)->where('customFormId', $formId)->orderBy("clientQuestionnaireAssignId", 'desc')->first();
            $objData = (new ClientQuestionnaireService)->addQuestionnaireTemplateByUsers($request, $clientQuestionnaire->udid);
            //  print_r(  $objData);
            $responseData = $objData->getData();
            $responseId = $responseData->id ?? '';
            return $responseId;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function get_steps_forms($id)
    {
        try {
            $patient = Patient::where('udid', $id)->first();
            if (isset($patient->id) && !empty($patient->id)) {
                $userId = $patient->userId;
                $assigned = DummySteps::where('userId', $userId)->with('assignedForms')->get();
                $customformsIds = array();
                $customformsdata = array();
                $response = array();
                foreach ($assigned as $key => $assign) {
                    $customformsIds[$key] = $assign->assignedForms->customFormId;
                }

                $allForms = CustomForms::whereIn('id', $customformsIds)->where('status', 1)->whereNull('deletedAt')->get();
                foreach ($allForms as $key => $form) {
                    $customformsdata[$form->id]['name'] = $form->formName;
                    $customformsdata[$form->id]['udid'] = $form->udid;
                }
                $processStatus = array();
                // print_r($assigned );
                foreach ($assigned as $key => $assign) {
                    if ($key == 0 && $assign->status == 0) {
                        $processStatus[$key] = 'process';
                    } elseif ($key == 0 && $assign->status == 1) {
                        $processStatus[$key] = 'completed';
                    } else {
                        $index = $key - 1;
                        if ($assigned[$index]->status == 0) {
                            $processStatus[$key] = 'pending';
                        } elseif ($assigned[$index]->status == 1 && $assign->status == 1) {
                            $processStatus[$key] = 'completed';
                        } elseif ($assigned[$index]->status == 1 && $assign->status == 0) {
                            $processStatus[$key] = 'process';
                        }
                    }
                }

                // print_r($processStatus); die;

                foreach ($assigned as $key => $assign) {
                    //  print_r($assign); die;
                    $response[$key]['assignedUdid'] = $assign->assignedForms->udid;
                    $response[$key]['formName'] = $customformsdata[$assign->assignedForms->customFormId]['name'];
                    $response[$key]['formUdid'] = $customformsdata[$assign->assignedForms->customFormId]['udid'];
                    $response[$key]['status'] = $assign->status;
                    $response[$key]['process'] = $processStatus[$key];
                }
                return response()->json(['data' => $response], 200);
                /*if (0 < $assigned->count()) {
                    return fractal()->collection($assigned)->transformWith(new CustomFormsAssignedTransformer(false))->toArray();
                } else {
                    return response()->json(['message' => 'No form assigned to this user'], 200);
                }*/
            } else {
                return response()->json(['message' => 'Patient not found'], 404);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function updateStep($assignedId, $userId)
    {
        try {
            $step = WorkFlowQueueStepAction::where('customFormAssignedId', $assignedId)->first();
            if (isset($step->workFlowQueueStepActionId)) {
                WorkFlowQueueStepAction::where('workFlowQueueStepActionId', $step->workFlowQueueStepActionId)->update(['status' => 1]);
            }
            /*WorkFlowQueueStepAction::where('workFlowQueueStepActionId',$flow->workFlowQueueStepActionId)->update(['customFormAssignedId'=> $assignId,'assignStatus'=>1]);
             $assigned =   DummySteps::where('userId', $userId)->where('customFormAssignedId', $assignedId)->first();
             if (isset($assigned->id)) {
                 $assigned->status = 1;
                 $assigned->save();
             }*/
            return true;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function getActionScore($patientUdid)
    {
        try {
            $patient = Patient::where('udid', $patientUdid)->first();
            if (isset($patient->id) && !empty($patient->id)) {
                $userId = $patient->userId;
                $assigned = DummySteps::where('userId', $userId)->with('assignedForms')->get();
                $customformsIds = array();
                $customformsdata = array();
                $response = array();
                foreach ($assigned as $key => $assign) {
                    $customformsIds[$key] = $assign->assignedForms->customFormId;
                }

                $allForms = CustomForms::whereIn('id', $customformsIds)->where('status', 1)->whereNull('deletedAt')->get();
                foreach ($allForms as $key => $form) {
                    $customformsdata[$key]['id'] = $form->udid;
                    $customformsdata[$key]['name'] = $form->formName;
                }

                foreach ($customformsdata as $key => $customForm) {
                    $data = $this->getResponseData($customForm['id'], $patientUdid);
                    $responseData = $data->getData();
                    $response[$key]['name'] = $customForm['name'];
                    $response[$key]['columns'] = $responseData->data->columns;
                    $response[$key]['values'] = $responseData->data->values;
                }
                if (count($response) > 0) {
                    return response()->json(['data' => $response], 200);
                } else {
                    return response()->json(['message' => 'No result found'], 200);
                }
            } else {
                return response()->json(['message' => 'Patient not found'], 404);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function addressHtml()
    {
        try {
            $fields[0]['name'] = 'Country';
            $fields[0]['type'] = 'text';
            $fields[0]['required'] = 1;
            $fields[0]['order'] = 1;
            $fields[0]['properties'] = array('class' => "country", 'placeholder' => 'Country', 'label' => 'Country');

            $fields[1]['name'] = 'State';
            $fields[1]['type'] = 'text';
            $fields[1]['required'] = 1;
            $fields[1]['order'] = 2;
            $fields[1]['properties'] = array('class' => "state", 'placeholder' => 'State / Province / Region', 'label' => 'State / Province / Region');

            $fields[2]['name'] = 'City';
            $fields[2]['type'] = 'text';
            $fields[2]['required'] = 1;
            $fields[2]['order'] = 3;
            $fields[2]['properties'] = array('class' => "city", 'placeholder' => 'City Name', 'label' => 'City');

            $fields[3]['name'] = 'ZipCode';
            $fields[3]['type'] = 'text';
            $fields[3]['required'] = 1;
            $fields[3]['order'] = 4;
            $fields[3]['properties'] = array('class' => "zip", 'placeholder' => 'ZIP/Postal Code', 'label' => 'ZIP/Postal Code');

            $fields[4]['name'] = 'Address 1';
            $fields[4]['type'] = 'text';
            $fields[4]['category'] = 'address';
            $fields[4]['required'] = 1;
            $fields[4]['order'] = 5;
            $fields[4]['properties'] = array('class' => "address", 'placeholder' => 'Address Line 1', 'label' => 'Address Line 1');

            $fields[5]['name'] = 'Address 2';
            $fields[5]['type'] = 'text';
            $fields[5]['required'] = 1;
            $fields[5]['order'] = 6;
            $fields[5]['properties'] = array('class' => "appartment", 'placeholder' => 'Address Line 2', 'label' => 'Address Line 2');
            return $fields;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function addressColumns()
    {
        try {
            $fields[0]['title'] = 'Country';
            $fields[0]['dataIndex'] = 'country';
            $fields[0]['order'] = 1;
            $fields[1]['title'] = 'State';
            $fields[1]['dataIndex'] = 'state';
            $fields[1]['order'] = 2;
            $fields[2]['title'] = 'City';
            $fields[2]['dataIndex'] = 'city';
            $fields[2]['order'] = 3;
            $fields[3]['title'] = 'ZipCode';
            $fields[3]['dataIndex'] = 'zipcode';
            $fields[3]['order'] = 4;
            $fields[4]['title'] = 'Address 1';
            $fields[4]['dataIndex'] = 'address 1';
            $fields[4]['order'] = 5;
            $fields[5]['title'] = 'Address 2';
            $fields[5]['dataIndex'] = 'address 2';
            $fields[5]['order'] = 6;
            return $fields;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function assignWorkflowForm($formUdId, $patientId)
    {
        try {
            $formDetail = CustomForms::where('udid', $formUdId)->with('fields')->first();
            if (isset($formDetail->id) && $formDetail->id) {
                $forms = json_decode($formDetail->formFields);
                $questionnaireTemplate = 0;
                $templateIds = array();
                $questionareData = array();
                foreach ($forms as $key => $form) :
                    if ($form->type == 'questionnaire') {
                        $questionnaireTemplate = 1;
                        $questionareData[] = $form->id;
                    }
                endforeach;
                if (isset($questionareData) && count($questionareData) > 0) {
                    $templateDetails = QuestionnaireTemplate::whereIn('udid', $questionareData)->get();
                    foreach ($templateDetails as $tempDetail) {
                        $questionnaireTemplate = 1;
                        $templateIds[] = $tempDetail->questionnaireTemplateId;
                    }
                }
                $patients[] = $patientId;
                $patientsUdiD = array();
                $saveData = array();
                $assignTemplate = array();
                if (count($patients) > 0) {
                    $alreadyAssignStatus = $this->checkAlraedyAssignStatus($patients, $formDetail->id);

                    if (count($alreadyAssignStatus) > 0) {
                        $count = 0;

                        $saveData['udid'] = Str::uuid()->toString();
                        $saveData['customFormId'] = $formDetail->id;
                        $saveData['userId'] = $alreadyAssignStatus[0];
                        $saveData['assignedBy'] = Auth::id();
                        $count++;
                    } else {
                        return response()->json(['message' => 'No user found to assign this form.'], 200);
                    }
                } else {
                    return response()->json(['message' => 'No user found to assign this form.'], 200);
                }
                if (!empty($saveData)) {
                    $saved = customFormAssignedToUser::create($saveData);
                }
                if ($questionnaireTemplate == 1 && count($templateIds) > 0) {  /// Assign Template
                    $count = 0;
                    foreach ($alreadyAssignStatus as $key => $user) :
                        // To assign Questionnaire template
                        foreach ($templateIds as $templateId) :
                            $assignTemplate[$count]["questionnaireTemplateId"] = $templateId;
                            $assignTemplate[$count]["referenceId"] = $user;
                            $assignTemplate[$count]["entityType"] = 247; // Patient for now
                            $assignTemplate[$count]['udid'] = Str::uuid()->toString();
                            $assignTemplate[$count]['createdBy'] = Auth::id();
                            $assignTemplate[$count]['customFormId'] = $formDetail->id;;

                            $count++;
                        endforeach;
                    endforeach;

                    if (!empty($assignTemplate)) {
                        $QuestionnaireSaved = ClientQuestionnaireAssign::insert($assignTemplate);
                    }
                }
                if (isset($saved) && $saved)
                    return $saved->id;
                else
                    return 0;
            } else {
                return 0;
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function get_assigned_workflow($id)
    {
        try {
            $patient = Patient::where('udid', $id)->first();
            if (isset($patient->id) && !empty($patient->id)) {
                $userId = $patient->userId;
                $workflowDetail = WorkFlowQueue::where('keyId', $userId)->orderBy('workFlowQueueId', 'desc')->first();
                if (isset($workflowDetail->workFlowQueueId) && !empty($workflowDetail->workFlowQueueId)) {
                    $assigned = WorkFlowQueueStepAction::where('workFlowQueueStepId', $workflowDetail->workFlowQueueId)
                        ->where('assignStatus', 1)
                        ->with('assignedForms')->get();
                    $customformsIds = array();
                    $customformsdata = array();
                    $response = array();
                    foreach ($assigned as $key => $assign) {
                        $customformsIds[$key] = $assign->assignedForms->customFormId;
                    }
                    $allForms = CustomForms::whereIn('id', $customformsIds)->where('status', 1)->whereNull('deletedAt')->get();
                    foreach ($allForms as $key => $form) {
                        $customformsdata[$form->id]['name'] = $form->formName;
                        $customformsdata[$form->id]['udid'] = $form->udid;
                    }
                    $processStatus = array();
                    // print_r($assigned );
                    foreach ($assigned as $key => $assign) {
                        if ($key == 0 && $assign->status == 0) {
                            $processStatus[$key] = 'process';
                        } elseif ($key == 0 && $assign->status == 1) {
                            $processStatus[$key] = 'completed';
                        } else {
                            $index = $key - 1;
                            if ($assigned[$index]->status == 0) {
                                $processStatus[$key] = 'pending';
                            } elseif ($assigned[$index]->status == 1 && $assign->status == 1) {
                                $processStatus[$key] = 'completed';
                            } elseif ($assigned[$index]->status == 1 && $assign->status == 0) {
                                $processStatus[$key] = 'process';
                            }
                        }
                    }

                    // print_r($processStatus); die;

                    foreach ($assigned as $key => $assign) {
                        //  print_r($assign); die;
                        $response[$key]['assignedUdid'] = $assign->assignedForms->udid;
                        $response[$key]['formName'] = $customformsdata[$assign->assignedForms->customFormId]['name'];
                        $response[$key]['formUdid'] = $customformsdata[$assign->assignedForms->customFormId]['udid'];
                        $response[$key]['status'] = $assign->status;
                        $response[$key]['process'] = $processStatus[$key];
                        $response[$key]['assignDate'] = strtotime($assign->assignedForms->createdAt);
                    }
                    return response()->json(['data' => $response], 200);
                }
            } else {
                return response()->json(['message' => 'Patient not found'], 404);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function getCountryState()
    {
        try {
            $response = array();
            $codes = GlobalCode::where('globalCodeCategoryId', 21)->Orwhere('globalCodeCategoryId', 20)->get();
            foreach ($codes as $key => $code) {
                $response[$code->id] = $code->name;
            }
            return $response;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
