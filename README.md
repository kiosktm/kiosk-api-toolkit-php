# Kiosk API Toolkit for PHP

The Kiosk API Toolkit for PHP is designed to allow Lead Vendors for Kiosk 
to be able to submit Prospect records to the Kiosk Prospect API.  The toolkit 
handles the OAuth2 authentication process, reusing the Bearer Token where 
possible, and provides a simple function that accepts a Prospect record in 
array format and submits it over the API.  

## Sample Prospect Submission

```php
<?php

define('KIOSK_CLIENT_ID', '<CLIENT ID>');
define('KIOSK_CLIENT_SECRET', '<CLIENT_SECRET>');

require __DIR__.'/KioskApiClient.php';

$client = new KioskApiClient(KIOSK_CLIENT_ID, KIOSK_CLIENT_SECRET);

$prospect = array(
    'FirstName'         => 'John', 
    'LastName'          => 'Test', 
    'Email'             => 'test@kiosk.tm', 
    'Phone'             => '4155551234', 
    'ProgramOfInterest' => 'Math'
);

$result = $client->submitProspect($prospect);
```

## Sample Responses

The `submitProspect` method always returns an array with a "status" element.  
The "status" element with be either "ok" or "error".

### OK Response

Valid submissions with have a "status" of "ok" and will provide an "id" and 
"prospect_id".  It is critically important that these IDs are retained so 
that they can be used to investigate issues with missing leads.

```php
array(3) {
  ["status"]=>
  string(2) "ok"
  ["id"]=>
  string(40) "<CONVERSION ID>"
  ["prospect_id"]=>
  string(40) "<PROSPECT ID>"
}
```

### Invalid Response

Invalid submissions will have a "status" of "error" and will provide a 
"validation" element detailing which fields were valid (true) or invalid 
(false).

```php
array(4) {
  ["status"]=>
  string(5) "error"
  ["error"]=>
  string(15) "InvalidProspect"
  ["message"]=>
  string(123) "The Prospect you submitted had invalid field data.  Please check the "validation" element to see which fields were invalid."
  ["validation"]=>
  array(4) {
    ["ProgramOfInterest"]=>
    bool(false)
    ["Phone"]=>
    bool(true)
    ["Email"]=>
    bool(true)
    ["is_valid"]=>
    bool(false)
  }
}
```

