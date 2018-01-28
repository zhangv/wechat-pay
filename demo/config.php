<?php

return [
	'mch_id' => 'XXXX', //商户号
	'app_id' => 'XXXXXXXXXXXXXXXXXXX', //APPID
	'app_secret' => 'XXXXXXXXXXXXXXXXXXXXXXXXX', //App Secret
	'api_key' =>'XXXXXXXXXXXXXXXXXXXXXXX', //支付密钥
	'ssl_cert_path' => __DIR__ . '/keys/apiclient_cert.pem',
	'ssl_key_path' => __DIR__ .'/keys/apiclient_key.pem',
	'sign_type' => 'MD5',
	'notify_url' => 'http://YOURSITE/paidnotify.php',
	'refund_notify_url' => 'http://YOURSITE/refundednotify.php',
	'h5_scene_info' => [//required in H5
		'h5_info' => ['type' => 'Wap', 'wap_url' => 'http://wapurl', 'wap_name' => 'wapname']
	],
	'rsa_pubkey_path' => __DIR__ .'/keys/pubkey.pem',
	'jsapi_ticket' => __DIR__ .'/jsapi_ticket.json'
];