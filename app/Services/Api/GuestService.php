<?php

namespace App\Services\Api;

use Exception;
use App\Models\Guest\Guest;
use Illuminate\Support\Str;
use App\Transformers\Guest\GuestTransformer;
use App\Helper;

class GuestService
{
    // Add Guest
    public function addGuest($request)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $input = [
                'udid' => Str::uuid()->toString(), 'conferenceId' => $request->conference, 'name' => $request->name, 'email' => $request->email,
                'providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
            ];
            $guest = Guest::create($input);
            $data = Guest::where('id', $guest->id)->first();
            Helper::updateFreeswitchUser();
            return fractal()->item($data)->transformWith(new GuestTransformer)->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
