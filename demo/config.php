<?php

return [
	'mch_id' => 'XXXX', //商户号
	'appid' => 'XXXXXXXXXXXXXXXXXXX', //APPID
	'appsecret' => 'XXXXXXXXXXXXXXXXXXXXXXXXX', //App Secret
	'apikey' =>'XXXXXXXXXXXXXXXXXXXXXXX', //支付密钥
	'sslcertPath' => '/PATHTO/apiclient_cert.pem',
	'sslkeyPath' => '/PATHTO/apiclient_key.pem',
	'signType' => 'MD5',
	'notify_url' => 'http://YOURSITE/paidnotify.php',
	'refundnotify_url' => 'http://YOURSITE/refundednotify.php',
];