<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\Screen\Screen;
use App\Models\ToolTip\Form;
use App\Models\ToolTip\FormLable;
use App\Transformers\BugReport\ScreenTransformer;
use App\Transformers\ToolTip\FormLableTransformer;
use App\Transformers\ToolTip\FormTransformer;
use App\Transformers\ToolTip\ToolTipScreenTransformer;

class ToolTipService
{
    //Tool Tip listing
    public function tooltipListing($request)
    {

        try {
            if ($request->refrenceId && $request->refrenceType) {
                $data = FormLable::with('types')->where([['refrenceId', $request->refrenceId], ['refrenceType', $request->refrenceType]])->get();
                return fractal()->collection($data)->transformWith(new FormLableTransformer())->toArray();
            } else {
                $data = Form::with('formLable')->get();
                return fractal()->collection($data)->transformWith(new ToolTipScreenTransformer())->toArray();
                // $data = FormLable::with('types')->get();
                // return fractal()->collection($data)->transformWith(new FormLableTransformer())->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    //Form Listing
    public function formList($request)
    {
        try {
            if ($request->screenId) {
                $data = Form::where('screenId', $request->screenId)->get();
                return fractal()->collection($data)->transformWith(new FormTransformer())->toArray();
            } else {
                $data = Form::all();
                return fractal()->collection($data)->transformWith(new FormTransformer())->toArray();
            }

        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    //Add Tool Tip
    public function addToolTip($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $detail = $request->input('tooltip');
            foreach ($detail as $value) {
                $input = [
                    'udid' => Str::uuid()->toString(),
                    'refrenceId' => $id,
                    'name' => $value['name'],
                    'lableType' => $value['lableType'],
                    'type' => $value['type'],
                    'refrenceType' => $value['refrenceType'],
                    'description' => $value['description'],
                    'providerId' => $provider,
                    'providerLocationId' => $providerLocation
                ];
                FormLable::create($input);
            }
            return response()->json(['message' => trans('messages.createdSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    //Update Tool Tip
    public function updateToolTip($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            //$detail = $request->input('tooltip');
            //foreach($detail AS $value){
            $input = array();
            if (!empty($request->name)) {
                $input['name'] = $request->name;
            }
            if (!empty($request->lableType)) {
                $input['lableType'] = $request->lableType;
            }
            if (!empty($request->type)) {
                $input['type'] = $request->type;
            }
            if (!empty($request->refrenceType)) {
                $input['refrenceType'] = $request->refrenceType;
            }
            if (!empty($request->description)) {
                $input['description'] = $request->description;
            }
            if (!empty($provider)) {
                $input['providerId'] = $provider;
            }
            if (!empty($providerLocation)) {
                $input['providerLocationId'] = $providerLocation;
            }
            if (!empty($input)) {
                FormLable::where('id', $id)->update($input);
            }
            //}
            return response()->json(['message' => trans('messages.updatedSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
