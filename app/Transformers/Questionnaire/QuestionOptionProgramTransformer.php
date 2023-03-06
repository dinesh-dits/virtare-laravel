<?php

namespace App\Transformers\Questionnaire;

use App\Models\Program\Program;
use Illuminate\Support\Facades\DB;
use League\Fractal\TransformerAbstract;


class QuestionOptionProgramTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        //
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        //
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */

    public function transform($data)
    {
        $programName = "";
        $programObj = array();
        if(isset($data->program->name)){
            $programName = $data->program->name;
        }else{
            $programObj = Program::where("id",$data->programId)->first();
            if(!empty($programObj) &&  count($programObj) > 0){
                $programName = $programObj->name;
            }
        }


        return [
            'id' => $data->udid,
            'programId' => $data->programId,
            'program' => $programName,
            'score' => (!empty($data->score))?$data->score->score:''
        ];
    }
}
