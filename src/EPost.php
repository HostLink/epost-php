<?php

use EPost\SMS;

class EPost
{
    private $key;
    private $endpoint;

    public function __construct(string $key, string $endpoint = "https://app.e-post.com.hk/api/")
    {
        $this->key = $key;
        $this->endpoint = $endpoint;
    }

    private function request(string $type, array $body): array
    {
        $gql = $type . ' { ' . array_to_gql($body) . ' }';

        $ch = curl_init($this->endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode(["query" => $gql]),
            CURLOPT_HTTPHEADER     => [
                "Content-Type: application/json",
                "Accept: application/json",
                "Authorization: Bearer {$this->key}",
            ],
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?? [];
    }

    public function getSMSExpiryDate(): ?string
    {
        $resp = $this->request("query", [
            "getMySMSQuota" => ["expiry_date" => true],
        ]);
        return $resp["data"]["getMySMSQuota"]["expiry_date"] ?? null;
    }

    public function getSMSQuota(): int
    {
        $resp = $this->request("query", [
            "getMySMSQuota" => ["quota" => true],
        ]);
        return (int)($resp["data"]["getMySMSQuota"]["quota"] ?? 0);
    }

    public function sendSMS(SMS $sms): int
    {
        $phone = '+' . $sms->country_code . $sms->phone;
        $resp = $this->request("mutation", [
            "sendSMS" => [
                "__args" => [
                    "phone"   => $phone,
                    "content" => $sms->content,
                ],
            ],
        ]);

        if (!empty($resp["errors"])) {
            throw new \Exception($resp["errors"][0]["message"]);
        }

        return (int)$resp["data"]["sendSMS"];
    }

    public function getSMSReport(string $start, string $end): array
    {
        $resp = $this->request("query", [
            "listSMS" => [
                "__args" => [
                    "filters" => [
                        "created_time" => [
                            "between" => [$start . " 00:00:00", $end . " 23:59:59"],
                        ],
                    ],
                ],
                "list" => [
                    "sms_id"         => true,
                    "phone"          => true,
                    "content"        => true,
                    "created_time"   => true,
                    "no_of_msg"      => true,
                    "receive_status" => true,
                    "receive_time"   => true,
                ],
            ],
        ]);

        return $resp["data"]["listSMS"]["list"] ?? [];
    }
}
