<?php

namespace EPost;

class SMS
{
    public $phone;
    public $content;

    public function setContent(string $content)
    {
        $this->content = $content;
    }

    public function setPhone(string $phone)
    {
        $this->phone = $phone;
    }
}
