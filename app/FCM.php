<?php

namespace App;

use Exception;
use GuzzleHttp\Client;

class FCM
{

    protected $endpoint;
    protected $deviceId;
    protected $data;
    protected $notification;

    public function __construct()
    {
        $this->endpoint = "https://fcm.googleapis.com/fcm/send";
    }

    public function setEndPoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }

    public function data(array $data = [])
    {

        $this->data = $data;
    }

    public function deviceId(array $deviceId)
    {
        $this->deviceId = $deviceId;
    }

    public function notifications(array $notification = [])
    {
        $this->notification = $notification;
    }

    public function send()
    {
        $serverKey = env("FCM_SERVER_KEY");
        $headers = [
            'Authorization' => 'key=' . $serverKey,
            'Content-Type' => 'application/json',
        ];
        $fields = [
            'registration_ids' => $this->deviceId,
            'content-available' => true,
            'priority' => 'high',
            'notification' => $this->notification,
            'data' => $this->data,
        ];
        $fields = json_encode($fields);
        $client = new Client();
        try {
            $request = $client->post($this->endpoint, [
                'headers' => $headers,
                "body" => $fields,
            ]);
            $response = $request->getBody()->getContents();
            return $response;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
