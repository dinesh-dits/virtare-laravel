<?php

namespace App\Transformers\CareTeamTransformer;

use App\Models\Client\Site\Site;
use League\Fractal\TransformerAbstract;
use App\Transformers\AssignProgram\AssignProgramTransformer;

class CareTeamMemberTransformer extends TransformerAbstract
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
        $siteHead = 0;
        if (isset($data->careTeam->site->siteHead) && $data->careTeam->site->siteHead == $data->contactId) {
            $siteHead = 1;
        }
        return [
            'udid' => $data->udid,
            'isHead' => $data->isHead,
            'isSiteHead' => $siteHead,
            'contactPerson' => (!empty($data->contact)) ? fractal()->item($data->contact)->transformWith(new CareTeamMemberStaffTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : '',
        ];
    }
}
