<?
# ------- SQL OBJECT ---------

function expected($value, $got, $msg) {
	if ($value !== $got) {
		echo "Expected value:".$value.PHP_EOL;
		echo "Got:".$got.PHP_EOL;
		echo $msg.PHP_EOL;
	}
}


include 'db.inc.php';
include 'lib\sql\filter.class.php';
include 'class\anecdote.class.php';
include 'lib\lang\language.class.php';

# --- Connect to test database
DB::test_connect();

class Test {
	public function run() {
		$test_methods =  preg_grep("/^test_/", get_class_methods(get_called_class()));
		foreach($test_methods as $method) {
			echo "Calling ".get_called_class()."::$method".PHP_EOL;
			call_user_func("static::".$method);
		}
	}
}

class TestSQL extends Test {
	private static function getAnecdote() {
		$filter = Anecdote::newFilter(array('Anecdote' => '*'));
		$filter->setLimit(1); 
		return current(Anecdote::find($filter));
	}
# ------------ FIND ONLY ONE ----------- 
	public static function test_find_one_two_anecdotes() {
		$filter = Anecdote::newFilter(array('Anecdote' => '*'));
		$filter->setLimit(1);

		$anecdote = Anecdote::find($filter);
		expected(1, count($anecdote), "Turi rasti tik vieną anekdotą");

		$filter->setLimit(2);
		$anecdote = Anecdote::find($filter);
		expected(2, count($anecdote), "Turi rasti du anekdotus");
	}

	public static function test_find_with_attribute() {
		$filter = Anecdote::newFilter(array("Anecdote" => array("title")));
		$filter->setFrom(array("Anecdote" => "a"));
		$filter->setLimit(1);
		$filter->setWhere(array("Anecdote.language" => Language::LT));
		$anecdote1 = current(Topic::find($filter));
		expected(FALSE, isset($anecdote1->contents), "Turi pakrauti tik 'title'");
		expected(TRUE , isset($anecdote1->title), "Turi pakrauti 'title'");
	}

	public static function test_exists() {
		$anecdote = self::getAnecdote();
		expected(TRUE, Anecdote::exists($anecdote->id), "Turi rasti anekdotą su id=$anecdote->id"); 
	}

	public static function test_load() {
		$anecdote = self::getAnecdote();
		$a        = Anecdote::load($anecdote->id);
		expected($a->id, $anecdote->id, "Turi pakrauti vieną anekdotą su id=$anecdote->id");
	}
}

TestSQL::run();

?>
