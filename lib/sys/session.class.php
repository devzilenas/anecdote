<?
class Session {
	public static function gSessionArray($name) {
		return ((isset($_SESSION[$name]) && is_array($_SESSION[$name])) ? $_SESSION[$name] : array());
	}

	//session get and unset
	public static function sgu($name) {
		$val = $_SESSION[$name];
		unset($_SESSION[$name]);
		return $val;
	}

}

