<?

class Language {

	const LT = 'lt';
	const RU = 'ru';
	const EN = 'en';
	const DE = 'de';

	public static function languages() {
		return array(
				self::LT => 'Lietuvių', 
				self::RU => 'Русский',
				self::DE => 'Deutsch',
				self::EN => 'English');
	}

	public static function setTranslations($language, $base_lang, $translations) {
		global $LANG;
		$LANG[$language][$base_lang] = array_change_key_case($translations);
	}

	public static function valid($language) {
		return in_array($language, array_keys(self::languages()));
	}

	public static function d() {
		return self::LT;
	}

	public static function baseLang() {
		return 'en';
	}

	private static function findStr($language, $base_language, $str) {
		global $LANG;
		if (isset($LANG[$language][$base_language][strtolower($str)])) {
			return $LANG[$language][$base_language][strtolower($str)];
		} else {
			return $str;
		}
	}

	public static function t($str) {
		global $LANG;
		if (UserSession::language() === self::baseLang()) {
			return $str;
		} else if ($str = self::findStr(UserSession::language(), self::baseLang(), $str)) {
			return $str;
		} else {
			return $str;
		}
	}

}

