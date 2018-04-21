<?php

/**
 * Magtiny API manager default config parameters. Please do not modify data here.
 * If you want to modify manager config parameters. Please pass the array parameters
 * when you instance the apiManager just as following:
 * new \magtiny\tool\apiManager($config)
 * The "instanceDir", "secret" and "instanceUrl" are required.
 * The "parseFields", "messages" parameter should not be modified or override.
 */

return [
	"timeout" => 7200,
	"alg" => "SHA256",
	"secret" => "111222333",
	"checkNbf" => true,
	"checkExp" => true,
	"checkAud" => true,
	"checkIss" => true,
	"checkSub" => true,
	"messages" => [
		1100 => "Magtiny jwt default response massage",
		1101 => "Invalaid jwt authorization information!",
		1102 => "Invalaid jwt alt information!",
	]
];

