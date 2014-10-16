<?
class UserSession {

	public static function setLanguage($lang) {
		$_SESSION['lang'] = $lang;
	}
	
	public static function language() {
		return isset($_SESSION['lang']) ? $_SESSION['lang'] : NULL;
	}

}

