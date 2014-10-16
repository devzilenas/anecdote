<?

class Request {
	public static function gPostArray($name) {
		return isset($_POST[$name]) && is_array($_POST[$name]) ? $_POST[$name] : array();
	}

	public static function hlexit($header) {
		header("Location: ".$header);
		exit;
	}

}

