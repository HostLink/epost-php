<?php

use EPost\SMS;
use GQL\Client;

/**
 * @property GQL\Client $client
 */
class EPost
{

    public $client;

    public function __construct(string $key)
    {
        $this->key = $key;

        $this->client = new Client("https://api.e-post.com.hk/v4/", ["verify" => false]);
        $this->client->token = $key;
    }

    public function sendSMS(SMS $sms): int
    {
        $resp = $this->client->subscription("sendSMS", [
            "phone" => $sms->phone,
            "content" => $sms->content
        ]);

        if ($resp["error"]) {
            throw new Exception($resp["error"]["message"]);
        }

        return $resp["data"]["sendSMS"][0];
    }

    public function getSMSReport(string $start, string $end)
    {
        $resp = $this->client->query([
            "SMS" => [
                "__args" => [
                    "filter" => [
                        "created_time" => [
                            "between" => [$start . " 00:00:00", $end . " 23:59:59"]
                        ]
                    ]
                ],
                "list" => [
                    "sms_id",
                    "phone",
                    "content",
                    "send_time",
                    "no_of_msg",
                    "receive_time"
                ]
            ]
        ]);

        return $resp["data"]["SMS"]["list"];
    }
}
