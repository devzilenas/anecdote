<?

class Crypt {

	public static function genAid() {
		return md5(self::randomCode(1024));
	}

	public static function genPhash($password) {
		return md5(md5(trim($password)));
	}

	private static function randomCode($length) {
		$chars = "qwertyuiopasdfghjklzxcvbnm0123456789QWERTYUIOPASDFGHJKLZXCVBNM";
		$code = ''; $count = mb_strlen($chars);
		while ($length-- > 0)
			$code .= $chars[mt_rand(0,$count-1)];
		return $code;
	}

}

