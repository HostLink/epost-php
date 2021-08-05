<?php

namespace EPost;

class SMS
{
    public $phone;
    public $content;
    public $country_code = "852";

    public function setCountryCode(string $country_code)
    {
        $this->country_code = $country_code;
    }

    public function setContent(string $content)
    {
        $this->content = $content;
    }

    public function setPhone(string $phone)
    {
        $this->phone = $phone;
    }
}
