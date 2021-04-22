# epost-php

## Usage
```php
use EPost\SMS;

$sms = new SMS();
$sms->setPhone("27717387"); //no need add 852
$sms->setContent("Hello");

$key="..."; //get from hostlink
$epost = new EPost($key);
print_r($epost->sendSMS($sms));

```