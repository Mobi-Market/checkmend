# Introduction
This package provides the Checkmend Due Diligence and Make and Model Extended API Calls for use in MobiMarket applications.

# Installation
Add the following to your composer.json
```javascript
"repositories":[
    {
        "type": "vcs",
        "url" : "https://autumndev@bitbucket.org/autumndevops/checkmend.git"
    }
],
"minimum-stability": "dev"
```
Run ```composer update``` followed by ```compoaser dump```

## Publishing confirguration file
```
php artisan vendor:publish --provider=Autumndev\Checkmend\CheckmendServiceProvider
```

Update the settings int he configuration file located at: .\config\checkmend.php

#Usage

```php
$result = Checkmend::dueDiligence($imei);
// send certificate to email or url call back
Checkmend::getCertificate($result->certid, $url, $email);
$result = Checkmend::makeModelExt([$imei]);
```
