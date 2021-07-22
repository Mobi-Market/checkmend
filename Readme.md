# Introduction
This package provides the Checkmend Due Diligence and Make and Model Extended API Calls for use in MobiMarket applications.

# Installation
Run
```sh
composer require mobi-market/checkmend
```

## Publishing configuration file
```
php artisan vendor:publish --provider=Autumndev\Checkmend\CheckmendServiceProvider
```

Update the settings int he configuration file located at: `.\config\checkmend.php`

#Usage

```php
$result = Checkmend::dueDiligence($imei);
// send certificate to email or url call back
Checkmend::getCertificate($result->certid, $url, $email);
$result = Checkmend::makeModelExt([$imei]);
```
