<?php

namespace magtiny\tool;

use magtiny\framework\globals;
use magtiny\framework\render;


class jwt
{
	public function __construct ($config = [])
	{
		$defaultConfig = render::config(__DIR__."/../config.php");
		$this->config = array_merge($defaultConfig, $config);
	}

	private function render ($code = 1100, $success = false, $data = null)
	{
		return [
			"success" => $success,
			"code" => $code,
			"message" => $this->config["messages"][$code],
			"data" => $data,
		];
	}

	private static function base64urlEncode ($data)
	{
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	private static function base64urlDecode ($data)
	{
		return base64_decode(strtr($data, '-_', '+/').str_repeat('=', 3 - ( 3 + strlen($data)) % 4 ));
	}

	private static function signature($input)
	{
		return hash_hmac($this->config["alg"], $input, $this->config["secret"]);
	}

	public static function encode ($data)
	{
		$header = static::base64urlEncode([
			"typ" => "JWT",
			"alg" => $this->config["alg"]
		]);
		$token = [
			"aud" => [globals::server("HTTP_HOST")],
			"iss" => globals::server("REMOTE_ADDR"),
			"iat" => globals::server("REQUEST_TIME"),
            "exp" => globals::server('REQUEST_TIME') + $this->config["timeout"],
			"sub" => globals::server("HTTP_ORIGIN"),
			"nbf" => globals::server("REQUEST_TIME"),
			"jti" => '1',
		];
		$payload = static::base64urlEncode(array_merge($token, $data));
		$jwt = $header.".".$token;
		$signature = static::signature($jwt);
		return $jwt.".".$signature;
	}

	public static function decode () 
	{
		$jwt = explode('.', globals::server("HTTP_AUTHORIZATION"));
		if (count($jwt) != 3) {
			return $this->render(1101);
		}
		list($header64, $payload64, $signature) = $jwt;
		$header = json_decode(self::base64urlDecode($header64), true);
		if (empty($header['alg'])) {
			return $this->render(1102);
		}
		if (self::signature($header64.'.'.$payload64) !== $signature) {
			return $this->render(1103);
		}
		$payload = json_decode(self::base64urlDecode($payload64), true);
		$time = globals::server("REQUEST_TIME");
		if ($this->config["checkNbf"] && $payload['nbf'] > $time) {
			return $this->render(1104);
		}
		if ($this->config["checkExp"] && $payload['exp'] < $time) {
			return $this->render(1105);
		}
		if ($this->config["checkAud"] && !in_array(globals::server("HTTP_HOST"), $payload["aud"])) {
			return $this->render(1106);
		}
		if ($this->config["checkIss"] && globals::server("REMOTE_ADDR") !== $payload["iss"]) {
			return $this->render(1107);
		}
		if ($this->config["checkSub"] && globals::server("HTTP_ORIGIN") !== $payload["sub"]) {
			return $this->render(1108);
		}
		return $this->render(1109, true, $payload);
	}
}



