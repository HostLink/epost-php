# epost-php

## Usage

### SMS
```php
use EPost\SMS;

$sms = new SMS();
$sms->setPhone("27717387"); //no need add 852
$sms->setContent("Hello");

$key="..."; //get from hostlink
$epost = new EPost($key);
print_r($epost->sendSMS($sms));

```

### get SMS report
```php
$sms = new SMS();
$from="2021-05-10";
$to="2021-05-15";
$report = $epost->getSMSReport($from,$to);
```

### get quota and expiry date
```php
$epost = new EPost($key);
echo $epost->getSMSQuota();
echo $epost->getSMSExpiryDate();
```