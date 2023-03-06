<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\Api\CustomFormService;
use App\Http\Requests\CustomForm\CustomFormRequest;
use App\Http\Requests\CustomForm\CustomFormDataRequest;
use App\Http\Requests\CustomForm\CustomFormAssignRequest;
use App\Models\GlobalCode\GlobalCodeCategory;
use App\Models\CustomForm\CustomFields;
use App\Models\CustomForm\CustomForms;


class CustomFormController extends Controller
{
    //
    public function saveForm(CustomFormRequest $request)
    {
        try {
            return (new CustomFormService)->create_form($request);            
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function getAllForms()
    {
        try {
            return (new CustomFormService)->getAllForms();            
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function FormDetail($id=NULL)
    {
        try {
            if($id){
                return (new CustomFormService)->getFormDetail($id);    
            }else{
                return response()->json(['message' => 'Required field missing'], 422);
            }                   
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function deleteForm($id=NULL)
    {
        try {
            if($id){
                return (new CustomFormService)->deleteForm($id);    
            }else{
                return response()->json(['message' => 'Required field missing'], 422);
            }                   
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function saveFormData(CustomFormDataRequest $request){
        try {
            return (new CustomFormService)->saveFormData($request);            
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function getResponseData($id=NULL,$userId=NULL)
    {
        try {
            if($id){
               
                return (new CustomFormService)->getResponseData($id,$userId);    
            }else{
                return response()->json(['message' => 'Required field missing'], 422);
            }                   
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function assignForm(CustomFormAssignRequest $request){
        try{            
            return (new CustomFormService)->assignForm($request);    
        }catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

    }

    public function getAssignedForm($id){
        try {
            if($id){
                return (new CustomFormService)->getAssignedForm($id);    
            }else{
                return response()->json(['message' => 'Required field missing'], 422);
            }                   
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

    }

    public function get_custom_templates(){
        try{
            return (new CustomFormService)->get_custom_templates();  
        }catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getTemplateDetail($id){
        try{
            if($id){
                return (new CustomFormService)->getTemplateDetail($id);    
            }else{
                return response()->json(['message' => 'Required field missing'], 422);
            }  

        }catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    
    public function getTemplateQuestionSection($id){
        try{
            if($id){
                return (new CustomFormService)->getTemplateQuestionSection($id);    
            }else{
                return response()->json(['message' => 'Required field missing'], 422);
            } 

        }catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function save_response_template_question_section(CustomFormDataRequest $request){
        try{
            return (new CustomFormService)->save_response_template_question_section($request);   
        }catch(Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function get_steps_forms($id){

        try {
            if($id){
                return (new CustomFormService)->get_steps_forms($id);    
            }else{
                return response()->json(['message' => 'Required field missing'], 422);
            }                   
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

    }

    public function get_steps_score($id){

        try {
            if($id){
                return (new CustomFormService)->getActionScore($id);    
            }else{
                return response()->json(['message' => 'Required field missing'], 422);
            }                   
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

    }

    public function get_assigned_workflow($id){
        try {
            if($id){
                return (new CustomFormService)->get_assigned_workflow($id);    
            }else{
                return response()->json(['message' => 'Required field missing'], 422);
            }                   
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        } 
    }
    
    public function getCustomFields()
    {

        $fields = array();
        try {
            $result = CustomFields::where('isActive', 1)->whereNull('deletedAt')->get();
            if ($result->count() > 0) {
                $fields = array();
                $index=0;
                $cat='';
                foreach ($result as $key => $value) :
                    if($cat != $value->category){
                        $index=0;
                    }
                    $fields[$value->category][$index]['udid'] = $value->udid;
                    $fields[$value->category][$index]['name'] = $value->name;
                    $fields[$value->category][$index]['type'] = $value->type;
                    $fields[$value->category][$index]['required'] = $value->required;
                    $fields[$value->category][$index]['properties'] = json_decode($value->properties);
                    $cat = $value->category;
                    $index++;
                endforeach;
            } else {
                $fields = 'No active record found';
            }

            /*$fields[0]['name'] ='Email';
            $fields[0]['udid'] =Str::uuid()->toString();
            $fields[0]['type'] ='email';
            $fields[0]['required'] =1;
            $fields[0]['properties'] =json_encode(array('class'=>"email_box",'placeholder'=>'Input email address'));

           

         

            $fields[1]['name'] ='Input Box';
            $fields[1]['udid'] = Str::uuid()->toString();
            $fields[1]['type'] ='text';
            $fields[1]['required'] =1;
            $fields[1]['properties'] =json_encode(array('class'=>"text_box",'placeholder'=>'Input email address'));

         

            $fields[2]['name'] ='Phone Number';
            $fields[2]['udid'] =Str::uuid()->toString();
            $fields[2]['type'] ='tel';
            $fields[2]['required'] =1;
            $fields[2]['properties'] =json_encode(array('class'=>"phone_number_box",'placeholder'=>'123-45-678','pattern'=>"[0-9]{3}-[0-9]{2}-[0-9]{3}"));

            $fields[3]['name'] ='Checkbox';
            $fields[3]['type'] ='checkbox';
            $fields[3]['udid'] =Str::uuid()->toString();
            $fields[3]['required'] =1;
            $fields[3]['properties'] =json_encode(array('class'=>"confirm_box"));

            $fields[4]['name'] ='Date & Time';
            $fields[4]['type'] ='date';
            $fields[4]['udid'] =Str::uuid()->toString();
            $fields[4]['required'] =1;
            $fields[4]['properties'] =json_encode(array('class'=>"date_box"));

            $fields[5]['name'] ='Upload File';
            $fields[5]['type'] ='file';
            $fields[5]['udid'] =Str::uuid()->toString();
            $fields[5]['required'] =1;
            $fields[5]['properties'] =json_encode(array('class'=>"file_box"));

           
            $fields[0]['udid'] =Str::uuid()->toString();
            $fields[0]['name'] ='address_line_1';
            $fields[0]['type'] ='text';
            $fields[0]['category'] ='address';
            $fields[0]['required'] =1;
            $fields[0]['properties'] =json_encode(array('class'=>"address_line_1",'placeholder'=>'Address Line 1','label'=>'Address Line 1'));

            $fields[1]['udid'] =Str::uuid()->toString();
            $fields[1]['name'] ='address_line_2';
            $fields[1]['type'] ='text';
            $fields[1]['required'] =1;
            $fields[1]['properties'] =json_encode(array('class'=>"address_line_2",'placeholder'=>'Address Line 2','label'=>'Address Line 2'));
            $fields[1]['category'] ='address';

            $fields[2]['udid'] =Str::uuid()->toString();
            $fields[2]['name'] ='city';
            $fields[2]['type'] ='text';
            $fields[2]['required'] =1;
            $fields[2]['properties'] =json_encode(array('class'=>"city",'placeholder'=>'City Name','label'=>'City'));
            $fields[2]['category'] ='address';

            $fields[3]['udid'] =Str::uuid()->toString();
            $fields[3]['name'] ='state';
            $fields[3]['type'] ='text';
            $fields[3]['required'] =1;
            $fields[3]['properties'] =json_encode(array('class'=>"state",'placeholder'=>'State / Province / Region','label'=>'State / Province / Region'));
            $fields[3]['category'] ='address';

            $fields[4]['udid'] =Str::uuid()->toString();
            $fields[4]['name'] ='zip';
            $fields[4]['type'] ='text';
            $fields[4]['required'] =1;
            $fields[4]['properties'] =json_encode(array('class'=>"zip",'placeholder'=>'ZIP/Postal Code','label'=>'ZIP/Postal Code'));
            $fields[4]['category'] ='address';

            $fields[5]['udid'] =Str::uuid()->toString();
            $fields[5]['name'] ='country';
            $fields[5]['type'] ='text';
            $fields[5]['required'] =1;
            $fields[5]['properties'] =json_encode(array('class'=>"country",'placeholder'=>'Country','label'=>'Country'));
            $fields[5]['category'] ='address';
            
            $conversation = CustomFields::insert($fields);
            die('STOP');*/



            /*   $allcodes = GlobalCodeCategory::select('udid','name')->where('isActive',1)->whereNull('deletedAt')->whereRaw('udid <> ""')->get();
           if($allcodes->count()>0){
            $allcodes = $allcodes->toArray();
                $fields[6]['name'] ='Global Codes';
                $fields[6]['type'] ='global_codes';
                $fields[6]['required'] =array('yes','no');
                $fields[6]['properties'] =array('class'=>"global_codes");
                $fields[6]['values'] = $allcodes;
           }*/



            return response()->json(['data' =>  $fields], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
