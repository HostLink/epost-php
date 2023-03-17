<?php
error_reporting(~E_WARNING & ~E_NOTICE);

use EPost\SMS;
use GQL\Builder;
use GQL\Client;

/**
 * @property GQL\Client $client
 */
class EPost
{

    public $client;
    public $key;

    public function __construct(string $key)
    {
        $this->key = $key;

        $this->client = new Client("https://api.e-post.com.hk/v4/", ["verify" => false]);
        $this->client->token = $key;
    }

    public function getSMSExpiryDate()
    {
        $resp = $this->client->query([
            "me" => [
                "SMSQuota" => [
                    "expiry_date"
                ]
            ]
        ]);
        return $resp["data"]["me"]["SMSQuota"]["expiry_date"];
    }

    public function getSMSQuota(): int
    {
        $resp = $this->client->query([
            "me" => [
                "SMSQuota" => [
                    "quota"
                ]
            ]
        ]);
        return $resp["data"]["me"]["SMSQuota"]["quota"];
    }

    public function sendSMS(SMS $sms): int
    {
        $resp = $this->client->subscription("sendSMS", [
            "__args" => [
                "phone" => $sms->phone,
                "content" => $sms->content,
                "country_code" => $sms->country_code
            ]
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
                    "status",
                    "receive_status",
                    "receive_time",
                ]
            ]
        ]);

        return $resp["data"]["SMS"]["list"];
    }
}
