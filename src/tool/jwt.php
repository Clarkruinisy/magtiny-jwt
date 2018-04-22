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
		$message = isset($this->config["messages"][$code]) ? $this->config["messages"][$code] : "";
		return [
			"success" => $success,
			"code" => $code,
			"message" => $message,
			"data" => $data,
		];
	}

	private function base64urlEncode ($data)
	{
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	private function base64urlDecode ($data)
	{
		return base64_decode(strtr($data, '-_', '+/').str_repeat('=', 3 - (3 + strlen($data)) % 4));
	}

	private function signature($input)
	{
		return hash_hmac($this->config["alg"], $input, $this->config["secret"]);
	}

	public function encode ($data = [])
	{
		$header = [
			"typ" => "JWT",
			"alg" => $this->config["alg"]
		];
		$header64 = $this->base64urlEncode(json_encode($header));
		$payload = [
			"aud" => $this->config["aud"],
			"iss" => $this->config["iss"],
			"iat" => $this->config["iat"],
            "exp" => $this->config["iat"] + $this->config["timeout"],
			"sub" => $this->config["sub"],
			"nbf" => $this->config["nbf"],
			"jti" => uniqid("", true),
			"data" => $data
		];
		$payload64 = $this->base64urlEncode(json_encode($payload));
		$jwt = $header64.".".$payload64;
		$signature = $this->signature($jwt);
		return $jwt.".".$signature;
	}

	public function decode ($jwt = "") 
	{
		$jwt = explode('.', $jwt ? $jwt : globals::server("HTTP_AUTHORIZATION"));
		if (count($jwt) != 3) {
			return $this->render(1101);
		}
		list($header64, $payload64, $signature) = $jwt;
		$header = json_decode($this->base64urlDecode($header64), true);
		if (empty($header['alg'])) {
			return $this->render(1102);
		}
		if ($this->signature($header64.'.'.$payload64, $this->config) !== $signature) {
			return $this->render(1103);
		}
		$payload = json_decode($this->base64urlDecode($payload64), true);
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
		if ($this->config["checkIss"] && !in_array(globals::server("REMOTE_ADDR"), $payload["iss"])) {
			return $this->render(1107);
		}
		if ($this->config["checkSub"] && !in_array(globals::server("HTTP_ORIGIN"), $payload["sub"])) {
			return $this->render(1108);
		}
		return $this->render(1109, true, $payload["data"]);
	}
}

