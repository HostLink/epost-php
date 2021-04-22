<?php

use EPost\SMS;
use GQL\Client;

class EPost
{


    public Client $client;

    public function __construct(string $key)
    {
        $this->key = $key;

        $this->client = new Client("https://api.e-post.com.hk/v4?token=$key", [], ["verify" => false]);
    }

    public function sendSMS(SMS $sms)
    {
        $resp = $this->client->subscription("sendSMS", [
            "phone" => $sms->phone,
            "content" => $sms->content
        ]);

        if ($resp["error"]) {
            throw new Exception($resp["error"]["message"]);
        }

        return $resp["data"]["sendSMS"];
    }
}
