<?

class User extends Dbobj {
	protected static $table = 'users';
	protected static $FIELDS = array(
			'id'    => '%d',
			'login' => '%s',
			'phash' => '%s',
			'sid'   => '%s',
			'email' => '%s',
			'aid'   => '%s',
			'active'=> '%d'
	);

    public static function by($x = 'email', $val) {
		return (self::isField($x)) ? self::find(array($x => $val), 1) : FALSE;
	}

	public static function login($user) {
		return static::update($user->id, array('sid'), array('sid'=> $user->sid));
	}

	public static function activate($id, $aid) { 
		if (self::exists($id) && $user = self::load($id) && $user->aid === $aid) {
			return !(current($user)->active) && self::update($id,
					array('active', 'aid'),
					array('active' => 1, 'aid' => ''));
		}
	}
}

