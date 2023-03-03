<?php

namespace App\Transformers\Escalation;

use App\Helper;
use League\Fractal\TransformerAbstract;
use App\Transformers\Staff\StaffDetailTransformer;
use App\Transformers\Patient\PatientBasicDetailTransformer;
use App\Transformers\Escalation\EscalationDetailTransformer;
use App\Transformers\Escalation\EscalationAssignToTransformer;


class EscalationTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        return [
            'id' => $data->udid,
            'patient' => $data->patient ? fractal()->item($data->patient)->transformWith(new PatientBasicDetailTransformer(false))->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : "",
            'referenceId' => $data->referenceId ? $data->referenceId : "",
            'entityType' => $data->entityType ? $data->entityType : "",
            'type' => $data->type ? $data->type->name : "",
            'typeId' => $data->type ? $data->type->id : "",
            'color' => $data->type ? $data->type->color : "",
            'reason' => $data->reason ? $data->reason : "",
            'status' => $data->status ? $data->status->name : "",
            'statusId' => $data->status ? $data->status->id : "",
            'createdAt' => strtotime($data->createdAt),
            'isActive' => $data->isActive ? true : false,
            'assignedBy' => (!empty($data->createdByName->staff)) ? fractal()->item($data->createdByName->staff)->transformWith(new StaffDetailTransformer(false))->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : "",
            'escalationAssignTo' => $data->assign ? fractal()->collection($data->assign)->transformWith(new EscalationAssignToTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : "",
            'escalationDetail' => $data->detail ? fractal()->collection($data->detail)->transformWith(new EscalationDetailTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : "",
            'escalationAction' => $data->escalationAction ? fractal()->collection($data->escalationAction)->transformWith(new EscalationActionTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : "",
            'escalationClose' => $data->escalationClose ? fractal()->item($data->escalationClose)->transformWith(new EscalationCloseTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : "",
            'escalationAuditDescription' => $data->escalationAuditDescription ? fractal()->item($data->escalationAuditDescription)->transformWith(new EscalationAuditDescriptionTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : "",
        ];
    }
}
