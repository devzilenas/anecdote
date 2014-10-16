<?

class Logger {
	public static function info($msg) {
		$info = new InfoMsg($msg);
		self::add($info);
	}
	public static function undefErr($msg) {
		self::err('UNDEF', $msg);
	}
	public static function err($id, $msg) {
		$tmp = array();
		if (is_array($msg)) {
			$tmp = $msg;
		} else if (is_string($msg)) {
			$tmp[] = $msg;
		}
		foreach ($tmp as $m) {
			$err = new ErrMsg($id, $m);
			self::add($err);
		}
	}
	public static function nextErr() { 
		if (self::ok()) {
			return $_SESSION['MSG']->shiftErr();
		}
	}
	public static function nextInfo() {
		if (self::ok()) {
			return $_SESSION['MSG']->shiftInfo();
		}
	}
	private static function add($msg) {
		if (!self::ok()) {
			$queue = new MsgQueue();
			$_SESSION['MSG'] = $queue;
		}
		$queue = $_SESSION['MSG'];
		$queue->add($msg);
	}
	private static function ok() { 
		return isset($_SESSION['MSG']) && ($_SESSION['MSG'] instanceof MsgQueue);
	}
}

class MsgQueue {
	private $queue = array();
	public function add($msg) {
		$type = get_class($msg);
		if (!is_array($this->queue[$type])) $this->queue[$type] = array();
		array_push($this->queue[$type], $msg);
	}
	public function shiftErr() {
		return $this->shift('ErrMsg');
	}
	public function shiftInfo() {
		return $this->shift('InfoMsg');
	}

	private function shift($type) {
		if (isset($this->queue[$type]) && is_array($this->queue[$type])) {
			return array_shift($this->queue[$type]);
		} else {
			return NULL;
		}
	}
}

class Msg {
	public $id;
	public $msg;
}

class ErrMsg extends Msg {
	public function __construct($id, $msg) {
		$this->id  = $id;
		$this->msg = $msg;
	}
	public static $ID = array(
			'BAD_PASS'    => 'Password unsuitable!',
			'BAD_EMAIL'   => 'E-mail unsuitable!',
			'BAD_LOGIN'   => 'User name unsuitable!',
			'PASS_MATCH'  => 'Passwords don\'t match!',
			'INUSE_EMAIL' => 'E-mail already in use!',
			'INUSE_LOGIN' => 'User name already in use!',
			'LOGIN_FAIL'  => 'Not logged in!',
			'NEW_USER_FAIL' => 'User not created!',
			'UNDEF'       => 'Error!',
			'NO_VAL'      => 'Provide value'
		); 
}

class InfoMsg extends Msg {
	public function __construct($msg) {
		$this->msg = $msg;
	}
	public static $ID = array(
		'UNDEF' => 'A msg 4 the usr.');
}

