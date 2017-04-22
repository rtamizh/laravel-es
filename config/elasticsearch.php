<?php

return [
	'hosts' => [
		env('ES_HOSTS', 'localhost:9200')
	],
	'log_path' => 'storage/logs/',
];
