<?php

/**
 * Magtiny jwt default config parameters. Please do not modify data here.
 * If you want to modify manager config parameters. Please pass the array parameters
 * when you instance the apiManager just as following:
 * new \magtiny\tool\apiManager($config)
 * The "secret" are required.
 * The "messages" parameter should not be modified or overrided.
 */

return [
	"timeout" => 3600 * 24,
	"alg" => "SHA256",
	"secret" => "MAGTINY JWT SECRET",
	"checkNbf" => true,
	"checkExp" => true,
	"checkAud" => true,
	"checkIss" => true,
	"checkSub" => true,
	"aud"		=> [$_SERVER["HTTP_HOST"]],
	"sub"		=> [$_SERVER["HTTP_ORIGIN"]],
	"iss"		=> [$_SERVER["REMOTE_ADDR"]],
	"iat" 		=> $_SERVER["REQUEST_TIME"],
	"nbf" 		=> $_SERVER["REQUEST_TIME"],
	"messages" => [
		1100 => "Magtiny jwt default response massage",
		1101 => "Invalaid jwt authorization information!",
		1102 => "Invalaid jwt alt information is required!",
		1103 => "Magtiny jwt signature is incorrect!",
		1104 => "Magtiny jwt nbf time has not arrived!",
		1105 => "Magtiny jwt exp time is timeout!",
		1106 => "Magtiny jwt aud host servers are not right!",
		1107 => "Magtiny jwt iss request server is incorrect!",
		1108 => "Magtiny jwt sub http origin is incorrect",
		1109 => "Magtiny jwt check success!",
	]
];

