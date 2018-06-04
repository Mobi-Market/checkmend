<?php

return [
	'baseURI'           => env('CHECKMEND_URI', 'https://gapi.checkmend.com/'),
	'partnerId'         => env('CHECKMEND_PARTNER_ID'),
	'secret'            => env('CHECKMEND_SECRET'),
	'organisationId'    => env('CHECKMEND_ORG_ID'),
	'storeId'           => env('CHECKMEND_STORE_ID'),
	'logging'           => env('CHECKMEND_ENABLE_LOGS', false),
	'timeout'           => env('CHECKMEND_TIMEOUT', 5.0),
	'reseller'			=> env('CHECKMEND_RESELLER', false),
	'resellerDetails'	=> [
		'name'			=> '',
		'address' 		=> '',
		'contactnumber' => '',
	]
];