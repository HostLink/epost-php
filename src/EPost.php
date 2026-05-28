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
            "mySMSQuota" => ["expiry_date" => true],
        ]);
        return $resp["data"]["mySMSQuota"]["expiry_date"] ?? null;
    }

    public function getSMSQuota(): int
    {
        $resp = $this->request("query", [
            "mySMSQuota" => ["quota" => true],
        ]);
        return (int)($resp["data"]["mySMSQuota"]["quota"] ?? 0);
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
                "data" => [
                    "sms_id"       => true,
                    "phone"        => true,
                    "content"      => true,
                    "send_time"    => true,
                    "no_of_msg"    => true,
                    "receive_time" => true,
                    "result"       => true,
                ],
            ],
        ]);

        $rows = $resp["data"]["listSMS"]["data"] ?? [];
        foreach ($rows as &$row) {
            $result = $row["result"] ?? "";
            if (is_numeric($result)) {
                $row["status"] = "Sent";
            } else {
                $row["status"] = ucwords(str_replace("_", " ", $result));
            }
            unset($row["result"]);
        }
        return $rows;
    }

    public function getSMSReportFilter(string $start, string $end, string $phone = "", string $content = ""): array
    {
        $filters = [
            "created_time" => [
                "between" => [$start . " 00:00:00", $end . " 23:59:59"],
            ],
        ];
        if ($phone !== "") {
            $filters["phone"] = ["like" => "%" . $phone . "%"];
        }
        if ($content !== "") {
            $filters["content"] = ["like" => "%" . $content . "%"];
        }

        $resp = $this->request("query", [
            "listSMS" => [
                "__args" => ["filters" => $filters],
                "data" => [
                    "sms_id"       => true,
                    "phone"        => true,
                    "content"      => true,
                    "send_time"    => true,
                    "no_of_msg"    => true,
                    "receive_time" => true,
                    "result"       => true,
                ],
            ],
        ]);

        $rows = $resp["data"]["listSMS"]["data"] ?? [];
        foreach ($rows as &$row) {
            $result = $row["result"] ?? "";
            if (is_numeric($result)) {
                $row["status"] = "Sent";
            } else {
                $row["status"] = ucwords(str_replace("_", " ", $result));
            }
            unset($row["result"]);
        }
        return $rows;
    }
}
