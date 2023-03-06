<?php

namespace App\Transformers\Dashboard;

use Illuminate\Support\Str;
use App\Models\Dashboard\Timezone;
use League\Fractal\TransformerAbstract;


class TimezoneTransformer extends TransformerAbstract
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
    public function transform($data): array
    {
        return [
            'id' => $data['udid'],
            'abbr' => $data['abbr'],
            'countryCode' => $data['countryCode'],
            'timeZone' => $data['abbr'].' — '.$data['timeZone'].' ('.$data['UTCOffset'].')',
            'UTCOffset' => $data['UTCOffset'],
            'UTCDSTOffset' => $data['UTCDSTOffset'],
            'isActive' => $data['isActive']?True:False,
        ];
    }
}
