# epost-php

PHP client library for the [e-post](https://app.e-post.com.hk) SMS API.

## Requirements

- PHP >= 7.0
- cURL extension

## Installation

```bash
composer require hostlink/epost-php
```

## Usage

### Initialise

```php
require 'vendor/autoload.php';

$key = 'your_jwt_token'; // Bearer token from e-post
$epost = new EPost($key);
```

### Send SMS

```php
use EPost\SMS;

$sms = new SMS();
$sms->setPhone('93465221');    // 8-digit HK number (852 added automatically)
$sms->setContent('Hello');

// Optional: set a different country code (default: 852)
// $sms->setCountryCode('86');  // e.g. mainland China

$smsId = $epost->sendSMS($sms);
echo "Sent! sms_id: $smsId";
```

`sendSMS()` returns the `sms_id` (int) on success, or throws an `Exception` on error.

### Get SMS Report

```php
$from   = '2026-05-01';
$to     = '2026-05-28';
$report = $epost->getSMSReport($from, $to);

foreach ($report as $row) {
    echo $row['sms_id']       . ' | '
       . $row['phone']        . ' | '
       . $row['content']      . ' | '
       . $row['created_time'] . PHP_EOL;
}
```

Each record contains: `sms_id`, `phone`, `content`, `created_time`, `no_of_msg`, `receive_status`, `receive_time`.

### Get Quota & Expiry Date

```php
echo $epost->getSMSQuota();       // int   e.g. 1200
echo $epost->getSMSExpiryDate();  // string e.g. "2394-08-26"
```
